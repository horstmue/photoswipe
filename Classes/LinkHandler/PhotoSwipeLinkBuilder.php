<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\LinkHandler;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\PageLinkBuilder;

/**
 * Builds "t3://photoswipe" links in the frontend: a page link that opens in a PhotoSwipe layer,
 * either as iframe (whole page) or as inline HTML (single content element via "#c<uid>").
 *
 * Instantiated via GeneralUtility::makeInstance() by the LinkFactory, hence public.
 */
#[Autoconfigure(public: true)]
final class PhotoSwipeLinkBuilder extends PageLinkBuilder
{
    /**
     * @param array<string, mixed> $linkDetails
     * @param array<string, mixed> $configuration
     */
    public function buildLink(array $linkDetails, array $configuration, ServerRequestInterface $request, string $linkText = ''): LinkResultInterface
    {
        $result = parent::buildLink($linkDetails, $configuration, $request, $linkText)
            ->withAttribute('data-ispsw-layer', '1');
        $url = (string)$result->getUrl();
        $contentElementUid = (int)ltrim(explode('#', $url, 2)[1] ?? '', 'c');

        return $contentElementUid > 0
            ? $this->buildContentLayer($result, $contentElementUid)
            : $this->buildIFrameLayer($result, $url);
    }

    private function buildIFrameLayer(LinkResultInterface $result, string $url): LinkResultInterface
    {
        return $result
            ->withAttribute('data-pswp-type', 'iframe')
            ->withAttribute('data-iframe-url', $url);
    }

    private function buildContentLayer(LinkResultInterface $result, int $contentElementUid): LinkResultInterface
    {
        $html = $this->contentObjectRenderer->cObjGetSingle('RECORDS', [
            'tables' => 'tt_content',
            'source' => $contentElementUid,
            'dontCheckPid' => 1,
        ]);
        $html = trim((string)preg_replace('/\s+/', ' ', $html));

        return $result
            ->withAttribute('data-pswp-type', 'html')
            ->withAttribute('data-html', rawurlencode($html));
    }
}
