<?php
/**
 * Copyright (c) 2019.
 *
 * Francesco "Abbadon1334" Danti <fdanti@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace atk4\validate\Rules;

use atk4\data\Model;

/**
 * Class ValueNotInList
 *
 * Validate : if value is NOT present against a Model Field
 *
 * Rule code : atk4_value_not_in_list
 *
 * @example ['atk4_value_not_in_list',$model,'field_to_check']
 * @example ['atk4_value_not_in_list',$model->ref('model_to_check'),'field_to_check']
 *
 * @package atk4\validate\Rules
 */
class ValueNotInList extends aRule implements iRule
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
        
        return ($count > 0) ? false : true;
    }
    
    public static function getMessages(): array
    {
        return [
            'en' => '{field} is in list',
            'it' => '{field} non nella lista',
            'ro' => '{field} nu este în listă',
        ];
    }
}
