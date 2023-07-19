<?php

declare(strict_types=1);

//use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\LevelSetList;

//use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/tests',
        //__DIR__ . '/resources',
        __DIR__ . '/lib',
    ]);

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->skip([
        __DIR__ . '/resources/php-epub-meta/vendor',
        ClosureToArrowFunctionRector::class
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        //DowngradeLevelSetList::DOWN_TO_PHP_80,
    ]);
};
