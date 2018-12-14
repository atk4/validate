<?php

// vim:ts=4:sw=4:et:fdm=marker:fdl=0

namespace atk4\validate;

/**
 * Add this controller class in your model to enable validations.
 *
 * IMPORTANT: You should add it before adding fields in data model !!!
 *
 * $model->add('\atk4\validate\Controller');
 */
class Controller
{
    // @todo probably some of these traits are not actually needed
    //use \atk4\core\ContainerTrait;
    use \atk4\core\TrackableTrait;
    //use \atk4\core\HookTrait;
    //use \atk4\core\DynamicMethodTrait;
    use \atk4\core\InitializerTrait {
        init as _init;
    }
    //use \atk4\core\NameTrait;
    //use \atk4\core\DIContainerTrait;
    //use \atk4\core\FactoryTrait;
    //use \atk4\core\AppScopeTrait;

    /**
     * Initialization.
     */
    public function init()
    {
        $this->_init();

        $this->owner->_default_seed_addField = ['\\atk4\\validate\\ModelField'];

        $this->owner->addHook('validate', $this);
    }

    /**
     * Runs validation for all model fields.
     *
     * @param \atk4\data\Model $m
     * @param string           $intent
     *
     * @return array|null
     */
    public function validate(\atk4\data\Model $m, $intent = null)
    {
        // initialize Validator, set data
        $v = new \Valitron\Validator($m->get());

        // validate all model fields
        foreach ($m->elements as $key=>$field) {
            if ($field instanceof ModelField) {
                $field->validate($v);
            }
        }

        // if errors then format them to fit atk4 error format
        if ($v->validate() !== true) {
            $errors = [];
            foreach ($v->errors() as $key => $e) {
                if (!isset($errors[$key])) {
                    $errors[$key] = array_pop($e);
                }
            }

            return $errors;
        }
    }
}
