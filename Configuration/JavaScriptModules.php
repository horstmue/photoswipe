<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'core',
        'backend',
    ],
    'tags' => [
        'backend.module',
        'backend.form',
        'backend.navigation-component',
    ],
    'imports' => [
        '@tei/photoswipe/' => [
            'path' => 'EXT:photoswipe/Resources/Public/JavaScript/',
        ],
    ],
];
