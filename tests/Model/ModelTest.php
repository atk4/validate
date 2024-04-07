<?php

declare(strict_types=1);

namespace Atk4\Validate\Tests\Model;

use Atk4\Data\Model;

class ModelTest extends Model
{
    public $table = 'validator_test';

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('name');
        $this->addField('age', ['type' => 'integer']);
        $this->addField('type', ['required' => true, 'enum' => ['dog', 'ball']]);
        $this->addField('tail_length', ['type' => 'float']);
    }
}
