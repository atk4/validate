<?php

namespace atk4\validate\Rules;

use Valitron\Validator;

/**
 * Class aRule
 * has an abstract must be left alone to his destiny :)
 *
 * and extend it like this :
 *
 * class RuleName extends aRule implements iRule {
 *
 *      public static function getCallback($field, $value, array $params, array $fields): bool
 *      {
 *
 *      }
 *
 *      public static function getMessages(): array;
 *      {
 *
 *      }
 * }
 *
 * @package atk4\validate\Rules
 */
abstract class aRule
{
    /**
     * Entry point for adding rule
     * you don't have to call this, it will be called one time :
     *  - in method atk4/validate->validate()
     *
     * it will be called another time :
     *  - if you have reset the language of \Valitron\Validate
     *
     * @throws \Exception
     */
    public static function setup()
    {
        Validator::addRule(
            static::getName(), [
                static::class,
                "getCallback",
            ],
            static::getMessage()
        );
    }
    
    /**
     * return the name of the rule, using classname of the rule decamelized
     *
     * atk4ValueInList => atk4_value_in_list
     *
     * @return string
     */
    protected static function getName()
    {
        $path      = explode('\\', static::class);
        $ClassName = array_pop($path);
        
        return 'atk4_' . static::decamelize($ClassName);
    }
    
    /**
     * decamelize class name
     *
     * @return string
     */
    private static function decamelize($string)
    {
        return strtolower(preg_replace([
                                           '/([a-z\d])([A-Z])/',
                                           '/([^_])([A-Z][a-z])/',
                                       ], '$1_$2', $string));
    }
    
    /**
     * get localized validation error message from extended Custom rule
     *
     * @return string
     * @throws \Exception
     */
    public static function getMessage(): string
    {
        $validatorLanguage = Validator::lang();
        $messages          = static::getMessages();
        
        if (isset($messages[$validatorLanguage])) {
            return $messages[$validatorLanguage];
        }
        
        
        if (!isset($messages['en'])) {
            throw new \Exception('Rule must have at least english translation');
        }
        
        return $messages['en'];
    }
    
    /**
     * add here only to not have error in QC
     *
     * if rule is extended correctly this will not be called
     * if called throw exception
     *
     * @param       $field
     * @param       $value
     * @param array $params
     * @param array $fields
     *
     * @return bool
     * @throws \Exception
     */
    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        throw new \Exception('getCallback must be implemented in extended aRule class');
    }
}
