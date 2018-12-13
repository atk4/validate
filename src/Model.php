<?php
namespace atk4\validate\Model;

class Model extends \atk4\data\Model
{
    function init()
    {
        parent::init();
        $this->_default_seed_addField = ['\atk4\validate\ModelField'];
    }

    function validate($intent = null) {
        $v = new \Valitron\Validator($this->get());

        foreach($this->elements as $key=>$field) {
            if (!$field instanceof \saasty\ModelField) {
                continue;
            }
            $field->validate($v);
        }

        if ($v->validate()===true) {
            return parent::validate($intent);
        }


        $errors = parent::validate($intent);

        foreach($v->errors() as $key=>$e){
            if (!isset($errors[$key])) {
                $errors[$key] = array_pop($e);
            }
        }

        return $errors;
    }
}
