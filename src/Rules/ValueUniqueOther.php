<?php

namespace atk4\validate\Rules;

use atk4\data\Exception;
use atk4\data\Model;

/**
 * Class ValueUniqueOther
 *
 * Validate : if value is unique in Model in another field
 *
 * Rule code : atk4_value_unique_other
 *
 * @example ['atk4_value_unique_other',$model,'field_name_to_check']
 *
 * @package atk4\validate\Rules
 */
class ValueUniqueOther extends ValueUnique
{
    public static $rule_id = "atk4_value_unique_other";

    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        if (count($params) != 2) {
            throw new Exception(
                [
                    'Rule ' . self::$rule_id . ' must have 2 parameters ( Model and Model field to check against)',
                    'field'  => $field,
                    'value'  => $value,
                    'params' => $params,
                    'fields' => $fields
                ]
            );
        }

        $ref_model = $params[0] ?? null;
        $ref_model_field = $params[1] ?? null;

        if (!($ref_model instanceof Model)) {
            throw new Exception(
                [
                    'Rule ' . self::$rule_id . ' first param must be a atk4\data\Model (the Model to check against)',
                    'field'  => $field,
                    'value'  => $value,
                    'params' => $params,
                    'fields' => $fields
                ]
            );
        }

        if (!is_string($ref_model_field)) {
            throw new Exception(
                [
                    'Rule ' . self::$rule_id . ' second param must be string (the Model field to check)',
                    'field'  => $field,
                    'value'  => $value,
                    'params' => $params,
                    'fields' => $fields
                ]
            );
        }

        $isLoaded = $ref_model->loaded(); // if loaded ? INSERT : UPDATE

        $count = (int)$ref_model->newInstance()
                                ->addCondition($ref_model_field, '=', $value)
                                ->action('count')
                                ->getOne()
        ;

        return ($isLoaded && $count === 1) || (!$isLoaded && $count === 0);
    }
}
