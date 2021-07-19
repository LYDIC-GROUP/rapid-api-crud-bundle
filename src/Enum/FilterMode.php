<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 9:41 PM
 */

namespace LydicGroup\RapidApiCrudBundle\Enum;

use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;

class FilterMode
{
    const BASIC = 1;
    CONST EXTENDED = 2;
    CONST DQL = 3;

    public static function fromLabel(string $label) {
        switch ($label) {
            case "BASIC":
                return 1;
            case "EXTENDED":
                return 2;
            case "DQL":
                return 3;
            default:
                throw new NotFoundException(sprintf('Filter mode %s not found', $label));
        }
    }
}