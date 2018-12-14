<?php

// vim:ts=4:sw=4:et:fdm=marker:fdl=0

namespace atk4\validate;

/**
 * Controller class for Agile Data model to enable validations.
 *
 * Use https://github.com/vlucas/valitron under the hood.
 *
 * $v = new \atk4\validate\Validator($model);
 */
class Validator
{
    /** @var \atk4\data\Model */
    public $model;

    /** @var array of rules */
    public $rules = [];

    /** @var array conditional rules */
    public $if_rules = [];

    /**
     * Initialization.
     */
    public function __construct(\atk4\data\Model $model)
    {
        $this->model = $model;
        $model->validator = $this;
        $model->addHook('validate', $this);
    }

    /**
     * Set one rule.
     *
     * @param string                $field
     * @param array|string|callable $rules
     *
     * @return $this
     */
    public function rule($field, $rules)
    {
        $rules = $this->_normalizeRules($rules);

        // @todo Here we could use array_merge to allow to call rule() multiple times and append rules
        $this->rules[$field] = $rules;

        return $this;
    }

    /**
     * Set multiple rules.
     *
     * @param array $hash Array of [$field=>$rules]
     *
     * @return $this
     */
    public function rules($hash)
    {
        foreach ($hash as $field=>$rules) {
            $this->rule($field, $rules);
        }

        return $this;
    }

    /**
     * Normalize rule-set.
     *
     * @param array|string|callable $rules
     *
     * @return array
     */
    protected function _normalizeRules($rules)
    {
        return is_array($rules) ? $rules : [$rules];
    }

    /**
     * Set conditional rules.
     *
     * @param array $conditions
     * @param array $then_hash
     * @param array $else_hash
     *
     * @return $this
     */
    public function if($conditions, $then_hash, $else_hash = [])
    {
        $this->if_rules[] = [
            $conditions,
            $this->_normalizeRules($then_hash),
            $this->_normalizeRules($else_hash),
        ];

        return $this;
    }

    /**
     * Runs all validations.
     *
     * @param \atk4\data\Model $model
     * @param string           $intent
     *
     * @return array|null
     */
    public function validate(\atk4\data\Model $model, $intent = null)
    {
        // initialize Validator, set data
        $v = new \Valitron\Validator($model->get());

        // prepare array of all rules we have to validate
        // this should also include respective rules from $this->if_rules.
        $all_rules = $this->rules;

        foreach ($this->if_rules as $row) {
            list($conditions, $then_hash, $else_hash) = $row;

            $test = true;
            foreach ($conditions as $field=>$value) {
                $test = $test && ($model[$field] == $value);
            }

            $all_rules = array_merge_recursive($all_rules, $test ? $then_hash : $else_hash);
        }

        // validate rules
        foreach ($all_rules as $field=>$rules) {

            // use rule key 'message' to pass custom message
            $message = null;
            if (isset($rules['message'])) {
                $message = $rules['message'];
                unset($rules['message']);
            }

            // set up all rules
            foreach ($rules as $key=>$rule) {
                $r = null;
                if (is_callable($rule)) {
                    // parameters - field_name, value, \Valitron\Validator
                    call_user_func_array($rule, [$field, $model->get($field), $v]);
                } elseif (is_int($key)) {
                    // just rule name, like 'required'
                    $r = $v->rule($rule, $field);
                } else {
                    // rule with argument like 'min'=>10
                    $r = $v->rule($key, $field, $rule);
                }
                if ($r && $message) {
                    $r->message($message)/*->label('Name')*/;
                }
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
