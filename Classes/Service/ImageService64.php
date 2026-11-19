<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\Service;

use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Creates a tiny, EXIF-free Base64 preview of an image for "lazy64" lazy loading.
 */
final readonly class ImageService64
{
    private const NOT_SUPPORTED = 'data:image/file-extensions-not-supported;';

    public function __construct(
        private ImageService $imageService,
    ) {}

    public function getBase64Preview(ProcessedFile $processedImage, SiteSettings $settings): string
    {
        $imageExtension = strtolower($processedImage->getExtension());
        $allowedImageExtensions = array_map(
            static fn(string $extension): string => strtolower(trim($extension)),
            explode(',', (string)$settings->get('photoswipe.imageExtensions', 'jpg,jpeg,png'))
        );
        if (!in_array($imageExtension, $allowedImageExtensions, true)) {
            return self::NOT_SUPPORTED;
        }

        $originalProperties = $processedImage->getOriginalFile()->getProperties();
        $originalWidth = (int)($originalProperties['width'] ?? 0);
        if ($originalWidth === 0) {
            return self::NOT_SUPPORTED;
        }
        $previewWidth = max(1, (int)$settings->get('photoswipe.svgWidth', 10));
        $previewHeight = max(1, (int)round((int)($originalProperties['height'] ?? 0) * $previewWidth / $originalWidth));

        $preview = $this->imageService->applyProcessingInstructions($processedImage, [
            'width' => $previewWidth,
            'height' => $previewHeight,
        ]);

        return 'data:image/' . $imageExtension . ';base64,' . base64_encode($this->removeExif($preview->getContents()));
    }

    /**
     * Strips EXIF, XMP, ICC and Photoshop segments from JPEG data.
     *
     * @see https://stackoverflow.com/questions/3614925/remove-exif-data-from-jpg-using-php/38862429 (Dmitry Bugrov)
     */
    private function removeExif(string $data): string
    {
        $result = '';
        $offset = 0;
        $pattern = '/\xFF[\xE1\xE2\xED\xEE](.)(.)(exif|photoshop|http:|icc_profile|adobe)/si';
        while (preg_match($pattern, $data, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $segmentStart = $match[0][1];
            $segmentLength = ord($match[1][0]) * 256 + ord($match[2][0]);
            $result .= substr($data, $offset, $segmentStart - $offset);
            $offset = $segmentStart + 2 + $segmentLength;
        }

        return $result . substr($data, $offset);
    }
}
