/*
 * Module: @tei/photoswipe/photoswipe-link-handler.js
 *
 * LinkBrowser tab "Layer": same UI as the core page tab (@typo3/backend/page-link-handler.js),
 * but finalizes the link as t3://photoswipe?uid=… (optionally #c<uid> for a single content element).
 */
import LinkBrowser from '@typo3/backend/link-browser.js';
import RegularEvent from '@typo3/core/event/regular-event.js';

class PhotoSwipeLinkHandler {
  constructor() {
    new RegularEvent('click', (event, target) => {
      event.preventDefault();
      LinkBrowser.finalizeFunction(target.getAttribute('href').replace('t3://page', 't3://photoswipe'));
    }).delegateTo(document, 'a.t3js-pageLink');

    new RegularEvent('click', (event) => {
      event.preventDefault();
      this.linkPageByTextfield();
    }).delegateTo(document, 'input.t3js-pageLink');
  }

  linkPageByTextfield() {
    const value = document.getElementById('luid')?.value;
    if (!value) {
      return;
    }
    const uid = parseInt(value, 10);
    if (!isNaN(uid)) {
      LinkBrowser.finalizeFunction('t3://photoswipe?uid=' + uid);
    }
  }
}

export default new PhotoSwipeLinkHandler();
