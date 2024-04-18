<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Schema\TestCase;
use Atk4\Validate\ValitronValidator as FixedValidator;
use TypeError;
use Valitron\Validator as OriginalValidator;

class ValitronValidatorTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createOriginalValidator(array $data = [], array $rules = []): OriginalValidator
    {
        $v = new OriginalValidator($data);
        $v->mapFieldsRules($rules);
        return $v;
    }

    protected function createFixedValidator(array $data = [], array $rules = []): FixedValidator
    {
        $v = new FixedValidator($data);
        $v->mapFieldsRules($rules);
        return $v;
    }

    public function testDate2(): void
    {
        $v = $this->createOriginalValidator(['d' => null], ['d' => ['required', 'date']]);
        $v->validate();
        var_dump($v->errors());


    }

    /*
    public function testDate(): void
    {
        $data = [
            'f_good_string' => '2024-10-20',
            'f_bad_string' => 'bad-date',
            'f_null' => null,
            'f_empty' => '',
            'f_datetime' => new \Datetime('2024-10-20'),
        ];
        $rules = [
            'f_good_string' => ['date'],
            'f_bad_string' => ['date'],
            'f_null' => ['date'],
            'f_empty' => ['date'],
            'f_datetime' => ['date'],
        ];

        $v = $this->createOriginalValidator($data, $rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));

        $v = $this->createFixedValidator($data, $rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));
    }

    public function testDateFormat(): void
    {
        $data = [
            'f_good_string' => '20-10-2024',
            'f_bad_string' => 'bad-date',
            'f_null' => null,
            'f_empty' => '',
            'f_datetime' => new \Datetime('2024-10-20'),
        ];
        $rules = [
            'f_good_string' => [['dateFormat', 'd-m-Y']],
            'f_bad_string' => [['dateFormat', 'd-m-Y']],
            'f_null' => [['dateFormat', 'd-m-Y']],
            'f_empty' => [['dateFormat', 'd-m-Y']],
            'f_datetime' => [['dateFormat', 'd-m-Y']],
        ];

        $v = $this->createOriginalValidator($data, $rules);
        // There should not be exception, but original class throws
        // TypeError: date_parse_from_format(): Argument #2 ($datetime) must be of type string, DateTime given
        self::expectException(TypeError::class);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));

        $v = $this->createFixedValidator($data, $rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));
    }

    public function testDateBefore(): void
    {
        $data = [
            'f_good_string' => '2024-10-20',
            'f_bad_string' => 'bad-date',
            'f_null' => null,
            'f_empty' => '',
            'f_datetime' => new \Datetime('2024-10-20'),
        ];
        $rules = [
            'f_good_string' => [['dateBefore', '2024-10-21']],
            'f_bad_string' => [['dateBefore', '2024-10-21']],
            'f_null' => [['dateBefore', '2024-10-21']],
            'f_empty' => [['dateBefore', '2024-10-21']],
            'f_datetime' => [['dateBefore', '2024-10-21']],
        ];

        $v = $this->createOriginalValidator($data, $rules);
        $ok = $v->validate();
var_dump($v->errors());
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));

        $v = $this->createFixedValidator($data, $rules);
        $ok = $v->validate();
        self::assertFalse($ok);
        self::assertSame(['f_bad_string'], array_keys($v->errors()));
    }
    */
}
