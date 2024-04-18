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

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    protected function createOriginalValidator(array $data, array $rules): OriginalValidator
    {
        $v = new OriginalValidator($data);
        $v->mapFieldsRules($rules);

        return $v;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    protected function createFixedValidator(array $data, array $rules): FixedValidator
    {
        $v = new FixedValidator($data);
        $v->mapFieldsRules($rules);

        return $v;
    }

    public function testDate(): void
    {
        $data = ['d' => null];
        $rules = ['d' => ['required', 'date']];

        $v = $this->createOriginalValidator($data, $rules);

        // this throws depreciation notice starting PHP 8.1
        // Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated
        $v->validate(); // @todo why validate fail with assert(isset($trace[1]['file'])) ???

        $v = $this->createFixedValidator($data, $rules);
        self::assertFalse($v->validate());
        self::assertSame(['d'], array_keys($v->errors()));
        self::assertSame(2, count($v->errors()['d']));
    }

    public function testDateFormat(): void
    {
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateFormat', 'd-m-Y']]];

        $v = $this->createOriginalValidator($data, $rules);

        // this throws depreciation notice starting PHP 8.1
        // Deprecated: date_parse_from_format(): Passing null to parameter #2 ($datetime) of type string is deprecated
        $v->validate(); // @todo why validate fail with assert(isset($trace[1]['file'])) ???

        $v = $this->createFixedValidator($data, $rules);
        self::assertFalse($v->validate());
        self::assertSame(['d'], array_keys($v->errors()));
        self::assertSame(2, count($v->errors()['d']));
    }

    public function testDateFormat2(): void
    {
        $data = ['d' => new \DateTime()];
        $rules = ['d' => ['required', ['dateFormat', 'd-m-Y']]];

        $v = $this->createOriginalValidator($data, $rules);

        // There should not be exception, but original class throws
        // TypeError: date_parse_from_format(): Argument #2 ($datetime) must be of type string, DateTime given
        self::expectException(\TypeError::class);
        $v->validate();

        $v = $this->createFixedValidator($data, $rules);
        self::assertTrue($v->validate());
        self::assertSame([], array_keys($v->errors()));
    }

    public function testDateBefore(): void
    {
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateBefore', '2029-12-31']]];

        $v = $this->createOriginalValidator($data, $rules);

        // this throws depreciation notice starting PHP 8.1
        // Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated
        $v->validate(); // @todo why validate fail with assert(isset($trace[1]['file'])) ???

        $v = $this->createFixedValidator($data, $rules);
        self::assertFalse($v->validate());
        self::assertSame(['d'], array_keys($v->errors()));
        self::assertSame(2, count($v->errors()['d']));
    }

    public function testDateAfter(): void
    {
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateAfter', '2023-12-31']]];

        $v = $this->createOriginalValidator($data, $rules);

        // this throws depreciation notice starting PHP 8.1
        // Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated
        $v->validate(); // @todo why validate fail with assert(isset($trace[1]['file'])) ???

        $v = $this->createFixedValidator($data, $rules);
        self::assertFalse($v->validate());
        self::assertSame(['d'], array_keys($v->errors()));
        self::assertSame(2, count($v->errors()['d']));
    }
}
