<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PHP83Migration' => true,
        '@Symfony' => true,
        'phpdoc_summary' => false,
        'yoda_style' => false,
        'single_line_empty_body' => true,
    ])
    ->setFinder($finder)
;