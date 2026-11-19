# TYPO3 photoswipe
[![Latest Stable Version](https://img.shields.io/packagist/v/tei/photoswipe?style=for-the-badge)](https://packagist.org/packages/tei/photoswipe)
[![TYPO3](https://img.shields.io/badge/TYPO3-photoswipe-%23f49700?style=for-the-badge)](https://extensions.typo3.org/extension/photoswipe)
[![License](https://img.shields.io/packagist/l/tei/photoswipe?style=for-the-badge)](https://packagist.org/packages/tei/photoswipe)

**Extension key:**
photoswipe

**Language:**
en

**Description:**
Adds the [photoswipe.com](http://photoswipe.com/) JavaScript plugin (version 5.4.2 https://github.com/dimsemenov/photoswipe) as default for image enlargement

**Keywords:**
gallery, photoswipe, lightbox, layer, zoom

**Copyright:**
2024

**Author:**
Tobias Eichelberger

**Email:**
[tobi.eichelberger@gmail.com](tobi.eichelberger@gmail.com)

**Licence:**
This document is published under the Open Publication License available from [opencontent.org/openpub/](http://www.opencontent.org/openpub/)
The content of this document is related to TYPO3, a GNU/GPL CMS/Framework available from www.typo3.org.


## Administration

### Requirements
TYPO3 v14.3+, PHP 8.2+, `typo3/cms-fluid-styled-content`

### Installation
1. `composer require horstmue/photoswipe`
2. Add the site set **PhotoSwipe** (`horstmue/photoswipe`) as dependency of your site
   (`config/sites/<site>/config.yaml` → `dependencies`, or the site set of your site package).
   It depends on `typo3/fluid-styled-content` and enables the click-enlarge lightbox.

## Configuration
Site settings (category "PhotoSwipe"):

| Setting                     | Default            | Description                                         |
|-----------------------------|--------------------|-----------------------------------------------------|
| `photoswipe.imageExtensions`| `jpg,jpeg,png,bmp` | Image extensions for the Base64 lazy-loading preview |
| `photoswipe.svgWidth`       | `10`               | Width of the Base64 lazy-loading preview in px       |

The set overrides the fluid_styled_content partials `Media/Type/Image` and `Media/Rendering/Image`
(`lib.contentElement.partialRootPaths.5`). Site packages can override them again with a higher index.

#### Photoswipe in your HTML:
The default setting for initializing photoswipe is by TYPO3's default css-class "ce-gallery". 
Your HTML has to look like this:
```
<someHtmlEl class="your-wrapper-class">
    [... code / parent nodes]
        <figure>
            <a href="big image" data-ispsw-img="1" data-pswp-width="800" data-pswp-height="500">
                <img [opt: title='used as caption']/>
            </a>
            [opt: <figcaption>prefered as caption</figcaption>]
        </figure>
    [/... code / parent nodes]
</someHtmlEl>
```

## Usage
Just use the regular "Enlarge on Click" checkbox in the backend. If you have more than one image, the extension will
add arrows in the zoom-view.

## Layer
After installing photoswipe, TYPO3 will offer you a new link-type "Layer". You can choose between pages (iFrame) and 
 content elements (innerHTML). 

