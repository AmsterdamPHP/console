#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use AmsterdamPHP\Console\Command\CreateJoindInCommand;
use Symfony\Component\Console\Application;

/** @var \Interop\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

$application = new Application();
$application->add($container->get(CreateJoindInCommand::class));
$application->run();
