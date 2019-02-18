<?php

namespace atk4\validate\Rules;

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

class ValueUniqueOther extends aRule implements iRule
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
        
        $isLoaded = $model->loaded(); // if loaded ? INSERT : UPDATE
        
        $model = $model->newInstance();
        $model->addCondition($fieldCheck, '=', $value);
        
        $action = $model->action('count');
        $count  = (int)$action->getOne();
        
        // if more than 1 is always false;
        if ($count > 1)
        {
            /**
             * @TODO throw exception this must not happen?
             */
            return false;
        }
        
        // if is loaded must be 1;
        if ($isLoaded && $count == 1) {
            return true;
        }
    
        // if is not loaded must be 0;
        if (!$isLoaded && $count == 0) {
            return true;
        }
        
        return false;
    }
    
    public static function getMessages(): array
    {
        return [
            'en' => '{field} must be unique',
            'it' => '{field} non è unico',
            'ro' => '{field} nu este unic',
        ];
    }
}
