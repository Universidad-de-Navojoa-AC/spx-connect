<?php

namespace Unav\SpxConnect\Contracts;

interface ClientServiceInterface
{
    public function search(string $query): array;
}
