<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/rector.php',
    ])
    ->withSkipPath(__DIR__.'/config/bundles.php')
    ->withSkip([__DIR__.'/src/DataFixtures', __DIR__.'/src/Factory'])
    ->withImportNames(removeUnusedImports: true)
    ->withPhpSets(php83: true)
    ->withSets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        //        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        //        PHPUnitSetList::PHPUNIT_90,
    ])
    ->withRules([])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withParallel()
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon']) // @todo fait quoi exactement ?
;
