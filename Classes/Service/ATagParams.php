<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\Service;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * TypoScript userFunc for lib.contentElement.settings.media.popup.linkParams.ATagParams:
 * adds the dimensions PhotoSwipe needs to the click-enlarge link of an image.
 *
 * Resolved from the container by ContentObjectRenderer::callUserFunction(), hence public.
 */
#[Autoconfigure(public: true)]
final readonly class ATagParams
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param array<string, mixed> $conf
     */
    #[AsAllowedCallable]
    public function set(string $content, array $conf, ServerRequestInterface $request): string
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $file = $contentObjectRenderer instanceof ContentObjectRenderer ? $contentObjectRenderer->getCurrentFile() : null;
        if (!$file instanceof File && !$file instanceof FileReference) {
            return '';
        }

        ['width' => $width, 'height' => $height] = $this->getDimensions($file);

        return sprintf('data-ispsw-img="1" data-pswp-width="%d" data-pswp-height="%d"', $width, $height);
    }

    /**
     * @return array{width: int, height: int}
     */
    private function getDimensions(File|FileReference $file): array
    {
        $crop = $file instanceof FileReference ? ($file->getReferenceProperties()['crop'] ?? null) : null;
        $cropArea = $crop ? CropVariantCollection::create((string)$crop)->getCropArea('default') : null;

        $properties = ($cropArea !== null && !$cropArea->isEmpty())
            ? $this->getCroppedFileProperties($file, $cropArea)
            : $file->getProperties();

        // Fall back to the original file if dimensions are missing, see https://github.com/j0nnybrav079/photoswipe/issues/10
        if ((int)($properties['width'] ?? 0) === 0 || (int)($properties['height'] ?? 0) === 0) {
            $properties = $file instanceof FileReference ? $file->getOriginalFile()->getProperties() : $properties;
        }

        return [
            'width' => (int)($properties['width'] ?? 0),
            'height' => (int)($properties['height'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getCroppedFileProperties(File|FileReference $file, Area $cropArea): array
    {
        return $this->imageService
            ->applyProcessingInstructions($file, ['crop' => $cropArea->makeAbsoluteBasedOnFile($file)])
            ->getProperties();
    }
}
