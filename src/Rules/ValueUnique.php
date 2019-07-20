<?php

namespace atk4\validate\Rules;

use atk4\data\Exception;
use atk4\data\Model;

/**
 * Class ValueUnique
 *
 * Validate : if value is unique in Model in his field
 *
 * Rule code : atk4_value_unique
 *
 * @example ['atk4_value_unique',$model]
 *
 * @package atk4\validate\Rules
 */
class ValueUnique extends RuleAbstract
{
    public static $rule_id = "atk4_value_unique";
    public static $rule_messages = [
        'en' => '{field} is not unique',
        'it' => '{field} non è unico',
        'ro' => '{field} nu este unic',
    ];

    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        if (count($params) != 1) {
            throw new Exception(
                [
                    'Rule ' . self::$rule_id . ' must have only 1 parameter Model',
                    'field'  => $field,
                    'value'  => $value,
                    'params' => $params,
                    'fields' => $fields
                ]
            );
        }

        $ref_model = $params[0] ?? null;

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

        $isLoaded = $ref_model->loaded(); // if loaded ? INSERT : UPDATE

        $count = (int)$ref_model->newInstance()
                                ->addCondition($field, '=', $value)
                                ->action('count')
                                ->getOne()
        ;

        return ($isLoaded && $count === 1) || (!$isLoaded && $count === 0);
    }
}
