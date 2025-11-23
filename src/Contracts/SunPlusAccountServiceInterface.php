<?php

namespace Unav\SpxConnect\Contracts;

interface SunPlusAccountServiceInterface
{
    public function getAll(): array;

    public function findByCode(string $accountCode): array;

    public function search(string $query): array;

    public function setUserId(?string $userId = 'global'): self;
}
