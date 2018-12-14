# Validator for Agile Data

This is validator add-on for Agile Data (https://github.com/atk4/data).

It uses https://github.com/vlucas/valitron under the hood.


[![Build Status](https://travis-ci.org/atk4/validate.png?branch=master)](https://travis-ci.org/atk4/validate)
[![Code Climate](https://codeclimate.com/github/atk4/validate/badges/gpa.svg)](https://codeclimate.com/github/atk4/validate)
[![StyleCI](https://styleci.io/repos/161695320/shield)](https://styleci.io/repos/161695320)
[![CodeCov](https://codecov.io/gh/atk4/validate/branch/develop/graph/badge.svg)](https://codecov.io/gh/atk4/validate)
[![Test Coverage](https://codeclimate.com/github/atk4/validate/badges/coverage.svg)](https://codeclimate.com/github/atk4/validate/coverage)
[![Issue Count](https://codeclimate.com/github/atk4/validate/badges/issue_count.svg)](https://codeclimate.com/github/atk4/validate)

[![License](https://poser.pugx.org/atk4/validate/license)](https://packagist.org/packages/atk4/validate)
[![GitHub release](https://img.shields.io/github/release/atk4/validate.svg?maxAge=2592000)](CHANGELOG.md)


# Usage

``` php
// add controller to your model BEFORE adding fields
$model->add(new \atk4\validate\Controller());

// add model fields

// name is required and at least 3 characters long
$model->addField('name', ['validate'=>['required','lengthMin'=>3]]);


$model->addField('type', [
    'required' => true,
    'enum'     => ['dog', 'ball'],
]);

$model->addField('tail_length', [
    'type'        => 'number',
    'validate_if' => ['type' => 'dog'], // only dogs have tail
    'validate'    => ['required','integer'], // if type=dog, then this field is required, otherwise it's not
]);
```

See `/tests` folder for more examples.

