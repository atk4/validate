# Validator for Agile Data

This is validator add-on for Agile Data (https://github.com/atk4/data).

It uses https://github.com/vlucas/valitron under the hood.

# Usage

``` php
// add controller to your model BEFORE adding fields
$model->add(new \atk4\validate\Controller());

// add model fields

// name is required and at least 3 characters long
$model->addField('name', 'validate'=>['required',['lengthMin', 3]]);


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

