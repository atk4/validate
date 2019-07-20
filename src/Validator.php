<?php

// vim:ts=4:sw=4:et:fdm=marker:fdl=0

namespace atk4\validate;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\validate\Rules\ValueInList;
use atk4\validate\Rules\ValueNotInList;
use atk4\validate\Rules\ValueUnique;
use atk4\validate\Rules\ValueUniqueOther;

/**
 * Controller class for Agile Data model to enable validations.
 *
 * Use https://github.com/vlucas/valitron under the hood.
 *
 * $v = new \atk4\validate\Validator($model);
 */
class Validator
{
    /** @var Model */
    public $model;

    /**
     * Array of rules in following format which is natively supported by Valitron mapFieldsRules():
     *  [
     *      'foo' => [
     *          ['required'],
     *          ['integer', 'message'=>'test 1'],
     *      ],
     *      'bar' => [
     *          ['email'],
     *          ['lengthBetween', 4, 10, 'message'=>'test 2'],
     *      ],
     *  ];.
     *
     * @var array
     */
    public $rules = [];

    /**
     * Array of conditional rules in following format:
     *  [
     *      [$conditions, $then_rules, $else_rules],
     *  ].
     *
     * $conditions - array of conditions
     * $then_rules - array in $this->rules format which will be used if conditions are met
     * $else_rules - array in $this->rules format which will be used if conditions are not met
     *
     * @var array
     */
    public $if_rules = [];

    /**
     * Track value of Valitron Language for rules message.
     *
     * \Valitron\Validator rules are static and we need to add custom rules :
     *  - when is called $this->validate
     *  - when is called $this->validate and the Valitron language is changed
     *
     * There is no problem adding rules two times, because in
     * static method : \Valitron\Validator::add() rules are replaced in static keyValue array :
     *  - \Valitron\Validator::$_rules
     *  - \Valitron\Validator::$_ruleMessages
     *
     * @var string
     */
    protected $valitron_language;

    /**
     * Defined custom rules to load.
     *
     * @var array
     */
    public $custom_rules = [
        ValueInList::class,
        ValueNotInList::class,
        ValueUnique::class,
        ValueUniqueOther::class,
    ];

    /**
     * Initialization.
     *
     * @throws \atk4\core\Exception
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        if (!isset($model->validator)) {
            $model->validator = $this;
        }

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
    public function rule(string $field, $rules)
    {
        $this->rules[$field] = array_merge(
            isset($this->rules[$field]) ? $this->rules[$field] : [],
            $this->_normalizeRules($rules)
        );

        return $this;
    }

    /**
     * Set multiple rules.
     *
     * @param array $hash Array of [$field=>$rules]
     *
     * @return $this
     */
    public function rules(array $hash)
    {
        foreach ($hash as $field=>$rules) {
            $this->rule($field, $rules);
        }

        return $this;
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
    public function if(array $conditions, array $then_hash, array $else_hash = [])
    {
        $this->if_rules[] = [
            $conditions,
            $this->_normalizeRules($then_hash),
            $this->_normalizeRules($else_hash),
        ];

        return $this;
    }

    /**
     * Normalize rule-set.
     *
     * @param array|string|callable $rules
     *
     * @return array or arrays
     */
    protected function _normalizeRules($rules)
    {
        $rules = (array) $rules;
        foreach ($rules as $key => $rule) {
            $rules[$key] = (array) $rule;
        }

        return $rules;
    }

    /**
     * Runs all validations.
     *
     * @param Model $model
     * @param string $intent
     *
     * @return array|null
     */
    public function validate(Model $model, string $intent = null) :?array
    {
        // Validator reload rules
        // - if not loaded
        // - Valitron\Valitron Language is changed
        if ($this->valitron_language !== \Valitron\Validator::lang()) {
            foreach($this->custom_rules as $custom_rule_class) {
                $custom_rule_class::setup();
            }

            $this->valitron_language = \Valitron\Validator::lang();
        }

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

        // set up Valitron rules
        $v->mapFieldsRules($all_rules);

        // validate and if errors then format them to fit atk4 error format
        if ($v->validate() !== true) {
            $errors = [];
            foreach ($v->errors() as $key => $e) {
                if (!isset($errors[$key])) {
                    $errors[$key] = array_pop($e);
                }
            }

            return $errors;
        }

        return null;
    }
}
