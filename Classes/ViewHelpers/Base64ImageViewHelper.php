<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\ViewHelpers;

use Tei\PhotoSwipe\Service\ImageService64;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Render a given image base64 preview.
 */
class Base64ImageViewHelper extends AbstractTagBasedViewHelper
{
    /** @var string */
    protected $tagName = 'img';

    public function __construct(
        protected readonly ImageService $imageService,
        protected readonly ImageService64 $imageService64
    ) {
        parent::__construct();
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('url', 'string', 'a path to the image', true, '');
    }

    /**
     * Resizes a given image (if required) and renders the respective base64 preview
     *
     * @throws Exception
     * @return string Rendered base64 preview string
     */
    public function render(): string
    {
        $image = $this->imageService->getImage($this->arguments['url'], null, false);
        return $this->imageService64->getBase64Preview($this->imageService->applyProcessingInstructions($image, []));
    }
}
