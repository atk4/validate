<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Schema\TestCase;
use Atk4\Validate\ValitronValidator as FixedValidator;
use Valitron\Validator as OriginalValidator;

class ValitronValidatorTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createOriginalValidator(array $data = [], array $fields = []): OriginalValidator
    {
        return new OriginalValidator($data, $fields);
    }

    protected function createFixedValidator(array $data = [], array $fields = []): FixedValidator
    {
        return new FixedValidator($data, $fields);
    }

    public function testDate(): void
    {
        $data = [
            'f_good_string' => '2024-10-20',
            'f_bad_string' => 'bad-date',
            'f_null' => null,
            'f_empty_string' => '',
            'f_datetime' => new \Datetime('2024-10-20'),
        ];
        $rules = [
            'f_good_string' => 'date',
            'f_bad_string' => 'date',
            'f_null' => 'date',
            'f_empty_string' => 'date',
            'f_datetime' => 'date',
        ];

        $v = $this->createOriginalValidator($data);
        $v->mapFieldsRules($rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));
        //var_dump($v->errors());

        $v = $this->createFixedValidator($data);
        $v->mapFieldsRules($rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));

    }

}
