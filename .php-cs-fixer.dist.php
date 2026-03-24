<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/packages/eusig/src',
        __DIR__ . '/packages/eusig/tests',
        __DIR__ . '/packages/eusig-bundle/src',
        __DIR__ . '/packages/eusig-bundle/tests',
    ])
    ->exclude('Resources')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => ['include' => ['@all'], 'scope' => 'namespaced'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'strict_param' => true,
        'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
