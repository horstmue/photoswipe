<?php

declare(strict_types=1);

defined('TYPO3') || die();

(function () {

    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['photoswipe'] =
        \Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkBuilder::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['photoswipe'] =
        \Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkHandling::class;

})();
