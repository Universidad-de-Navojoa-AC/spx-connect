<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Enums\DimensionType;

interface SunPlusDimensionServiceInterface
{
    public function find(DimensionType $dimension): array;

    public function setUserId(?string $userId = 'global'): self;
}
