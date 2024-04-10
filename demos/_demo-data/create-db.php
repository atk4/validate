<?php

declare(strict_types=1);

namespace Atk4\Validate\Demos;

use Atk4\Data\Persistence;
use Atk4\Data\Schema\Migrator;
use Atk4\Validate\Tests\Model\Dummy;

require_once __DIR__ . '/../init-autoloader.php';

$sqliteFile = __DIR__ . '/db.sqlite';
if (!file_exists($sqliteFile)) {
    new Persistence\Sql('sqlite:' . $sqliteFile);
}
unset($sqliteFile);

/** @var Persistence\Sql $db */
require_once __DIR__ . '/../init-db.php';

if (getenv('GITHUB_JOB') !== false && strpos(getenv('GITHUB_JOB'), 'unit-') === 0) {
    echo "skip db creation in create-db\n\n";

    return;
}

$migr = new Migrator(new Dummy($db));
$migr->dropIfExists()->create();

echo "import complete!\n\n";
