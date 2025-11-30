<?php

namespace Unav\SpxConnect\Contracts;

interface ProductServiceInterface
{
    public function search(string $query): array;

    public function setUserId(?string $userId = 'global'): self;
}
