<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use Tei\PhotoSwipe\Service\ImageService64;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * Drop-in replacement for <f:media> (TYPO3 v14 core MediaViewHelper) with optional
 * Base64 preview lazy loading ("lazy64"): the img src is a tiny inline preview,
 * the real image URL is moved to data-src.
 *
 * ```
 *   <ps:media file="{file}" width="400" height="375" lazy64="{data.imagelazy64}" />
 * ```
 */
final class MediaViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'img';

    public function __construct(
        private readonly RendererRegistry $rendererRegistry,
        private readonly ImageService $imageService,
        private readonly ImageService64 $imageService64,
    ) {
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'object', 'File', true);
        $this->registerArgument('additionalConfig', 'array', 'This array can hold additional configuration that is passed though to the Renderer object', false, []);
        $this->registerArgument('width', 'string', 'This can be a numeric value representing the fixed width of in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'This can be a numeric value representing the fixed height in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');
        $this->registerArgument('fileExtension', 'string', 'Custom file extension to use for images');
        $this->registerArgument('loading', 'string', 'Native lazy-loading for images property. Can be "lazy", "eager" or "auto". Used on image files only.');
        $this->registerArgument('decoding', 'string', 'Provides an image decoding hint to the browser. Can be "sync", "async" or "auto"');
        $this->registerArgument('lazy64', 'bool', 'Render a Base64 preview as src and move the image URL to data-src. Used on image files only.', false, false);
    }

    public function render(): string
    {
        $file = $this->arguments['file'] ?? null;
        $additionalConfig = (array)($this->arguments['additionalConfig'] ?? []);
        $width = (string)($this->arguments['width'] ?? '');
        $height = (string)($this->arguments['height'] ?? '');

        // Extbase domain model: fetch the FAL resource object
        if (is_object($file) && is_callable([$file, 'getOriginalResource'])) {
            $file = $file->getOriginalResource();
        }

        if (!$file instanceof FileInterface) {
            throw new InvalidArgumentValueException('Supplied file object type ' . get_debug_type($file) . ' must be FileInterface.', 1454252193);
        }

        $fileExtension = (string)($this->arguments['fileExtension'] ?? '');
        if ($fileExtension !== '' && !GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileExtension)) {
            throw new InvalidArgumentValueException(
                'The extension ' . $fileExtension . ' is not specified in $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
                . ' as a valid image file extension and can not be processed.',
                1619030957
            );
        }

        $fileRenderer = $this->rendererRegistry->getRenderer($file);

        // Fallback to image when no renderer is found
        if ($fileRenderer === null) {
            return $this->renderImage($file, $width, $height, $fileExtension);
        }

        $arguments = [];
        foreach (array_merge($this->arguments, $this->additionalArguments) as $argumentName => $argumentValue) {
            // Prevent "null" when given in fluid
            if (!empty($argumentValue) && $argumentValue !== 'null') {
                $arguments[$argumentName] = $argumentValue;
            }
        }

        return $fileRenderer->render($file, $width, $height, array_merge_recursive($arguments, $additionalConfig));
    }

    private function renderImage(FileInterface $image, string $width, string $height, string $fileExtension): string
    {
        $cropVariant = (string)(($this->arguments['cropVariant'] ?? '') ?: 'default');
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width' => $width,
            'height' => $height,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        if ($fileExtension !== '') {
            $processingInstructions['fileExtension'] = $fileExtension;
        }
        $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
        $imageUri = $this->imageService->getImageUri($processedImage);

        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', (string)$focusArea->makeAbsoluteBasedOnFile($processedImage));
            }
        }

        if ($this->arguments['lazy64'] === true) {
            $this->tag->addAttribute('src', $this->imageService64->getBase64Preview($processedImage, $this->getSiteSettings()));
            $this->tag->addAttribute('data-src', $imageUri);
            $this->tag->addAttribute('class', trim('lazy64 ' . ($this->additionalArguments['class'] ?? '')));
        } else {
            $this->tag->addAttribute('src', $imageUri);
        }
        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }
        if (in_array($this->arguments['decoding'] ?? '', ['sync', 'async', 'auto'], true)) {
            $this->tag->addAttribute('decoding', $this->arguments['decoding']);
        }

        $alt = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if (empty($this->additionalArguments['alt'])) {
            $this->tag->addAttribute('alt', $alt ?? '');
        }
        if (empty($this->additionalArguments['title']) && !empty($title)) {
            $this->tag->addAttribute('title', $title);
        }

        return $this->tag->render();
    }

    private function getSiteSettings(): SiteSettings
    {
        $request = $this->renderingContext->hasAttribute(ServerRequestInterface::class)
            ? $this->renderingContext->getAttribute(ServerRequestInterface::class)
            : null;
        $site = $request?->getAttribute('site');

        return $site instanceof Site ? $site->getSettings() : SiteSettings::createFromSettingsTree([]);
    }
}
