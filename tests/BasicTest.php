<?php

namespace atk4\validate\tests;

use atk4\data\Model;
use atk4\validate\Controller;
use atk4\validate\ModelField;

class BasicTest extends \atk4\core\PHPUnit_AgileTestCase
{
    public $m;

    public function setUp()
    {
        $a = [];
        $p = new \atk4\data\Persistence_Array($a);
        $this->m = new \atk4\data\Model($p);

        // add controller to your model BEFORE adding fields
        $this->m->add(new \atk4\validate\Controller());

        // add model fields

        // name is required and at least 3 characters long
        $this->m->addField('name', [
            'validate' => ['required','lengthMin'=>3]
        ]);

        $this->m->addField('type', [
            'required' => true,
            'enum'     => ['dog', 'ball'],
        ]);

        $this->m->addField('tail_length', [
            'type'        => 'number',
            'validate_if' => ['type' => 'dog'], // only dogs have tail
            'validate'    => ['required','integer'], // if type=dog, then this field is required, otherwise it's not
        ]);
    }

    /**
     * @expectedException        \atk4\data\ValidationException
     */
    public function testEx1()
    {
        // name to short
        $this->m->save(['name'=>'a']);
    }

    /**
     * @expectedException        \atk4\data\ValidationException
     */
    public function testEx2()
    {
        // for dogs tail length is required field
        $this->m->save(['name'=>'Rex','type'=>'dog']);
    }

    public function testOk1()
    {
        // ball have no tail, so it's fine
        $this->m->save(['name'=>'Jumpy','type'=>'ball']);
    }

    public function testOk2()
    {
        // ball have no tail, so it's fine
        $this->m->save(['name'=>'Rex','type'=>'dog','tail_length'=>10]);
    }
}
