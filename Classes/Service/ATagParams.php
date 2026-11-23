<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\Service;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class ATagParams
{
    private ?ContentObjectRenderer $cObj = null;

    public function __construct(
        private readonly ?ImageService $imageService = null
    ) {
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    public function set(string $content, array $conf): string
    {
        if ($this->cObj === null) {
            return '';
        }

        $file = $this->cObj->getCurrentFile();
        if ($file === null) {
            return '';
        }

        ['width' => $width, 'height' => $height] = $this->getFileProperties($file);

        // fallback to original file if dimensions are still null for unknown reason
        // see https://github.com/j0nnybrav079/photoswipe/issues/10
        if (((int)$width === 0) || ((int)$height === 0)) {
            if ($file instanceof FileReference) {
                ['width' => $width, 'height' => $height] = $file->getOriginalFile()->getProperties();
            }
        }

        return sprintf(
            'data-ispsw-img="1" data-pswp-width="%s" data-pswp-height="%s"',
            $width,
            $height
        );
    }

    private function getFileProperties(FileInterface|FileReference $file): array
    {
        $crop = $file instanceof FileReference ? ($file->getReferenceProperties()['crop'] ?? null) : null;

        if ($crop === null) {
            // No crop applied, so just return properties of original file.
            return $file->getProperties();
        }

        // File (reference) is cropped.
        $cropArea = CropVariantCollection::create((string)$crop)->getCropArea('default');

        return $cropArea->isEmpty() ? $file->getProperties() : $this->getCroppedFileProperties($file, $cropArea);
    }

    private function getCroppedFileProperties(FileInterface|FileReference $file, Area $cropArea): array
    {
        $processingInstructions = [
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file)
        ];

        $imageService = $this->imageService ?? GeneralUtility::makeInstance(ImageService::class);

        return $imageService->applyProcessingInstructions($file, $processingInstructions)->getProperties();
    }
}
