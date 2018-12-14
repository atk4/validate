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
$v->rule('name', ['required','lengthMin'=>3]);

// set multiple validation rules in one shot
// ->rules($array_of_rules) // [field=>rules]
$v->rules([
    'name' => ['required', 'lengthMin'=>3],
    'age' => ['integer', 'min'=>0, 'max'=>99],
    'tail_length' => ['integer', 'min'=>0],
]);

// set validation rules based on value of another field
// if type=dog, then sets fields age and tail_length as required
// ->if($condition, $then_array_of_rules, $else_array_of_rules)
$v->if(['type'=>'dog'], [
    'age' => ['required'],
    'tail_length' => ['required'],
], [
    'tail_length' => ['equals'=>''], // balls don't have tail
]);

// you can also pass multiple conditions which will be treated as AND conditions
$v->if(['type'=>'dog', 'age'=>50], $rules_if_true, $rules_if_false);

// you can also set custom error message like this:
$v->rule('age', ['min'=>3, 'message'=>'Common! {field} should be bigger']);
// and you will get this "Common! Age should be bigger"
```

You can also pass callback instead of array of rules.

See `/tests` folder for more examples.
