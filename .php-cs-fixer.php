<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$header = <<<'EOF'
    SoigneMoi Webcli - Projet ECF
    
    @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
    2024
    EOF;

$finder = (new Finder())
    ->ignoreDotFiles(true)
    ->ignoreVCSIgnored(true)
    ->exclude(['public', 'assets', 'tests', 'config', 'src/Factory/', 'src/DataFixtures/',])
    ->in(__DIR__);

return (new Config())
    ->setRiskyAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'declare_strict_types' => true,
        'header_comment' => ['header' => $header],
        'no_alias_functions' => true,
        'no_unneeded_final_method' => true,
        'non_printable_character' => ['use_escape_sequences_in_strings' => false],
        'no_break_comment' => true,
    ])
    ->setFinder($finder);
