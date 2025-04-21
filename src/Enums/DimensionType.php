<?php

namespace Unav\SpxConnect\Enums;

enum DimensionType: string
{
    case RESOURCE = '01';
    case TFWW = '02';
    case FUND = '03';
    case FUNCTION = '04';
    case RESTRICTION = '05';
    case ORGID = '06';
    case WHO = '07';
    case FLAG = '08';
    case PROJECT = '09';
    case DETAIL = '10';

    public function label(): string
    {
        return match ($this) {
            self::RESOURCE => '01',
            self::TFWW => '02',
            self::FUND => '03',
            self::FUNCTION => '04',
            self::RESTRICTION => '05',
            self::ORGID => '06',
            self::WHO => '07',
            self::FLAG => '08',
            self::PROJECT => '09',
            self::DETAIL => '10',
        };
    }
}