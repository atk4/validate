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

/**
 * Class ValueNotInList.
 *
 * Validate : if value is NOT present against a Model Field
 *
 * Rule code : atk4_value_not_in_list
 *
 * @example ['atk4_value_not_in_list',$model,'field_to_check']
 * @example ['atk4_value_not_in_list',$model->ref('model_to_check'),'field_to_check']
 */
class ValueNotInList extends ValueInList
{
    public static $rule_id = 'atk4_value_not_in_list';
    public static $rule_messages = [
        'en' => '{field} is already in list',
        'it' => '{field} è già nella lista',
        'ro' => '{field} este deja în listă',
    ];

    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        return !parent::getCallback($field, $value, $params, $fields);
    }
}
