<?php

namespace atk4\validate\Rules;

interface iRule
{
    public static function getCallback($field, $value, array $params, array $fields): bool;
    
    public static function getMessages(): array;
}
