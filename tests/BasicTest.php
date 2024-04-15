<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Data\Schema\TestCase;
use Atk4\Validate\Tests\Model\Dummy;
use Atk4\Validate\Validator;
use Atk4\Validate\ValidatorRule;

class BasicTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDb([
            'validator_dummy' => [
                1 => [
                    'name' => 'John',
                    'age' => 22,
                    'type' => 'dog',
                    'tail_length' => 5,
                    'dob' => '2024-01-01',
                ],
            ],
        ]);
    }

    protected function createModel(): Model
    {
        return new Dummy($this->db);
    }

    protected function createValidator(Model $model): Validator
    {
        return new Validator($model);
    }

    /**
     * Name to short.
     */
    public function testSimple1(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('name', ['required', ['lengthMin', 3]]);

        $err = $model->createEntity()->set('name', 'a')->validate();
        self::assertSame(['name'], array_keys($err));
    }

    /**
     * Name required.
     */
    public function testSimple2(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('name', ['required', ['lengthMin', 3]]);

        $err = $model->createEntity()->setNull('name')->validate();
        self::assertSame(['name'], array_keys($err));
    }

    /**
     * Multiple errors.
     */
    public function testMultiple1(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

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

        self::assertSame(['name', 'tail_length'], array_keys($err));
    }

    /**
     * Callback instead of rules.
     */
    public function testCallback1(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        // Age should be odd number
        $validator->rule('age', [
            [
                static function ($field, $value, $params, $data): bool {
                    return $value % 2 !== 0;
                },
                'message' => 'Age should be odd',
            ],
        ]);

        $err = $model->createEntity()->setMulti([
            'age' => 10, // odd number, so should throw error
        ])->validate();

        self::assertSame(['age' => 'Age should be odd'], $err); // error and custom message
    }

    /**
     * Conditional rules.
     */
    public function testIf(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

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
        self::assertSame([], array_keys($err));

        // ball should not have tail_length
        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
            'tail_length' => 5,
        ])->validate();
        self::assertSame(['tail_length'], array_keys($err));

        // dogs require age and tail_length
        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'tail_length' => 5, // age is not set
        ])->validate();
        self::assertSame(['age'], array_keys($err));
    }

    /**
     * Mix rules.
     */
    public function testMix(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('age', [['min', 3]]); // everything should have age at least 3
        $validator->if(['type' => 'dog'], [
            // dogs require age and age of dog should be less than 20
            'age' => ['required', ['max', 20]],
        ]);

        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
        ])->validate();
        self::assertSame([], array_keys($err)); // age can be blank for balls

        $err = $model->createEntity()->setMulti([
            'type' => 'ball',
            'age' => 2,
        ])->validate();
        self::assertSame(['age'], array_keys($err)); // age must be at least 3 for everything if set

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
        ])->validate();
        self::assertSame(['age'], array_keys($err)); // for dogs age is required

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 10,
        ])->validate();
        self::assertSame([], array_keys($err)); // for dogs age 10 is ok

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 2,
        ])->validate();
        self::assertSame(['age'], array_keys($err)); // for dogs age should be at least 3

        $err = $model->createEntity()->setMulti([
            'type' => 'dog',
            'age' => 30,
        ])->validate();
        self::assertSame(['age'], array_keys($err)); // for dogs age should be no more than 20
    }

    /**
     * Test custom message.
     */
    public function testMessage(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('age', [
            ['min', 3, 'message' => 'Common! {field} to small'],
            ['max', 5, 'message' => 'And now to big'],
        ]);

        $err = $model->createEntity()->setMulti([
            'age' => 2,
        ])->validate();
        self::assertSame(['age' => 'Common! Age to small'], $err); // custom message here

        $err = $model->createEntity()->setMulti([
            'age' => 10,
        ])->validate();
        self::assertSame(['age' => 'And now to big'], $err); // custom message here
    }

    public function testModelHookValidate(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);
        $validator->rule('name', ['required', ['lengthMin', 3, 'message' => 'Name to short']]);

        $entity = $model->createEntity()->setMulti([
            'name' => 'abcd',
            'type' => 'dog',
        ]);

        // will not raise exception for return an empty array in place of null
        $entity->save();

        $entity = $model->createEntity()->setMulti([
            'name' => 'a',
            'type' => 'dog',
        ]);

        // will raise exception because name to short
        self::expectException(Exception::class);
        self::expectExceptionMessage('Name to short');
        $entity->save();
    }

    public function testExceptionIfRule(): void
    {
        $rule = new ValidatorRule('test', ['required']);
        $rule->setActivateOnSuccess(['type' => 'dog']); // if type=dog, then check if field "test" is set

        self::expectException(Exception::class);
        self::expectExceptionMessage('Activation condition already set');
        $rule->setActivateOnFail(['type' => 'dog']); // should not try to set another condition on same rule
    }

    public function testComplexRuleset(): void
    {
        $models = $validators = [];
        foreach (['old', 'new'] as $i) {
            $models[$i] = $this->createModel();
            $validators[$i] = $this->createValidator($models[$i]);
        }

        // set rules in old-style
        // everyone should have name set
        // dogs should have age set and not older than 20 years
        // others should have name at least 4 chars long
        $validators['old']->if(['type' => 'dog'], [
            'name' => ['required'],
            'age' => ['required', ['max', 20]],
        ], [
            'name' => ['required', ['lengthMin', 3, 'message' => 'Name to short']],
        ]);

        // and set exactly the same rule using new ValidatorRule style
        // everyone should have name set
        $rule = new ValidatorRule('name', 'required');
        $validators['new']->addRule($rule);

        // dogs should have age set
        $rule = new ValidatorRule('age', 'required');
        $rule->setActivateOnSuccess(['type' => 'dog']);
        $validators['new']->addRule($rule);

        // dogs should be not older than 20 years
        $rule = new ValidatorRule('age', ['max', 20]);
        $rule->setActivateOnSuccess(['type' => 'dog']);
        $validators['new']->addRule($rule);

        // others should have name at least 4 chars long
        $rule = new ValidatorRule('name', ['lengthMin', 3, 'message' => 'Name to short']);
        $rule->setActivateOnFail(['type' => 'dog']);
        $validators['new']->addRule($rule);

        // now testing both
        foreach (['old', 'new'] as $i) {
            $err = $models[$i]->createEntity()->setMulti([
                'type' => 'ball',
            ])->validate();
            self::assertSame(['name'], array_keys($err)); // name is required for everyone

            $err = $models[$i]->createEntity()->setMulti([
                'type' => 'dog',
            ])->validate();
            self::assertSame(['name', 'age'], array_keys($err)); // name and age is required for dogs

            $err = $models[$i]->createEntity()->setMulti([
                'type' => 'dog',
                'name' => 'AB',
                'age' => 25,
            ])->validate();
            self::assertSame(['age'], array_keys($err)); // for dogs age should be no more than 20, but short name is fine

            $err = $models[$i]->createEntity()->setMulti([
                'type' => 'ball',
                'name' => 'AB',
            ])->validate();
            self::assertSame(['name'], array_keys($err)); // for others name should be long enough
        }
    }

    /**
     * Test complex data type.
     */
    public function testComplexDataTypeInRule(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('dob', ['required', 'date', ['dateAfter', '2024-01-01']]);

        // date of birth not set
        $err = $model->createEntity()->validate();
        self::assertSame(['dob'], array_keys($err));

        // date of birth is to small
        $err = $model->createEntity()->set('dob', new \DateTime('2023-01-01'))->validate();
        self::assertSame(['dob'], array_keys($err));

        // date of birth is ok
        $err = $model->createEntity()->set('dob', new \DateTime('2024-10-01'))->validate();
        self::assertSame([], array_keys($err));
    }

    /**
     * Text complex type as condition.
     */
    public function testComplexDataTypeInCondition(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        // if date of birth is this date, then type is required
        // otherwise name is required
        $validator->if(['dob' => new \DateTime('2024-01-01')], [
            'type' => ['required'],
        ], [
            'name' => ['required'],
        ]);

        $err = $model->createEntity()->setMulti([
            'dob' => new \DateTime('2023-10-10'),
        ])->validate();
        self::assertSame(['name'], array_keys($err));

        $err = $model->createEntity()->setMulti([
            'dob' => new \DateTime('2024-01-01'),
        ])->validate();
        self::assertSame(['type'], array_keys($err));
    }

    /**
     * Test DateTime data typefor coverage.
     */
    public function testDateTimeForCoverage(): void
    {
        $model = $this->createModel();
        $validator = $this->createValidator($model);

        $validator->rule('dob', ['required', ['dateFormat', 'DD-MM-YYYY'], ['dateBefore', '20-10-2024']]);

        // date of birth is to big
        $err = $model->createEntity()->set('dob', new \DateTime('2025-01-01'))->validate();
        self::assertSame(['dob'], array_keys($err));

        // date of birth is ok
        $err = $model->createEntity()->set('dob', new \DateTime('2024-10-01'))->validate();
        self::assertSame([], array_keys($err));
    }
}
