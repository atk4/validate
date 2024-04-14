<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Data\Exception;
use Atk4\Data\Model;

/**
 * One validator rule.
 *
 * https://github.com/vlucas/valitron/blob/v1.4.11/src/Valitron/Validator.php#L1251
 *
 * @phpstan-type ValidatorCallback \Closure(string, mixed, list<mixed>, list<mixed>): bool
 *
 * https://github.com/vlucas/valitron/blob/master/src/Valitron/Validator.php#L1240
 * @phpstan-type ValitronRuleType 'required'|'equals'|'different'|'accepted'|'array'|'numeric'|'integer'|'length'|'lengthBetween'|'lengthMin'|'lengthMax'|'min'|'max'|'between'|'in'|'listContains'|'notIn'|'contains'|'subset'|'containsUnique'|'ip'|'ipv4'|'ipv6'|'email'|'ascii'|'emailDNS'|'url'|'urlActive'|'alpha'|'alphaNum'|'slug'|'regex'|'date'|'dateFormat'|'dateBefore'|'dateAfter'|'boolean'|'creditCard'|'instanceOf'|'requiredWith'|'requiredWithout'|'optional'|'arrayHasKeys'
 * @phpstan-type ValitronRule array<int|'message', ValitronRuleType|int|string|string[]|ValidatorCallback>
 */
class ValidatorRule
{
    public const ON_SUCCESS = 'success';
    public const ON_FAIL = 'fail';
    public string $field;

    /**
     * @var array<string, mixed>
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
     * @param array<string, mixed> $activationConditions [field_name => value]
     */
    public function setActivateOnSuccess(array $activationConditions): void
    {
        $this->setActivateOnResult(self::ON_SUCCESS, $activationConditions);
    }

    /**
     * @param array<string, mixed> $activationConditions [field_name => value]
     */
    public function setActivateOnFail(array $activationConditions): void
    {
        $this->setActivateOnResult(self::ON_FAIL, $activationConditions);
    }

    /**
     * @param array<string, mixed> $activationConditions [field_name => value]
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
            if ($this->activateOn === self::ON_SUCCESS && $model->get($conditionField) !== $conditionValue) {
                return false;
            }

            if ($this->activateOn === self::ON_FAIL && $model->get($conditionField) === $conditionValue) {
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
