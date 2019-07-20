<?php

namespace atk4\validate\Rules;

use atk4\data\Exception;
use Closure;
use Valitron\Validator;

abstract class RuleAbstract
{
    public static $rule_id;
    public static $rule_messages = [];

    /**
     * Entry point for adding rule
     * you don't have to call this, it will be called one time :
     *  - in method atk4/validate->validate()
     *
     * it will be called another time :
     *  - if you have reset the language of \Valitron\Validate
     *
     * @throws Exception
     */
    public static function setup()
    {
        Validator::addRule(
            static::getRuleID(),
            Closure::fromCallable([static::class, "getCallback"]),
            static::getMessage()
        );
    }

    /**
     * Return the $rule_id of the Rule
     *
     * @return string
     * @throws Exception
     */
    protected static function getRuleID(): string
    {
        $name = static::$rule_id ?? false;
        if ($name !== false && !empty($name)) {
            return $name;
        }

        throw new Exception(['Rule $rule_id must be defined, unique and not empty']);
    }

    /**
     * Get localized error message for the rule
     *
     * @return string
     * @throws Exception
     */
    public static function getMessage(): string
    {
        $message = static::$rule_messages[Validator::lang()] ?? false;
        if ($message !== false) {
            return $message;
        }

        $message = static::$rule_messages['en'] ?? false;
        if ($message !== false) {
            return $message;
        }

        throw new Exception(['Rule must have at least an english translation']);
    }

    /**
     * The validation callback of the rule
     *
     * @param       $field
     * @param       $value
     * @param array $params
     * @param array $fields
     *
     * @return bool
     * @throws Exception
     */
    abstract public static function getCallback($field, $value, array $params, array $fields): bool;
}