<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Core\WarnDynamicPropertyTrait;
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
    use WarnDynamicPropertyTrait;

    /**
     * @var ValidatorRules[]
     */
    public array $rules = [];

    public function __construct(Model $model)
    {
        $model->onHook(Model::HOOK_VALIDATE, \Closure::fromCallable([$this, 'validate']));
    }

    /**
     * Set one rule.
     *
     * @param array<string|array<string|int|string[]|callable>> $rules
     *
     * @return $this
     */
    public function rule(string $field, array $rules): self
    {
        foreach ($rules as $rule) {
            $this->addValidationRules(new ValidatorRules($field, $rule));
        }

        return $this;
    }

    public function addValidationRules(ValidatorRules $validationRules): void
    {
        $this->rules[] = $validationRules;
    }

    /**
     * Set multiple rules.
     *
     * @param array<string, array<string|array<string|int|string[]|callable>>> $hash array with field name as key and rules as value
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
     *
     * @param array<string, string|int|bool>|array<array<string, string|int|bool>>    $conditions
     * @param array<string, string|array<string|array<string|int|string[]|callable>>> $then_hash
     * @param array<string, string|array<string|array<string|int|string[]|callable>>> $else_hash
     *
     * @return $this
     */
    public function if(array $conditions, array $then_hash, array $else_hash = []): self
    {
        foreach ($then_hash as $field => $rules) {
            foreach ($rules as $rule) {
                $validatorRules = new ValidatorRules($field, $rule);
                $validatorRules->setActivationConditionsSuccess($conditions);
                $this->addValidationRules($validatorRules);
            }
        }

        foreach ($else_hash as $field => $rules) {
            foreach ($rules as $rule) {
                $validatorRules = new ValidatorRules($field, $rule);
                $validatorRules->setActivationConditionsFail($conditions);
                $this->addValidationRules($validatorRules);
            }
        }

        return $this;
    }

    /**
     * Runs all validations and return an array with validation errors.
     *
     * @return array<string, string> array with field name as key and error message as value
     */
    public function validate(Model $model, string $intent = null): array
    {
        // initialize Validator, set data
        $v = new \Valitron\Validator($model->get());

        $rules = [];
        foreach ($this->rules as $rule) {
            if ($rule->isActivated($model) === false) {
                continue;
            }
            $rules[$rule->field][] = $rule->rule->getRule();
        }

        // set up Valitron rules
        $v->mapFieldsRules($rules);

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
