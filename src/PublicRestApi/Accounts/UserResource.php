<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Accounts;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class UserResource extends Resource
{
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const ACCOUNTS_ATTRIBUTE = 'accounts';
    private const ACCOUNT_STATUS_KEY_ATTRIBUTE = 'accountStatusKey';
    private const ACCOUNT_TYPE_KEY_ATTRIBUTE = 'accountTypeKey';
    private const EMAIL_ATTRIBUTE = 'email';
    private const USERNAME_ATTRIBUTE = 'username';

    public function getPersonUid(): ?string
    {
        return $this->resourceData[self::PERSON_UID_ATTRIBUTE] ?? null;
    }

    public function getNumAccounts(): int
    {
        return count($this->resourceData[self::ACCOUNTS_ATTRIBUTE] ?? []);
    }

    public function getAccountStatusKey(int $accountIndex): ?string
    {
        return $this->resourceData[self::ACCOUNTS_ATTRIBUTE][$accountIndex][self::ACCOUNT_STATUS_KEY_ATTRIBUTE] ?? null;
    }

    public function getAccountTypeKey(int $accountIndex): ?string
    {
        return $this->resourceData[self::ACCOUNTS_ATTRIBUTE][$accountIndex][self::ACCOUNT_TYPE_KEY_ATTRIBUTE] ?? null;
    }

    public function getEmail(int $accountIndex): ?string
    {
        return $this->resourceData[self::ACCOUNTS_ATTRIBUTE][$accountIndex][self::EMAIL_ATTRIBUTE] ?? null;
    }

    public function getUsername(int $accountIndex): ?string
    {
        return $this->resourceData[self::ACCOUNTS_ATTRIBUTE][$accountIndex][self::USERNAME_ATTRIBUTE] ?? null;
    }
}
