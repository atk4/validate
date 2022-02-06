<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Data\Model;

/**
 * Controller class for Agile Data model to enable validations.
 *
 * Use https://github.com/vlucas/valitron under the hood.
 *
 * $v = new \Atk4\Validate\Validator($model);
 */
class Validator
{
    public Model $model;

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
     */
    public array $rules = [];

    /**
     * Array of conditional rules in following format:
     *  [
     *      [$conditions, $then_rules, $else_rules],
     *  ].
     *
     * $conditions - array of conditions
     * $then_rules - array in $this->rules format which will be used if conditions are met
     * $else_rules - array in $this->rules format which will be used if conditions are not met
     */
    public array $if_rules = [];

    public function __construct(Model $model)
    {
        $this->model = $model;

        $model->onHook(Model::HOOK_VALIDATE, \Closure::fromCallable([$this, 'validate']));
    }

    /**
     * Set one rule.
     */
    public function rule(string $field, array $rules): self
    {
        $this->rules[$field] = array_merge(
            $this->rules[$field] ?? [],
            $this->_normalizeRules($rules)
        );

        return $this;
    }

    /**
     * Set multiple rules.
     *
     * @param array<string, array> $hash array with field name as key and rules as value
     *
     * @return $this
     */
    public function rules(array $hash): self
    {
        foreach ($hash as $field => $rules) {
            $this->rule($field, $rules);
        }

        return $this;
    }

    /**
     * Set conditional rules.
     */
    public function if(array $conditions, array $then_hash, array $else_hash = []): self
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
    protected function _normalizeRules($rules): array
    {
        $rules = (array) $rules;
        foreach ($rules as $key => $rule) {
            $rules[$key] = (array) $rule;
        }

        return $rules;
    }

    /**
     * Runs all validations and return an array with validation errors.
     */
    public function validate(Model $model, string $intent = null): array
    {
        // initialize Validator, set data
        $v = new \Valitron\Validator($model->get());

        // prepare array of all rules we have to validate
        // this should also include respective rules from $this->if_rules.
        $all_rules = $this->rules;

        foreach ($this->if_rules as $row) {
            [$conditions, $then_hash, $else_hash] = $row;

            $test = true;
            foreach ($conditions as $field => $value) {
                $test = $test && ($model->get($field) === $value);
            }

            $all_rules = array_merge_recursive($all_rules, $test ? $then_hash : $else_hash);
        }

        // set up Valitron rules
        $v->mapFieldsRules($all_rules);

        // validate and if errors then format them to fit Atk4 error format
        if ($v->validate() === true) {
            return [];
        }

        $errors = [];
        foreach ($v->errors() as $key => $e) {
            if (!isset($errors[$key])) {
                $errors[$key] = array_pop($e);
            }
        }

        return $errors;
    }
}
