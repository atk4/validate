<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Valitron\Validator as OriginalValidator;

/**
 * Valitron Validation Class with required fixes.
 */
class ValitronValidator extends OriginalValidator
{
    protected function validateDate($field, $value)
    {
        if (is_null($value) || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDate($field, $value);
    }

    protected function validateDateFormat($field, $value, $params)
    {
        if (is_null($value) || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateFormat($field, $value, $params);
    }

    protected function validateDateBefore($field, $value, $params)
    {
        if (is_null($value) || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateBefore($field, $value, $params);
    }

    protected function validateDateAfter($field, $value, $params)
    {
        if (is_null($value) || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateAfter($field, $value, $params);
    }
}
