<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

/**
 * Helper class for buildings API filters.
 * Since the API has weird limitations/quirks building them manually should be avoided.
 *
 * Create a new FilterBuilder, add conditions, and finally use getFilter() or getFilters() to get the result.
 *
 * Multiple conditions are combined with AND
 */
class FilterBuilder
{
    /**
     * @var array<string,?string>
     */
    private $filters;

    public function __construct()
    {
        $this->filters = [];
    }

    /**
     * Split a filter value into non-allowed and allowed substrings and only returns an array of allowed values.
     * In case of a user-defined filter string this can be used to at least filter by the substrings that are
     * allowed.
     *
     * 'foo; ;bar quux' -> ['foo', 'bar', 'quux']
     *
     * @return string[]
     */
    public static function extractValidFilterValueSubstrings(string $value): array
    {
        return Tools::extractValidFilterSubstrings($value);
    }

    /**
     * Equal, case-sensitive.
     */
    public function eq(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'eq', $value);
    }

    /**
     * Equal, case-insensitive.
     */
    public function eqI(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'eqI', $value);
    }

    /**
     * Greater than or equal.
     */
    public function gte(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'gte', $value);
    }

    /**
     * Greater than.
     */
    public function gt(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'gt', $value);
    }

    /**
     * Less than or equal.
     */
    public function lte(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'lte', $value);
    }

    /**
     * Less than.
     */
    public function lt(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'lt', $value);
    }

    /**
     * Substring search, case-sensitive.
     */
    public function like(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'like', $value);
    }

    /**
     * Substring search, case-insensitive.
     */
    public function likeI(string $columnName, string $value): FilterBuilder
    {
        return $this->append($columnName, 'likeI', $value);
    }

    /**
     * Matches at least one of the values.
     *
     * @param string[] $values
     */
    public function in(string $columnName, array $values): FilterBuilder
    {
        // The docs say it's "In", but that is wrong
        return $this->append($columnName, 'in', $values);
    }

    /**
     * Empty field.
     */
    public function isNull(string $columnName): FilterBuilder
    {
        return $this->append($columnName, 'isNull');
    }

    /**
     * Non-empty field.
     */
    public function notNull(string $columnName): FilterBuilder
    {
        return $this->append($columnName, 'notNull');
    }

    /**
     * @param string|string[]|null $value
     */
    private function append(string $columnName, string $operation, $value = null): FilterBuilder
    {
        $columnName = Tools::validateFilterName($columnName);
        $key = "$columnName-$operation";
        if (array_key_exists($key, $this->filters)) {
            // The API will ignore the first one if they start the same, which is confusing, so error out in this case.
            throw new \ValueError('Filters don\'t allow two entries with the same operation for the same field');
        }
        if ($value !== null) {
            if (is_array($value)) {
                $this->filters[$key] = Tools::validateFilterValueList($value);
            } else {
                $this->filters[$key] = Tools::validateFilterValue($value);
            }
        } else {
            $this->filters[$key] = null;
        }

        return $this;
    }

    /**
     * Returns the separate filter entries
     * e.g. ['FOO-eq=4', 'BAR-like=42'].
     *
     * @return string[]
     */
    public function getFilters(): array
    {
        $items = [];
        foreach ($this->filters as $key => $value) {
            if ($value === null) {
                $items[] = $key;
            } else {
                $items[] = "$key=$value";
            }
        }

        return $items;
    }

    /**
     * Returns the whole filter joined by ';'
     * e.g. 'FOO-eq=4;BAR-like=42'.
     */
    public function getFilter(): string
    {
        return implode(';', $this->getFilters());
    }
}
