<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->ignoreVCS(true)
;

$config = new Config();

return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'single_quote' => true,
    'no_unused_imports' => true,
    'ordered_imports' => true,
    'no_trailing_whitespace_in_comment' => true,
    'binary_operator_spaces' => ['default' => 'align_single_space'],
])->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->setSkip([
        // Exclude migrations by default; adjust to your preference
        'src/Migrations' => true,
    ]);
