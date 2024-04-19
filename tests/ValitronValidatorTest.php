<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests;

use Atk4\Data\Schema\TestCase;
use Atk4\Validate\ValitronValidator as FixedValidator;
use TypeError;
use Valitron\Validator as OriginalValidator;

class ValitronValidatorTest extends TestCase
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    protected function createOriginalValidator(array $data, array $rules): OriginalValidator
    {
        $validator = new OriginalValidator($data);
        $validator->mapFieldsRules($rules);

        return $validator;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    protected function createFixedValidator(array $data, array $rules): FixedValidator
    {
        $validator = new FixedValidator($data);
        $validator->mapFieldsRules($rules);

        return $validator;
    }

    /**
     * @return mixed
     */
    private function executeFxAndThrowErrors(\Closure $fx, int $errorLevels = \E_ALL)
    {
        set_error_handler(
            static function ($errno, $errstr) {
                throw new \Exception($errstr, $errno);
            },
            $errorLevels
        );

        try {
            return $fx();
        } finally {
            restore_error_handler();
        }
    }

    public function testDate(): void
    {
        // date in data field is null
        $data = ['d' => null];
        $rules = ['d' => ['required', 'date']];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(2, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateFormat(): void
    {
        // date in data field is null
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateFormat', 'd-m-Y']]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(2, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('date_parse_from_format(): Passing null to parameter #2 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateFormat2(): void
    {
        // date in data field is DateTime object
        $data = ['d' => new \DateTime()];
        $rules = ['d' => ['required', ['dateFormat', 'd-m-Y']]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertTrue($validator->validate());
        self::assertSame([], array_keys($validator->errors()));

        // this throws warning in PHP 7.4 and TypeError in 8.x
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID < 80000) {
            // @codeCoverageIgnoreStart
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('date_parse_from_format() expects parameter 2 to be string, object given');
        // @codeCoverageIgnoreEnd
        } else {
            // TypeError: date_parse_from_format(): Argument #2 ($datetime) must be of type string, DateTime given
            self::expectException(\TypeError::class);
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateBefore(): void
    {
        // date in data field is null
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateBefore', '2029-12-31']]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(2, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateBefore2(): void
    {
        // date in condition is null
        $data = ['d' => '2024-10-20'];
        $rules = ['d' => ['required', ['dateBefore', null]]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(1, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateAfter(): void
    {
        // date in data field is null
        $data = ['d' => null];
        $rules = ['d' => ['required', ['dateAfter', '2023-12-31']]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(2, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }

    public function testDateAfter2(): void
    {
        // date in condition is null
        $data = ['d' => '2024-10-20'];
        $rules = ['d' => ['required', ['dateAfter', null]]];

        $validator = $this->createFixedValidator($data, $rules);
        self::assertFalse($validator->validate());
        self::assertSame(['d'], array_keys($validator->errors()));
        self::assertSame(1, count($validator->errors()['d']));

        // this throws depreciation notice starting PHP 8.1
        $validator = $this->createOriginalValidator($data, $rules);
        if (\PHP_VERSION_ID >= 80100) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated');
        }
        $this->executeFxAndThrowErrors(static function () use ($validator) {
            $validator->validate();
        });
    }
}
