<?php

namespace atk4\validate\tests;

class BasicTest extends \atk4\core\PHPUnit_AgileTestCase
{
    public $m;
    public $c;

    public function setUp()
    {
        $a = [];
        $p = new \atk4\data\Persistence_Array($a);
        $this->m = $m = new \atk4\data\Model($p);

        $m->addField('name');
        $m->addField('age', ['type' => 'number']);
        $m->addField('type', ['required' => true, 'enum' => ['dog', 'ball']]);
        $m->addField('tail_length', ['type' => 'number']);

        $this->c = new \atk4\validate\Validator($m);
    }

    /**
     * Name to short.
     */
    public function testSimple1()
    {
        $this->c->rule('name', ['required', ['lengthMin', 3]]);

        $err = $this->m->unload()->set(['name'=>'a'])->validate();
        $this->assertEquals(['name'], array_keys($err));
    }

    /**
     * Name required.
     */
    public function testSimple2()
    {
        $this->c->rule('name', ['required', ['lengthMin', 3]]);

        $err = $this->m->unload()->set(['name'=>'a'])->validate();
        $this->assertEquals(['name'], array_keys($err));
    }

    /**
     * Multiple errors.
     */
    public function testMultiple1()
    {
        $this->c->rules([
            'name'        => 'required',
            'age'         => ['integer', ['min', 0], ['max', 99]],
            'tail_length' => ['integer', ['min', 0]],
        ]);

        $err = $this->m->unload()->set([
            'name'        => null,
            'age'         => 10,
            'tail_length' => 5.45,
        ])->validate();

        $this->assertEquals(['name', 'tail_length'], array_keys($err));
    }

    /**
     * Callback instead of rules.
     */
    public function testCallback1()
    {
        // Age should be odd (nepāra skaitlis)
        $this->c->rule('age', [
            [
                function ($field, $value, $params, $data) {
                    return $value % 2 != 0;
                },
                'message' => 'Age should be odd',
            ],
        ]);

        $err = $this->m->unload()->set([
            'age' => 10, // odd number, so should throw error
        ])->validate();

        $this->assertEquals(['age'=>'Age should be odd'], $err); // error and custom message
    }

    /**
     * Conditional rules.
     */
    public function testIf()
    {
        $this->c->if(['type'=>'dog'], [
            // dogs require age and tail_length
            'age'         => ['required'],
            'tail_length' => ['required'],
        ], [
            // balls should not have tail
            'tail_length' => [['equals', '']],
        ]);

        // ball don't require tail_length and age
        $err = $this->m->unload()->set([
            'type' => 'ball',
        ])->validate();
        $this->assertEquals([], array_keys($err));

        // ball should not have tail_length
        $err = $this->m->unload()->set([
            'type'        => 'ball',
            'tail_length' => 5,
        ])->validate();
        $this->assertEquals(['tail_length'], array_keys($err));

        // dogs require age and tail_length
        $err = $this->m->unload()->set([
            'type'        => 'dog',
            'tail_length' => 5, // age is not set
        ])->validate();
        $this->assertEquals(['age'], array_keys($err));
    }

    /**
     * Mix rules.
     */
    public function testMix()
    {
        $this->c->rule('age', [['min', 3]]); // everything should have age at least 3
        $this->c->if(['type'=>'dog'], [
            // dogs require age and age of dog should be less than 20
            'age' => ['required', ['max', 20]],
        ]);

        $err = $this->m->unload()->set([
            'type' => 'ball',
        ])->validate();
        $this->assertEquals([], array_keys($err)); // age can be blank for balls

        $err = $this->m->unload()->set([
            'type' => 'ball',
            'age'  => 2,
        ])->validate();
        $this->assertEquals(['age'], array_keys($err)); // age must be at least 3 for everything if set

        $err = $this->m->unload()->set([
            'type' => 'dog',
        ])->validate();
        $this->assertEquals(['age'], array_keys($err)); // for dogs age is required

        $err = $this->m->unload()->set([
            'type' => 'dog',
            'age'  => 10,
        ])->validate();
        $this->assertEquals([], array_keys($err)); // for dogs age 10 is ok

        $err = $this->m->unload()->set([
            'type' => 'dog',
            'age'  => 2,
        ])->validate();
        $this->assertEquals(['age'], array_keys($err)); // for dogs also age should be at least 3

        $err = $this->m->unload()->set([
            'type' => 'dog',
            'age'  => 30,
        ])->validate();
        $this->assertEquals(['age'], array_keys($err)); // for dogs age should be no more than 20
    }

    /**
     * Test custom message.
     */
    public function testMessage()
    {
        $this->c->rule('age', [
            ['min', 3, 'message'=>'Common! {field} to small'],
            ['max', 5, 'message'=>'And now to big'],
        ]);

        $err = $this->m->unload()->set([
            'age'  => 2,
        ])->validate();
        $this->assertEquals(['age'=>'Common! Age to small'], $err); // custom message here

        $err = $this->m->unload()->set([
            'age'  => 10,
        ])->validate();
        $this->assertEquals(['age'=>'And now to big'], $err); // custom message here
    }
}
