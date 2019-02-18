<?php

namespace atk4\validate\Rules;

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
class ValueInList extends aRule implements iRule
{
    
    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        $errors = [];
        
        if (count($params) != 2) {
            $errors[] = 'need 2 params';
        }
        
        if (isset($params[0]) && !($params[0] instanceof Model)) {
            $errors[] = 'first param must be \atk4\data\Model';
        }
        
        if (isset($params[1]) && !is_string($params[1])) {
            $errors[] = 'second param must be string';
        }
        
        if (count($errors) > 0) {
            throw new \Exception(static::getName() . ' ' . implode(', ', $errors) . ' (Model $model,string $field)');
        }
        
        $model = $params[0]; // Model used for check
        
        $fieldCheck = $params[1]; // Field name used for check
        
        $model = $model->newInstance();
        $model->addCondition($fieldCheck, '=', $value);
        
        $action = $model->action('count');
        $count  = (int)$action->getOne();
        
        return ($count > 0) ? true : false;
    }
    
    public static function getMessages(): array
    {
        return [
            'en' => '{field} is not in list',
            'it' => '{field} già nella lista',
            'ro' => '{field} deja în listă',
        ];
    }
}
