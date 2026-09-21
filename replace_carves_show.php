<?php

$path = __DIR__.'/resources/views/production/carves/show.blade.php';
$content = file_get_contents($path);

// Perform replacements
$replacements = [
    'Sortir' => 'Potong Ukir',
    'sortir' => 'potong ukir',
    'sortBatch' => 'carveBatch',
    'sort_code' => 'carve_code',
    'sort_date' => 'carve_date',
    'production.sorts.' => 'production.carves.',
    'reportSortForm' => 'reportCarveForm',
    'sortItemsBody' => 'carveItemsBody',
    'sortSignatureCanvas' => 'carveSignatureCanvas',
    'sortSignatureInput' => 'carveSignatureInput',
    "category: 'Kayu Tembak'" => "category: 'Bahan Susut'",
    '$defaultSortCategories = [\'Bahan Tembak\', \'Bahan Suling\', \'Bahan Ukir Potong\'];' => '$defaultCarveCategories = [\'Bahan Susut\'];',
    '$mergedCategories = array_unique(array_merge($defaultSortCategories, $categories' => '$mergedCategories = array_unique(array_merge($defaultCarveCategories, $categories',
];

$content = strtr($content, $replacements);

file_put_contents($path, $content);
echo 'Done.';
