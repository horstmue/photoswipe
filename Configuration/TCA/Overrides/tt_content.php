<?php

defined('TYPO3') || die();

$ttContentColumns = [
    'imagelazy64' => [
        'exclude' => true,
        'label' => 'LLL:EXT:photoswipe/Resources/Private/Language/locallang_db.xlf:tx_photoswipe.imagelazy64',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    'label' => 'ImageLazy', // Hier kann optional ein Text stehen, der neben der Checkbox erscheint
                    'value' => '1', // Der Wert, der in der DB gespeichert wird, wenn aktiv
                ],
            ],
            'default' => 0,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $ttContentColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'imagelinks', 'imagelazy64');
