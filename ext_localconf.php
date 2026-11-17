<?php

declare(strict_types=1);

use Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkBuilder;
use Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkHandling;

defined('TYPO3') || die();

// t3://photoswipe links: backend link handling and frontend typolink builder
$GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['photoswipe'] = PhotoSwipeLinkHandling::class;
$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['photoswipe'] = PhotoSwipeLinkBuilder::class;
