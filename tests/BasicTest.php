<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Model;
use Atk4\Data\Schema\TestCase;
use Atk4\Validate\Validator;

class BasicTest extends TestCase
{
    public ?Model $m;

    public ?Validator $c;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDb([
            'validator_test' => [
                ['id' => 1, 'name' => 'ball', 'type' => 'ball', 'tail_length' => 0, 'age' => 30],
            ],
        ]);
    }

    protected function getModel(): Model
    {
        return new ModelTest($this->db);
    }

    protected function getValidator(Model $model): Validator
    {
        return new Validator($model);
    }

    /**
     * Name to short.
     */
    public function testSimple1(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->rule('name', ['required', ['lengthMin', 3]]);

        $err = $model->createEntity()->set('name', 'a')->validate();
        $this->assertSame(['name'], array_keys($err));
    }

    /**
     * Name required.
     */
    public function testSimple2(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->rule('name', ['required', ['lengthMin', 3]]);

        $err = $model->createEntity()->set('name', 'a')->validate();
        $this->assertSame(['name'], array_keys($err));
    }

    /**
     * Multiple errors.
     */
    public function testMultiple1(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->rules([
            'name' => ['required'],
            'age' => ['integer', ['min', 0], ['max', 99]],
            'tail_length' => ['integer', ['min', 0]],
        ]);

        $err = $model->createEntity()->setMulti([
            'name' => null,
            'age' => 10,
            'tail_length' => 5.45,
        ])->validate();

        $this->assertSame(['name', 'tail_length'], array_keys($err));
    }

    /**
     * Callback instead of rules.
     */
    public function testCallback1(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        // Age should be odd (nepāra skaitlis)
        $validator->rule('age', [
            [
                function ($field, $value, $params, $data) {
                    return $value % 2 !== 0;
                },
                'message' => 'Age should be odd',
            ],
        ]);

        $err = $model->createEntity()->setMulti([
            'age' => 10, // odd number, so should throw error
        ])->validate();

        $this->assertSame(['age' => 'Age should be odd'], $err); // error and custom message
    }

    /**
     * Conditional rules.
     */
    public function testIf(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->if(['type' => 'dog'], [
            // dogs require age and tail_length
            'age' => ['required'],
            'tail_length' => ['required'],
        ], [
            // balls should not have tail
            'tail_length' => [['equals', '']],
        ]);

        // ball don't require tail_length and age
        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
        ])->validate();
        $this->assertSame([], array_keys($err));

        // ball should not have tail_length
        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
            'tail_length' => 5,
        ])->validate();
        $this->assertSame(['tail_length'], array_keys($err));

        // dogs require age and tail_length
        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'tail_length' => 5, // age is not set
        ])->validate();
        $this->assertSame(['age'], array_keys($err));
    }

    /**
     * Mix rules.
     */
    public function testMix(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->rule('age', [['min', 3]]); // everything should have age at least 3
        $validator->if(['type' => 'dog'], [
            // dogs require age and age of dog should be less than 20
            'age' => ['required', ['max', 20]],
        ]);

        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
        ])->validate();
        $this->assertSame([], array_keys($err)); // age can be blank for balls

        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
            'age' => 2,
        ])->validate();
        $this->assertSame(['age'], array_keys($err)); // age must be at least 3 for everything if set

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
        ])->validate();
        $this->assertSame(['age'], array_keys($err)); // for dogs age is required

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 10,
        ])->validate();
        $this->assertSame([], array_keys($err)); // for dogs age 10 is ok

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 2,
        ])->validate();
        $this->assertSame(['age'], array_keys($err)); // for dogs also age should be at least 3

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 30,
        ])->validate();
        $this->assertSame(['age'], array_keys($err)); // for dogs age should be no more than 20
    }

    /**
     * Test custom message.
     */
    public function testMessage(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $validator->rule('age', [
            ['min', 3, 'message' => 'Common! {field} to small'],
            ['max', 5, 'message' => 'And now to big'],
        ]);

        $err = $model->createEntity()->setMulti([
            'age' => 2,
        ])->validate();
        $this->assertSame(['age' => 'Common! Age to small'], $err); // custom message here

        $err = $model->createEntity()->setMulti([
            'age' => 10,
        ])->validate();
        $this->assertSame(['age' => 'And now to big'], $err); // custom message here
    }

    public function testModelHookValidate(): void
    {
        $model = $this->getModel();
        $validator = $this->getValidator($model);

        $entity = $model->createEntity();

        $validator->rule('name', ['required', ['lengthMin', 3]]);

        $entity->setMulti([
            'name' => 'abcd',
            'type' => 'dog',
        ]);

        $err = $entity->validate();
        $this->assertSame([], $err);

        // will not raise exception for return an empty array in place of null
        $entity->save();
    }
}
