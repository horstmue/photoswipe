<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\Backend\EventListener;

use TYPO3\CMS\Backend\Form\Event\ModifyLinkExplanationEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;

/**
 * Shows a meaningful icon and label for "t3://photoswipe" links in FormEngine link fields.
 */
#[AsEventListener(identifier: 'photoswipe/backend/modify-link-explanation')]
final readonly class LinkEventListener
{
    public function __construct(
        private IconFactory $iconFactory,
    ) {}

    public function __invoke(ModifyLinkExplanationEvent $event): void
    {
        if (($event->getLinkData()['type'] ?? null) !== 'photoswipe') {
            return;
        }

        $url = (string)($event->getLinkParts()['url'] ?? '');
        $isContentLayer = str_contains($url, '#');

        $event->setLinkExplanation([
            'icon' => $this->iconFactory->getIcon(
                $isContentLayer ? 'tx-photoswipe-content' : 'tx-photoswipe-page',
                IconSize::SMALL
            )->render(),
            'text' => sprintf('PhotoSwipe Lightbox - %s (%s)', $isContentLayer ? 'Content' : 'Page', $url),
        ]);
    }
}
