<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in('.')
    ->name('*.php');

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        '@PHP81Migration' => true,
        'no_empty_statement' => true,
        'no_unused_imports' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'modernize_strpos' => true,
        'fully_qualified_strict_types' => true,
        'heredoc_indentation' => [
            'indentation' => 'same_as_start'
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
