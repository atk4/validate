<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Data\Exception;
use Atk4\Data\Model;

/**
 * https://github.com/vlucas/valitron/blob/v1.4.11/src/Valitron/Validator.php#L1251.
 *
 * @phpstan-type ValidatorCallback \Closure(string, mixed, list<mixed>, list<mixed>): bool
 * @phpstan-type ValidatorCondition array<string, mixed>
 *
 * https://github.com/vlucas/valitron/blob/master/src/Valitron/Validator.php#L1240
 * @phpstan-type ValitronRuleType 'accepted'|'alpha'|'alphaNum'|'array'|'arrayHasKeys'|'ascii'|'between'|'boolean'|'contains'|'containsUnique'|'creditCard'|'date'|'dateAfter'|'dateBefore'|'dateFormat'|'different'|'email'|'emailDNS'|'equals'|'in'|'instanceOf'|'integer'|'ip'|'ipv4'|'ipv6'|'length'|'lengthBetween'|'lengthMax'|'lengthMin'|'listContains'|'max'|'min'|'notIn'|'numeric'|'optional'|'regex'|'required'|'requiredWith'|'requiredWithout'|'slug'|'subset'|'url'|'urlActive'
 * @phpstan-type ValitronRule array<int|'message', ValitronRuleType|int|string|list<string>|ValidatorCallback>
 */
class ValidatorRule
{
    public const ON_SUCCESS = 'success';
    public const ON_FAIL = 'fail';

    public string $field;

    /**
     * @var ValidatorCondition
     */
    public array $activateConditions = [];
    public ?string $activateOn = null;

    /**
     * @var list<ValitronRule>
     */
    private array $rule = [];
    private ?string $message = null;

    /**
     * @param ValitronRuleType|ValitronRule|ValidatorCallback $rule
     */
    public function __construct(string $field, $rule)
    {
        if (!is_array($rule)) {
            $rule = [$rule];
        }

        $this->field = $field;

        $message = $rule['message'] ?? null;
        if (isset($rule['message'])) {
            unset($rule['message']);
        }

        $this->setRule($rule);
        $this->setMessage($message);
    }

    /**
     * @param ValidatorCondition $activationConditions [field_name => value]
     */
    public function setActivateOnSuccess(array $activationConditions): void
    {
        $this->setActivateOnResult(self::ON_SUCCESS, $activationConditions);
    }

    /**
     * @param ValidatorCondition $activationConditions [field_name => value]
     */
    public function setActivateOnFail(array $activationConditions): void
    {
        $this->setActivateOnResult(self::ON_FAIL, $activationConditions);
    }

    /**
     * @param ValidatorCondition $activationConditions [field_name => value]
     */
    public function setActivateOnResult(string $activateOn, array $activationConditions): void
    {
        if ($this->activateOn !== null) {
            throw new Exception('Activation condition already set');
        }

        $this->activateOn = $activateOn;
        $this->activateConditions = $activationConditions;
    }

    public function isActivated(Model $model): bool
    {
        $this->activateOn ??= self::ON_SUCCESS;

        foreach ($this->activateConditions as $conditionField => $conditionValue) {
            if ($this->activateOn === self::ON_SUCCESS && !$model->compare($conditionField, $conditionValue)) {
                return false;
            }

            if ($this->activateOn === self::ON_FAIL && $model->compare($conditionField, $conditionValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<ValitronRule> $rule
     */
    private function setRule(array $rule): void
    {
        $this->rule = $rule;
    }

    private function setMessage(?string $message = null): void
    {
        $this->message = $message;
    }

    /**
     * @return list<ValitronRule>
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return list<ValitronRule>
     */
    public function getValitronRule(): array
    {
        $rule = $this->getRule();
        if ($this->getMessage() !== null) {
            $rule['message'] = $this->getMessage();
        }

        return $rule;
    }
}
