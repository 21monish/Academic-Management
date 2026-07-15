import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const showPageSkeleton = () => {
    if (document.getElementById('page-skeleton-loader')) {
        document.documentElement.classList.add('is-page-loading');
    }
};

const hidePageSkeleton = () => {
    document.documentElement.classList.remove('is-page-loading');
};

const shouldSkipLinkLoader = (link) => {
    if (!link.href || link.dataset.noSkeleton === 'true' || link.hasAttribute('download')) {
        return true;
    }

    if (link.target && link.target !== '_self') {
        return true;
    }

    const url = new URL(link.href, window.location.href);

    if (url.origin !== window.location.origin) {
        return true;
    }

    return url.pathname === window.location.pathname
        && url.search === window.location.search
        && url.hash !== '';
};

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const target = event.target instanceof Element ? event.target : null;
    const link = target?.closest('a');

    if (!link || shouldSkipLinkLoader(link)) {
        return;
    }

    showPageSkeleton();
});

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || event.target.dataset.noSkeleton === 'true') {
        return;
    }

    showPageSkeleton();
});

window.addEventListener('pageshow', hidePageSkeleton);
window.addEventListener('load', hidePageSkeleton);
