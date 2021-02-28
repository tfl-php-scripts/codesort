<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [
        __DIR__ . '/public/codesort2',
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/public/codesort2/functions.php',
    ]);

    $parameters->set(Option::SKIP, [
        __DIR__ . '/public/codesort2/codes-config.php',
        __DIR__ . '/public/codesort2/captcha.php',
    ]);

    $parameters->set(Option::SETS, [
        SetList::PHP_74,
    ]);
};
