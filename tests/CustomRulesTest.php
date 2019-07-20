<?php

namespace atk4\validate\tests;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\schema\PHPUnit_SchemaTestCase;
use atk4\validate\Rules\RuleAbstract;
use atk4\validate\Validator;

class CustomRulesTest extends PHPUnit_SchemaTestCase
{

    public $m;

    public $c;

    public function setUp()
    {
        parent::setUp();

        $this->m = new TestModel($this->db);

        $migration = $this->getMigration($this->m);
        $migration->create();

        for ($a = 0; $a < 10; $a++) {
            $data = [
                'v_value' => 'v_'.$a,
                'o_value' => 'o_'.$a,
            ];

            $this->m->unload()->set($data)->save();
        }

        $this->m->unload();
    }

    // test ValueUnique

    public function testUnique()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique', $this->m],
        ]
        );

        $data = [
            'v_value' => 'v_10',
            'o_value' => 'o_10',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals([], array_keys($err));
    }

    public function testUniqueError()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique', $this->m],
        ]
        );

        $data = [
            'v_value' => 'v_1',
            'o_value' => 'o_1',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals(['v_value'], array_keys($err));
    }

    // test ValueUniqueOther
    public function testUniqueOther()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique_other', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_10',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals([], array_keys($err));
    }

    public function testUniqueOtherError()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique_other', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals(['v_value'], array_keys($err));
    }

    // test ValueInList
    public function testValueInList()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_in_list', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals([], array_keys($err));
    }

    public function testValueInListError()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_in_list', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_10',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals(['v_value'], array_keys($err));
    }

    // test ValueNotInList
    public function testValueNotInList()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_10',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals([], array_keys($err));
    }

    public function testValueNotInListError()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $err = $this->m->unload()->set($data)->validate();

        $this->assertEquals(['v_value'], array_keys($err));
    }

    public function testValueChangedLanguage()
    {
        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        \Valitron\Validator::lang('it');

        $validate_it = $this->m->unload()->set($data)->validate();

        \Valitron\Validator::lang('en');

        $validate_en = $this->m->unload()->set($data)->validate();

        $this->assertNotEquals($validate_en['v_value'], $validate_it['v_value']);
    }

    public function testException_InList_count()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_InList_1()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list', null, null],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_InList_2()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_not_in_list', $this->m, null]
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_unique_count()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_unique_1()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique', null],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    // unique other

    public function testException_unique_other_count()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique_other'],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_unique_other_1()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique_other', null, null],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testException_unique_other_2()
    {
        $this->expectException(Exception::class);

        $this->c = new Validator($this->m);

        $this->c->rule(
            'v_value', [
            ['atk4_value_unique_other', $this->m, null],
        ]
        );

        $data = [
            'v_value' => 'o_1',
        ];

        $this->m->unload()->set($data)->validate();
    }

    public function testRuleNoID()
    {
        $this->expectException(Exception::class);
        RuleTestID::setup();
    }

    public function testRuleNoMessage()
    {
        $this->expectException(Exception::class);
        RuleTestNoMessage::setup();
    }

    public function testRuleMessageFallback()
    {
        \Valitron\Validator::lang('it');
        $message = RuleTestFallbackMessage::getMessage();
        $this->assertEquals('fallback error', $message);
    }

    public function tearDown()
    {
        $m = new TestModel($this->db);
        $this->dropTable($m->table);
    }
}

class TestModel extends Model
{
    public $table = 'model_table';

    public function init()
    {
        parent::init();

        $this->addField('v_value');
        $this->addField('o_value');
    }
}

class RuleTestID extends RuleAbstract
{

    /**
     * defined only to check exceptions in test.
     */
    public static function getCallback($field, $value, array $params, array $fields): bool
    {
        return true;
    }
}

class RuleTestNoMessage extends RuleTestID
{
    public static $rule_id = 'test_rule';
}

class RuleTestFallbackMessage extends RuleTestNoMessage
{
    public static $rule_messages = [
        'en' => 'fallback error',
    ];
}