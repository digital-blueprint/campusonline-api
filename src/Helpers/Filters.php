<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Filters
{
    /** @var string Case-sensitive exact match */
    public const EQUALS_OPERATOR = 'equals';
    /** @var string Case-insensitive exact match */
    public const EQUALS_CI_OPERATOR = 'equals_ci';
    /** @var string Case-sensitive partial match */
    public const CONTAINS_OPERATOR = 'contains';
    /** @var string Case-insensitive partial match */
    public const CONTAINS_CI_OPERATOR = 'contains_ci';
    /** @var string Passes if the value exactly matches any of the given filter values (case-sensitive) */
    public const IN_OPERATOR = 'in';

    public const LOGICAL_AND_OPERATOR = 'and';
    public const LOGICAL_OR_OPERATOR = 'or';

    public const IDENTIFIERS_FILTER = 'identifiers';

    public static function passesFilter(?string $value, string $filterOperator, $filterValue): bool
    {
        switch ($filterOperator) {
            case Filters::EQUALS_OPERATOR:
                return $value === $filterValue;
            case Filters::EQUALS_CI_OPERATOR:
                return $value === null ?
                        $filterValue === null :
                        $filterValue !== null && strtolower($value) === strtolower($filterValue);
            case Filters::CONTAINS_OPERATOR:
                return $value !== null && $filterValue !== null && str_contains($value, $filterValue);
            case Filters::CONTAINS_CI_OPERATOR:
                return $value !== null && $filterValue !== null && str_contains(strtolower($value), strtolower($filterValue));
            case Filters::IN_OPERATOR:
                // strictly require an array type filter value
                return in_array($value, is_array($filterValue) ? $filterValue : [], true);
            default:
                throw new ApiException(sprintf('unknown filter operator \'%s\'', $filterOperator));
        }
    }
}
