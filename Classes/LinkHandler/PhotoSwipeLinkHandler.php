<?php

declare(strict_types=1);

namespace Tei\PhotoSwipe\LinkHandler;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\LinkHandler\PageLinkHandler;

/**
 * Backend LinkBrowser tab "Layer": selects a page or content element like the core page tab,
 * but finalizes the link as "t3://photoswipe?uid=…".
 */
final class PhotoSwipeLinkHandler extends PageLinkHandler
{
    public function render(ServerRequestInterface $request): string
    {
        $this->pageRenderer->loadJavaScriptModule('@tei/photoswipe/photoswipe-link-handler.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/tree/page-browser.js');
        $this->getBackendUser()->initializeWebmountsForElementBrowser();

        $this->expandPage = (int)($request->getQueryParams()['expandPage'] ?? 0);

        $this->view->assign('initialNavigationWidth', $this->getBackendUser()->uc['selector']['navigation']['width'] ?? 250);
        $this->view->assign('treeActions', ['link']);
        $this->getRecordsOnExpandedPage($this->expandPage);

        return $this->view->render('LinkBrowser/Page');
    }
}
