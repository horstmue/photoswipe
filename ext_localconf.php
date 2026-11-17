<?php
use Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkBuilder;
use Tei\PhotoSwipe\LinkHandler\PhotoSwipeLinkHandling;

defined('TYPO3') || die();

(function (): void {

    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['photoswipe'] =
        PhotoSwipeLinkBuilder::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['photoswipe'] =
        PhotoSwipeLinkHandling::class;

})();
