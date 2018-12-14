<?php

// vim:ts=4:sw=4:et:fdm=marker:fdl=0

namespace atk4\validate;

/**
 * Models which have validation controller added will use this field class.
 */
class ModelField extends \atk4\data\Field_SQL
{
    /**
     * Describes arbitrary validation rules.
     *
     * @var array
     */
    public $validate = [];

    /**
     * If this is set, then validation will only happen if all array keys (fields) will have value.
     *
     * @var array
     */
    public $validate_if = [];

    /**
     * Validate field value by using \Valitron\Validator.
     *
     * @param \Valitron\Validator $v
     */
    public function validate($v)
    {
        $should_validate = true;

        // if validate_if property is set, then validate only if all fields will have specific values set
        foreach ($this->validate_if as $field => $value) {
            $should_validate = $should_validate && ($this->owner[$field] == $value);
        }

        if ($this->validate && $should_validate) {
            if (is_array($this->validate)) {
                foreach ($this->validate as $rule=>$args) {
                    if (is_callable($args)) {
                        // callback
                        $args($v);
                    } elseif (is_int($rule)) {
                        // rule_name
                        $v->rule($args, $this->short_name);
                    } else {
                        // rule_name => [args]
                        $v->rule($rule, $this->short_name, $args);
                    }
                }
            } else {
                // rule_name
                $v->rule($this->validate, $this->short_name);
            }
        }
    }
}
