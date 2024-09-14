<?php

declare(strict_types=1);

//use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;

//use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/test',
        //__DIR__ . '/resources',
        //__DIR__ . '/lib',
        __DIR__,
    ]);

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        ClosureToArrowFunctionRector::class,
        MixedTypeRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        //DowngradeLevelSetList::DOWN_TO_PHP_80,
        //DowngradeLevelSetList::DOWN_TO_PHP_74,
        PHPUnitSetList::PHPUNIT_100,
    ]);
};
