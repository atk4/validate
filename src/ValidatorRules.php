<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Data\Exception;
use Atk4\Data\Model;

class ValidatorRules
{
    public const SUCCESS = 'success';
    public const FAIL = 'fail';
    public string $field;

    /**
     * @var array<array<string, string|int|bool>>
     */
    public array $activateConditions = [];
    public ?string $activateRule = null;

    public ValidatorRule $rule;

    /**
     * @param string|array<string|int|string[]|callable> $rule
     */
    public function __construct(string $field, $rule)
    {
        if (is_string($rule)) {
            $rule = [$rule];
        }

        $this->field = $field;

        $this->setRule($rule, $rule['message'] ?? null);
    }

    /**
     * @param array<string|int|string[]|callable> $rule
     */
    public function setRule(array $rule, string $message = null): void
    {
        $this->rule = new ValidatorRule($rule, $message);
    }

    /**
     * @param array<array<string, string|int|bool>> $activationConditions
     */
    public function setActivationConditionsSuccess(array $activationConditions): void
    {
        $this->setActivateOnResult(self::SUCCESS);
        $this->activateConditions = $activationConditions;
    }

    /**
     * @param array<array<string, string|int|bool>> $activationConditions
     */
    public function setActivationConditionsFail(array $activationConditions): void
    {
        $this->setActivateOnResult(self::FAIL);
        $this->activateConditions = $activationConditions;
    }

    private function setActivateOnResult(string $activateRule): void
    {
        if ($this->activateRule !== null) {
            throw new Exception('Activation rule already set');
        }

        $this->activateRule = $activateRule;
    }

    public function isActivated(Model $model): bool
    {
        $this->activateRule = $this->activateRule ?? self::SUCCESS;

        foreach ($this->activateConditions as $conditionField => $conditionValue) {
            if ($this->activateRule === self::SUCCESS && $model->get($conditionField) !== $conditionValue) {
                return false;
            }

            if ($this->activateRule === self::FAIL && $model->get($conditionField) === $conditionValue) {
                return false;
            }
        }

        return true;
    }
}
