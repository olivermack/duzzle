<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'trim_array_spaces' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_trailing_comma_in_singleline' => true,
        'trailing_comma_in_multiline' => true,
        'single_space_around_construct' => true,
    ])
    ->setFinder($finder);
