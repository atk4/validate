<?php

namespace atk4\validate\tests;

use atk4\data\Model;
use atk4\validate\Validator;

class CustomRulesTest extends \atk4\schema\PHPUnit_SchemaTestCase
{
    
    public $m;
    
    public $c;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->m   = new TestModel($this->db);
    
        $migration = $this->getMigration($this->m);
        $migration->create();
        
        for ($a = 0; $a < 10; $a++) {
            
            $data = [
                'v_value' => 'v_' . $a,
                'o_value' => 'o_' . $a,
            ];
            
            $this->m->unload()->set($data)->save();
        }
        
        $this->m->unload();
    }
    
    // test ValueUnique
    
    public function testUnique()
    {
    
        $this->c = new TestableValidatorCalls($this->m);
        
        $this->c->rule('v_value', [
            ['atk4_value_unique', $this->m]
        ]);
        
        $data = [
            'v_value' => 'v_10',
            'o_value' => 'o_10',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals([], array_keys($err));
    }
    
    public function testUniqueError()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_unique', $this->m]
        ]);
        
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
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_unique_other', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_10',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals([], array_keys($err));
    }
    
    public function testUniqueOtherError()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_unique_other', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_1',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals(['v_value'], array_keys($err));
    }
    
    // test ValueInList
    public function testValueInList()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_in_list', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_1',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals([], array_keys($err));
    }
    
    public function testValueInListError()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_in_list', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_10',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals(['v_value'], array_keys($err));
    }
    
    // test ValueNotInList
    public function testValueNotInList()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_10',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals([], array_keys($err));
    }
    
    public function testValueNotInListError()
    {
        $this->c = new TestableValidatorCalls($this->m);
    
        $this->c->rule('v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_1',
        ];
        
        $err = $this->m->unload()->set($data)->validate();
        
        $this->assertEquals(['v_value'], array_keys($err));
    }
    
    public function testValueChangedLanguage()
    {
        $this->c = new TestableValidatorCalls($this->m);
        
        $this->c->rule('v_value', [
            ['atk4_value_not_in_list', $this->m, 'o_value']
        ]);
        
        $data = [
            'v_value' => 'o_1',
        ];
    
        \Valitron\Validator::lang('it');
    
        $saved_err = $this->m->unload()->set($data)->validate(); // load 1
    
        \Valitron\Validator::lang('en');
    
        $saved_err = $this->m->unload()->set($data)->validate(); // load 2
    
        \Valitron\Validator::lang('it');
    
        $saved_err = $this->m->unload()->set($data)->validate(); // load 3
    
        \Valitron\Validator::lang('en');
    
        $err = $this->m->unload()->set($data)->validate(); // load 4
        $err = $this->m->unload()->set($data)->validate(); // load 4
        $err = $this->m->unload()->set($data)->validate(); // load 4
        $err = $this->m->unload()->set($data)->validate(); // load 4
        $err = $this->m->unload()->set($data)->validate(); // load 4
        
        $this->assertNotEquals($saved_err['v_value'], $err['v_value']);
    
        $this->assertEquals(4,$this->c->getCalledLoad());
    }
    
    protected function tearDown()
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

class TestableValidatorCalls extends Validator {
    
    public static $calledLoad    = 0;
    
    public function getCalledLoad()
    {
        return static::$calledLoad;
    }
    
    protected static function _addCustomRuleDefinition()
    {
        $tryLoad = parent::_addCustomRuleDefinition();
        
        if($tryLoad)
        {
            static::$calledLoad++;
        }
        
        return $tryLoad;
    }
}
