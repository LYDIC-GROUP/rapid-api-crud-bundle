<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 9:41 PM
 */

namespace LydicGroup\RapidApiCrudBundle\Enum;

use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;

class SorterMode
{
    const BASIC = 1;

    public static function fromLabel(string $label) {
        switch ($label) {
            case "BASIC":
                return 1;
            default:
                throw new NotFoundException(sprintf('Filter mode %s not found', $label));
        }
    }
}