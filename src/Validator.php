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
 *
 * https://github.com/vlucas/valitron/blob/v1.4.11/src/Valitron/Validator.php#L1251 https://github.com/phpstan/phpstan/issues/10874
 *
 * @phpstan-type ValidatorCallback \Closure(string, mixed, list<mixed>, list<mixed>): bool
 * @phpstan-type ValidatorCondition array<string, mixed>
 *
 * https://github.com/vlucas/valitron/blob/master/src/Valitron/Validator.php#L1240 https://github.com/phpstan/phpstan/issues/10874
 * @phpstan-type ValitronRuleType 'accepted'|'alpha'|'alphaNum'|'array'|'arrayHasKeys'|'ascii'|'between'|'boolean'|'contains'|'containsUnique'|'creditCard'|'date'|'dateAfter'|'dateBefore'|'dateFormat'|'different'|'email'|'emailDNS'|'equals'|'in'|'instanceOf'|'integer'|'ip'|'ipv4'|'ipv6'|'length'|'lengthBetween'|'lengthMax'|'lengthMin'|'listContains'|'max'|'min'|'notIn'|'numeric'|'optional'|'regex'|'required'|'requiredWith'|'requiredWithout'|'slug'|'subset'|'url'|'urlActive'
 * @phpstan-type ValitronRule array<int|'message', ValitronRuleType|int|string|list<string>|ValidatorCallback>
 */
class Validator
{
    use WarnDynamicPropertyTrait;

    /**
     * @var list<ValidatorRule>
     */
    public array $rules = [];

    public function __construct(Model $model)
    {
        $model->onHook(Model::HOOK_VALIDATE, \Closure::fromCallable([$this, 'validate']));
    }

    /**
     * Add rule/rules for given field.
     *
     * @param array<string|ValitronRule> $rules
     * @param ValidatorCondition         $conditions
     *
     * @return $this
     */
    public function rule(string $field, array $rules, ?string $activateOn = null, array $conditions = []): self
    {
        foreach ($rules as $rule) {
            $validatorRule = new ValidatorRule($field, $rule);
            if ($activateOn !== null) {
                $validatorRule->setActivateOnResult($activateOn, $conditions);
            }
            $this->addRule($validatorRule);
        }

        return $this;
    }

    /**
     * Set one rule.
     *
     * @return $this
     */
    public function addRule(ValidatorRule $validatorRule): self
    {
        $this->rules[] = $validatorRule;

        return $this;
    }

    /**
     * Set multiple rules.
     *
     * @param array<string, list<string|ValitronRule>> $hash array with field name as key and rules as value
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
     * @param ValidatorCondition                              $conditions
     * @param array<string, string|list<string|ValitronRule>> $then_hash
     * @param array<string, string|list<string|ValitronRule>> $else_hash
     *
     * @return $this
     */
    public function if(array $conditions, array $then_hash, array $else_hash = []): self
    {
        foreach ($then_hash as $field => $rules) {
            $this->rule($field, $rules, ValidatorRule::ON_SUCCESS, $conditions);
        }

        foreach ($else_hash as $field => $rules) {
            $this->rule($field, $rules, ValidatorRule::ON_FAIL, $conditions);
        }

        return $this;
    }

    /**
     * Runs all validations and return an array with validation errors.
     *
     * @return array<string, string> array of errors in format: [field_name => error_message]
     */
    public function validate(Model $model): array
    {
        // initialize Validator, set data
        $v = new ValitronValidator($model->get());

        $rules = [];
        foreach ($this->rules as $rule) {
            if ($rule->isActivated($model) === true) {
                $rules[$rule->field][] = $rule->getValitronRule();
            }
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
