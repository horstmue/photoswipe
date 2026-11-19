==========
PhotoSwipe
==========

Adds the `PhotoSwipe 5 <https://photoswipe.com>`__ lightbox to TYPO3's native
"Enlarge on click" image setting and provides the link type "Layer" to open pages
(iframe) or single content elements (inline HTML) in a PhotoSwipe layer.

Requirements
============

TYPO3 v14.3+, PHP 8.2+, typo3/cms-fluid-styled-content

Installation
============

1. ``composer require horstmue/photoswipe``
2. Add the site set ``horstmue/photoswipe`` as dependency of your site.

Configuration
=============

Site settings ``photoswipe.imageExtensions`` and ``photoswipe.svgWidth`` (category
"PhotoSwipe") control the optional Base64 lazy-loading preview, which editors enable
per content element with the "Lazy loading" checkbox next to "Enlarge on click".

See README.md for the expected HTML structure.
