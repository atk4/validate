<?php

declare(strict_types=1);

namespace Atk4\Validate;

use Atk4\Data\Exception;
use Atk4\Data\Model;

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
     * @var list<string|int|string[]|\Closure(string, mixed, list<mixed>, list<mixed>): bool>
     */
    private array $rule = [];
    private ?string $message = null;

    /**
     * @param string|array<string|int|string[]|\Closure(string, mixed, list<mixed>, list<mixed>): bool> $rule
     */
    public function __construct(string $field, $rule)
    {
        if (is_string($rule)) {
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
     * @param array<string|int|string[]|\Closure(string, mixed, list<mixed>, list<mixed>): bool> $rule
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
     * @return array<string|int|string[]|\Closure(string, mixed, list<mixed>, list<mixed>): bool>
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
     * @return array<string|array<string|int|string[]|\Closure(string, mixed, list<mixed>, list<mixed>): bool>>
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
