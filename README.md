# Validator for Agile Data

This is validator add-on for Agile Data (https://github.com/atk4/data).

It uses https://github.com/vlucas/valitron under the hood.


[![Build Status](https://travis-ci.org/atk4/validate.png?branch=develop)](https://travis-ci.org/atk4/validate)
[![Code Climate](https://codeclimate.com/github/atk4/validate/badges/gpa.svg)](https://codeclimate.com/github/atk4/validate)
[![StyleCI](https://styleci.io/repos/161695320/shield)](https://styleci.io/repos/161695320)
[![CodeCov](https://codecov.io/gh/atk4/validate/branch/develop/graph/badge.svg)](https://codecov.io/gh/atk4/validate)
[![Test Coverage](https://codeclimate.com/github/atk4/validate/badges/coverage.svg)](https://codeclimate.com/github/atk4/validate/coverage)
[![Issue Count](https://codeclimate.com/github/atk4/validate/badges/issue_count.svg)](https://codeclimate.com/github/atk4/validate)

[![License](https://poser.pugx.org/atk4/validate/license)](https://packagist.org/packages/atk4/validate)
[![GitHub release](https://img.shields.io/github/release/atk4/validate.svg?maxAge=2592000)](CHANGELOG.md)


# Usage

``` php
// add model fields
$model->addField('name');
$model->addField('age', ['type' => 'number']);
$model->addField('type', ['required' => true, 'enum' => ['dog', 'ball']]);
$model->addField('tail_length', ['type' => 'number']);

// add validator to your model
// also will register itself as $model->validator property
$v = new \atk4\validate\Validator($model);

// set simple validation rule for one field
// ->rule($field, $rules)
$v->rule('name', [ 'required', ['lengthMin', 3] ]);

// set multiple validation rules in one shot
// ->rules($array_of_rules) // [field=>rules]
$v->rules([
    'name' => ['required', ['lengthMin',3]],
    'age' => ['integer', ['min',0], ['max',99]],
    'tail_length' => ['integer', ['min',0]],
]);

// set validation rules based on value of another field
// if type=dog, then sets fields age and tail_length as required
// ->if($condition, $then_array_of_rules, $else_array_of_rules)
$v->if(['type'=>'dog'], [
    'age' => ['required'],
    'tail_length' => ['required'],
], [
    'tail_length' => [ ['equals',''] ], // balls don't have tail
]);

// you can also pass multiple conditions which will be treated as AND conditions
$v->if(['type'=>'dog', 'age'=>50], $rules_if_true, $rules_if_false);

// you can also set custom error message like this:
$v->rule('age', [ ['min', 3, 'message'=>'Common! {field} to small'] ]);
// and you will get this "Common! Age to small"
```

You can also pass callback instead of array of rules.
Callback receives these parameters $field, $value, $args, $data and should return true/false.

See `/tests` folder for more examples.

# Custom rules

Definition of custom rules : 

Example class of rule : atk4_value_unique

can be used like this :

``` php

$validator = new \atk4\validate\Validator($userModel);

$validator->rule('email', [ ['atk4_value_unique', $userModel] ]);

```

it will check if the value in field email is unique in field email of model  

below the simple code that make the validation :

method `getCallback` 
 - $field = is the field to validate
 - $value = is the value of field to validate
 - $params = is the array of parameters, in this case 1 ( $userModel )
 - $fields = other fields of data to validate

method `getMessages`
 - return an array, where key is language code and value is the translated language ( en is mandatory)  

``` php

class ValueUnique extends aRule implements iRule
{
    
    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        $errors = [];
    
        if (count($params) != 1) {
            $errors[] = 'need 1 param';
        }
    
        if (isset($params[0]) && !($params[0] instanceof Model)) {
            $errors[] = 'first param must be \atk4\data\Model';
        }
    
        if (count($errors) > 0) {
            throw new \Exception(static::getName() . ' ' . implode(', ', $errors) . ' (Model $model,string $field)');
        }
    
        $model = $params[0]; // Model used for check
    
        $isLoaded = $model->loaded(); // if loaded ? INSERT : UPDATE
        
        $model = $model->newInstance();
        $model->addCondition($field, '=', $value);
        
        $action = $model->action('count');
        $count  = (int)$action->getOne();
                
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

```

at now there are 4 Model Validation rule :
 - `atk4_value_unique` ***validate uniqueness on the same model field, need 1 param : Model*** 			  
 - `atk4_value_unique_other` ***validate uniqueness on another model field, need 2 params : Model, field*** 
 - `atk4_value_in_list` ***validate if value exists in model field, need 2 params : Model, field***
 - `atk4_value_not_in_list` ***validate if value not exists in model field, need 2 params : Model, field***
 
 Attention : `atk4_value_unique` and `atk4_value_unique_other` check model for inserting or updating
 
See `/tests` folder for more examples.
