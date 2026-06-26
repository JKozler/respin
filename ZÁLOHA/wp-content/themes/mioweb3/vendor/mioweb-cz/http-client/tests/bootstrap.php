<?php declare(strict_types=1);

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if (!include_once __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');


// configure locks dir
require_once __DIR__ . '/constants.php';
@mkdir(LOCKS_DIR);

function run(Tester\TestCase $testCase): void
{
	$testCase->run($_SERVER['argv'][1] ?? null);
}
