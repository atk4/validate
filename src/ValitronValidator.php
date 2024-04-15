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
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDate($field, $value);
    }

    /** @param array<mixed> $params */
    protected function validateDateFormat($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateFormat($field, $value, $params);
    }

    /** @param array<mixed> $params */
    protected function validateDateBefore($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateBefore($field, $value, $params);
    }

    /** @param array<mixed> $params */
    protected function validateDateAfter($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDateAfter($field, $value, $params);
    }
}
