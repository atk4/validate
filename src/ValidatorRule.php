<?php

declare(strict_types=1);

namespace Atk4\Validate;

class ValidatorRule
{
    /**
     * @var array<string|int|string[]|callable>
     */
    private array $rule = [];
    private ?string $message = null;

    /**
     * @param array<string|int|string[]|callable> $rule
     */
    public function __construct(array $rule, string $message = null)
    {
        $this->setRule($rule);
        $this->setMessage($message);
    }

    /**
     * @param array<string|int|string[]|callable> $rule
     */
    private function setRule(array $rule): void
    {
        $this->rule = $rule;
    }

    private function setMessage(string $message = null): void
    {
        $this->message = $message;
    }

    /**
     * @return array<string|int|string[]|callable>
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
