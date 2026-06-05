<?php

declare(strict_types=1);

/**
 * PHP-CS-Fixer configuration for the Soccr plugin.
 *
 * Enforces the PER Coding Style 3.0 (@PER-CS3x0) — the official successor to
 * PSR-12. Mirrors the previous PHPCS setup by scanning the src/ directory only.
 */

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS3x0' => true,
    ])
    ->setFinder($finder);
