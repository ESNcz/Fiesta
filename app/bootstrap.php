<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(true); // enable for your remote IP
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

if(file_exists(__DIR__ . '/Config/config.prod.neon')) {
    $configurator->addConfig(__DIR__ . "/Config/config.prod.neon");
} else {
    $configurator->addConfig(__DIR__ . "/Config/config.dev.neon");
}

$configurator->addConfig(__DIR__ . '/Config/config.neon');

$container = $configurator->createContainer();

return $container;
