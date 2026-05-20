<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function inventory_categories(): array
{
    return [
        'Raspberry' => 'Raspberry',
        'Arduino' => 'Arduino-Koffer',
        'PC-Teile' => 'PC-Teile',
        'Sonstiges' => 'Sonstiges',
    ];
}

function normalize_category(string $category): string
{
    return array_key_exists($category, inventory_categories()) ? $category : 'Sonstiges';
}

function koffer_label(array $koffer): string
{
    $label = trim((string)($koffer['Bezeichnung'] ?? ''));
    if ($label !== '') {
        return $label;
    }

    return 'Koffer ' . (string)($koffer['Koffer_ID'] ?? '');
}

function koffer_meta(array $koffer): string
{
    $parts = [];
    foreach (['Kategorie', 'Zielgruppe', 'Ansprechpartner'] as $field) {
        $value = trim((string)($koffer[$field] ?? ''));
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    return implode(' · ', $parts);
}
