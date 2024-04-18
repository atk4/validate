<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Valitron\Validator as OriginalValidator;

class ValitronValidator extends OriginalValidator
{
    #[\Override]
    protected function validateDate($field, $value)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        return parent::validateDate($field, $value);
    }

    /**
     * @param array<mixed> $params
     */
    #[\Override]
    protected function validateDateFormat($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        if ($value instanceof \DateTime) {
            return true;
        }

        return parent::validateDateFormat($field, $value, $params);
    }

    /**
     * @param array<mixed> $params
     */
    #[\Override]
    protected function validateDateBefore($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        if (!isset($params[0]) || $params[0] === null || (is_string($params[0]) && trim($params[0]) === '')) {
            return false;
        }

        return parent::validateDateBefore($field, $value, $params);
    }

    /**
     * @param array<mixed> $params
     */
    #[\Override]
    protected function validateDateAfter($field, $value, $params)
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return false;
        }

        if (!isset($params[0]) || $params[0] === null || (is_string($params[0]) && trim($params[0]) === '')) {
            return false;
        }

        return parent::validateDateAfter($field, $value, $params);
    }
}
