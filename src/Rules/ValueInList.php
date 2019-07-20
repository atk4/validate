<?php

namespace atk4\validate\Rules;

use atk4\data\Exception;
use atk4\data\Model;

/**
 * Class ValueInList
 *
 * Validate : if value is present against a Model Field
 *
 * Rule code : atk4_value_in_list
 *
 * @example ['atk4_value_in_list',$model,'field_to_check']
 * @example ['atk4_value_in_list',$model->ref('model_to_check'),'field_to_check']
 *
 * @package atk4\validate\Rules
 */
class ValueInList extends RuleAbstract
{
    public static $rule_id = 'atk4_value_in_list';

    public static $rule_messages = [
        'en' => '{field} is not in list',
        'it' => '{field} già nella lista',
        'ro' => '{field} deja în listă',
    ];

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

        $count = (int)$ref_model->newInstance()
                                ->addCondition($ref_model_field, '=', $value)
                                ->action('count')
                                ->getOne()
        ;

        return $count > 0;
    }
}
