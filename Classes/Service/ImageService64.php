<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageService64
{
    protected array $settings = [];

    public function __construct(
        protected readonly ConfigurationManagerInterface $configurationManager,
        protected readonly ImageService $imageService
    ) {
    }

    public function getBase64Preview(ProcessedFile $processedImage): string
    {
        $this->settings = $this->getSettings();
        $imageExtension = strtolower($processedImage->getExtension());
        $allowedImageExtensions = GeneralUtility::trimExplode(',', strtolower((string)($this->settings['imageExtensions'] ?? 'jpg,jpeg,png,bmp')), true);
        $svgImage = 'data:image/file-extensions-not-supported;';

        if (in_array($imageExtension, $allowedImageExtensions, true)) {
            $props = $processedImage->getOriginalFile()->getProperties();
            $svgWidth = (int)($this->settings['svgWidth'] ?? 10);
            $origWidth = (int)($props['width'] ?? 0);
            $origHeight = (int)($props['height'] ?? 0);
            $svgHeight = $origWidth > 0 ? (int)round(($origHeight * $svgWidth) / $origWidth) : $svgWidth;

            $processingInstructions = [
                'width' => $svgWidth,
                'height' => $svgHeight,
            ];
            $processedImageSVG = $this->imageService->applyProcessingInstructions($processedImage, $processingInstructions);

            $fileLink = Environment::getPublicPath() . $this->imageService->getImageUri($processedImageSVG);

            // remove EXIF and get img-data
            $processedDir = Environment::getPublicPath() . '/fileadmin/_processed_';
            if (!is_dir($processedDir)) {
                GeneralUtility::mkdir_deep($processedDir);
            }
            $tmpFileLink = $processedDir . '/ps_tmp.' . $imageExtension;
            $this->removeExif($fileLink, $tmpFileLink);
            if (file_exists($tmpFileLink)) {
                $tmpData = (string)file_get_contents($tmpFileLink);
                $svgImage = 'data:image/' . $imageExtension . ';base64,' . base64_encode($tmpData);
            }
        }
        return $svgImage;
    }

    public function getSettings(): array
    {
        $settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'photoswipe'
        );
        if (empty($settings)) {
            $setup = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            $settings = $setup['plugin']['tx_photoswipe']['settings']
                ?? $setup['plugin.']['tx_photoswipe.']['settings.']
                ?? [];
        }
        return array_merge([
            'imageExtensions' => 'jpg,jpeg,png,bmp',
            'svgWidth' => 10,
        ], $settings ?? []);
    }

    /**
     * taken from https://stackoverflow.com/questions/3614925/remove-exif-data-from-jpg-using-php/38862429 [Dmitry Bugrov]
     * @param string $in
     * @param string $out
     */
    private function removeExif(string $in, string $out): void
    {
        $buffer_len = 4096;
        $inPath = urldecode($in);
        if (!file_exists($inPath)) {
            return;
        }
        $fd_in = fopen($inPath, 'rb');
        $fd_out = fopen($out, 'wb');
        if ($fd_in === false || $fd_out === false) {
            if ($fd_in) {
                fclose($fd_in);
            }
            if ($fd_out) {
                fclose($fd_out);
            }
            return;
        }
        while (($buffer = fread($fd_in, $buffer_len))) {
            //  \xFF\xE1\xHH\xLLExif\x00\x00 - Exif
            //  \xFF\xE1\xHH\xLLhttp://      - XMP
            //  \xFF\xE2\xHH\xLLICC_PROFILE  - ICC
            //  \xFF\xED\xHH\xLLPhotoshop    - PH
            while (preg_match('/\xFF[\xE1\xE2\xED\xEE](.)(.)(exif|photoshop|http:|icc_profile|adobe)/si', $buffer, $match, PREG_OFFSET_CAPTURE)) {
                $len = ord($match[1][0]) * 256 + ord($match[2][0]);
                fwrite($fd_out, substr($buffer, 0, $match[0][1]));
                $filepos = $match[0][1] + 2 + $len - strlen($buffer);
                fseek($fd_in, $filepos, SEEK_CUR);
                $buffer = fread($fd_in, $buffer_len);
            }
            fwrite($fd_out, $buffer, strlen($buffer));
        }
        fclose($fd_out);
        fclose($fd_in);
    }
}
