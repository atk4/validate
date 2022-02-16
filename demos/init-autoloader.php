<?php

declare(strict_types=1);

namespace Atk4\Validate\Demos;

use Atk4\Validate\Tests\BasicTest;

$isRootProject = file_exists(__DIR__ . '/../vendor/autoload.php');
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__, $isRootProject ? 1 : 4) . '/vendor/autoload.php';
if (!$isRootProject && !class_exists(BasicTest::class)) {
    throw new \Error('Demos can be run only if Atk4\Validate is a root composer project or if dev files are autoloaded');
}
$loader->setClassMapAuthoritative(false);
$loader->setPsr4('Atk4\Validate\Demos\\', __DIR__ . '/_includes');
unset($isRootProject, $loader);
