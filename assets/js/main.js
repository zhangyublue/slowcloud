(() => {
    const root = document.documentElement;
    const body = document.body;
    const toggle = document.querySelector('[data-slowcloud-theme-toggle]');
    const coverHeader = document.querySelector('[data-slowcloud-cover]');
    const categoryLists = document.querySelectorAll('.category-list');
    const topLoader = document.querySelector('[data-slowcloud-top-loader]');
    const storageKey = 'slowcloud-theme';
    const storage = window.sessionStorage;
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const themeMode = body?.dataset.slowcloudThemeMode || 'system';

    const getPreferredTheme = () => {
        const savedTheme = storage.getItem(storageKey);
        if (savedTheme === 'light' || savedTheme === 'dark') {
            return savedTheme;
        }

        if (themeMode === 'dark') {
            return 'dark';
        }

        if (themeMode === 'light') {
            return 'light';
        }

        return mediaQuery.matches ? 'dark' : 'light';
    };

    const applyHeaderContrast = (mode) => {
        if (!coverHeader) {
            return;
        }

        coverHeader.dataset.slowcloudContrast = mode;
    };

    const applyTheme = (theme) => {
        root.dataset.slowcloudTheme = theme;
        applyHeaderContrast(theme === 'dark' ? 'light' : 'dark');
        window.dispatchEvent(new CustomEvent('slowcloud-theme-change', {
            detail: { theme }
        }));

        if (!toggle) {
            return;
        }

        const isDark = theme === 'dark';
        toggle.setAttribute('aria-pressed', String(isDark));
        toggle.setAttribute('aria-label', isDark ? '切换到白天模式' : '切换到黑夜模式');
    };

    applyTheme(getPreferredTheme());
    root.classList.add('slowcloud-ready');

    if (topLoader) {
        let hideTopLoaderTimer = 0;
        let loaderPositionFrame = 0;
        const siteHeader = document.querySelector('.slowcloud-site-header');
        const siteOrigin = window.location.origin;
        const sitePath = `${window.location.pathname}${window.location.search}`;

        const updateTopLoaderPosition = () => {
            loaderPositionFrame = 0;

            if (!siteHeader) {
                topLoader.style.setProperty('--slowcloud-loader-top', '0px');
                return;
            }

            const rect = siteHeader.getBoundingClientRect();
            const headerBottom = Math.max(0, Math.min(rect.bottom, window.innerHeight));
            topLoader.style.setProperty('--slowcloud-loader-top', `${headerBottom > 0 ? headerBottom : 0}px`);
        };

        const requestTopLoaderPosition = () => {
            if (loaderPositionFrame) {
                return;
            }

            loaderPositionFrame = window.requestAnimationFrame(updateTopLoaderPosition);
        };

        const showTopLoader = () => {
            window.clearTimeout(hideTopLoaderTimer);
            updateTopLoaderPosition();
            topLoader.classList.add('is-active');
        };

        const hideTopLoader = () => {
            window.clearTimeout(hideTopLoaderTimer);
            hideTopLoaderTimer = window.setTimeout(() => {
                topLoader.classList.remove('is-active');
            }, 120);
        };

        const isPlainNavigationClick = (event) => (
            event.button === 0
            && !event.defaultPrevented
            && !event.metaKey
            && !event.ctrlKey
            && !event.shiftKey
            && !event.altKey
        );

        const shouldShowLoaderForLink = (link) => {
            if (!link || link.dataset.noLoader !== undefined) {
                return false;
            }

            const href = link.getAttribute('href') || '';
            if (
                href === ''
                || href.startsWith('#')
                || href.startsWith('javascript:')
                || href.startsWith('mailto:')
                || href.startsWith('tel:')
                || link.hasAttribute('download')
                || (link.target && link.target !== '_self')
            ) {
                return false;
            }

            let url;
            try {
                url = new URL(href, window.location.href);
            } catch (error) {
                return false;
            }

            if (url.origin !== siteOrigin) {
                return false;
            }

            const nextPath = `${url.pathname}${url.search}`;
            return nextPath !== sitePath || url.hash === '';
        };

        const shouldShowLoaderForForm = (form) => {
            if (!form || form.dataset.noLoader !== undefined) {
                return false;
            }

            const method = (form.getAttribute('method') || 'get').toLowerCase();
            if (method !== 'get' && method !== 'post') {
                return false;
            }

            const target = form.getAttribute('target') || '';
            if (target && target !== '_self') {
                return false;
            }

            const action = form.getAttribute('action') || window.location.href;
            try {
                return new URL(action, window.location.href).origin === siteOrigin;
            } catch (error) {
                return false;
            }
        };

        window.addEventListener('load', hideTopLoader);
        window.addEventListener('pageshow', hideTopLoader);
        window.addEventListener('pagehide', showTopLoader);
        window.addEventListener('scroll', requestTopLoaderPosition, { passive: true });
        window.addEventListener('resize', requestTopLoaderPosition);
        requestTopLoaderPosition();

        document.addEventListener('click', (event) => {
            if (!isPlainNavigationClick(event)) {
                return;
            }

            const link = event.target.closest('a[href]');
            if (shouldShowLoaderForLink(link)) {
                showTopLoader();
            }
        });

        document.addEventListener('submit', (event) => {
            if (shouldShowLoaderForForm(event.target)) {
                showTopLoader();
            }
        });
    }

    if (toggle) {
        toggle.addEventListener('click', () => {
            const nextTheme = root.dataset.slowcloudTheme === 'dark' ? 'light' : 'dark';
            storage.setItem(storageKey, nextTheme);
            applyTheme(nextTheme);
        });
    }

    const syncSystemTheme = (event) => {
        if (themeMode !== 'system' || storage.getItem(storageKey)) {
            return;
        }

        applyTheme(event.matches ? 'dark' : 'light');
    };

    if (typeof mediaQuery.addEventListener === 'function') {
        mediaQuery.addEventListener('change', syncSystemTheme);
    } else if (typeof mediaQuery.addListener === 'function') {
        mediaQuery.addListener(syncSystemTheme);
    }

    const detectHeaderContrast = () => {
        if (!coverHeader) {
            return;
        }

        applyHeaderContrast(root.dataset.slowcloudTheme === 'dark' ? 'light' : 'dark');
    };

    detectHeaderContrast();

    categoryLists.forEach((list) => {
        const parentItems = list.querySelectorAll('li');

        parentItems.forEach((item) => {
            const childList = item.querySelector(':scope > ul');
            const link = item.querySelector(':scope > a');
            const count = item.querySelector(':scope > .slowcloud-category-count');

            if (!childList || !link) {
                return;
            }

            item.classList.add('slowcloud-category-list-parent');

            if (count) {
                count.hidden = true;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'slowcloud-category-toggle iconfont icon-slowcloudarrow-right';
            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-label', `展开 ${link.textContent.trim()}`);

            button.addEventListener('click', () => {
                const isOpen = item.classList.toggle('slowcloud-is-open');
                button.classList.toggle('icon-slowcloudarrow-down', isOpen);
                button.classList.toggle('icon-slowcloudarrow-right', !isOpen);
                button.setAttribute('aria-expanded', String(isOpen));
                button.setAttribute('aria-label', `${isOpen ? '收起' : '展开'} ${link.textContent.trim()}`);
            });

            item.insertBefore(button, childList);
        });
    });

    const articleContent = document.querySelector('.slowcloud-article-detail .slowcloud-entry-content');
    const articleImages = articleContent
        ? Array.from(articleContent.querySelectorAll('img:not(.slowcloud-owo-image)'))
        : [];

    if (articleImages.length > 0) {
        let lightboxCleanupTimer = 0;
        const lightbox = document.createElement('div');
        lightbox.className = 'slowcloud-image-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.setAttribute('aria-label', '图片预览');
        lightbox.innerHTML = [
            '<button class="slowcloud-image-lightbox__close" type="button" aria-label="关闭图片预览">×</button>',
            '<figure class="slowcloud-image-lightbox__figure">',
            '<img class="slowcloud-image-lightbox__image" alt="">',
            '<figcaption class="slowcloud-image-lightbox__caption" hidden></figcaption>',
            '</figure>'
        ].join('');
        document.body.appendChild(lightbox);

        const closeButton = lightbox.querySelector('.slowcloud-image-lightbox__close');
        const previewImage = lightbox.querySelector('.slowcloud-image-lightbox__image');
        const caption = lightbox.querySelector('.slowcloud-image-lightbox__caption');

        const closeLightbox = () => {
            window.clearTimeout(lightboxCleanupTimer);
            lightbox.classList.remove('is-active');
            body.classList.remove('slowcloud-lightbox-open');
            lightboxCleanupTimer = window.setTimeout(() => {
                if (lightbox.classList.contains('is-active')) {
                    return;
                }

                previewImage.removeAttribute('src');
                previewImage.alt = '';
                caption.textContent = '';
                caption.hidden = true;
            }, 220);
        };

        const openLightbox = (image) => {
            const source = image.currentSrc || image.src;
            if (!source) {
                return;
            }

            window.clearTimeout(lightboxCleanupTimer);
            const alt = image.getAttribute('alt') || '';
            previewImage.src = source;
            previewImage.alt = alt;
            caption.textContent = alt;
            caption.hidden = alt.trim() === '';
            body.classList.add('slowcloud-lightbox-open');
            lightbox.classList.add('is-active');
            closeButton.focus({ preventScroll: true });
        };

        articleImages.forEach((image) => {
            if (image.closest('a')) {
                return;
            }

            image.classList.add('slowcloud-lightbox-image');
            image.setAttribute('tabindex', '0');
            image.setAttribute('role', 'button');
            image.setAttribute('aria-label', image.alt ? `查看图片：${image.alt}` : '查看图片');

            image.addEventListener('click', () => {
                openLightbox(image);
            });

            image.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                openLightbox(image);
            });
        });

        closeButton.addEventListener('click', closeLightbox);

        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && lightbox.classList.contains('is-active')) {
                closeLightbox();
            }
        });
    }

    const emojiToggle = document.querySelector('[data-slowcloud-emoji-toggle]');
    const emojiPanel = document.querySelector('[data-slowcloud-emoji-panel]');
    const commentTextarea = document.querySelector('#textarea');

    if (emojiToggle && emojiPanel && commentTextarea) {
        const emojiTabs = emojiPanel.querySelectorAll('[data-slowcloud-emoji-tab]');
        const emojiGroups = emojiPanel.querySelectorAll('[data-slowcloud-emoji-group]');

        const closeEmojiPanel = () => {
            emojiPanel.hidden = true;
            emojiToggle.setAttribute('aria-expanded', 'false');
        };

        const switchEmojiGroup = (target) => {
            emojiTabs.forEach((tab) => {
                const isActive = tab.getAttribute('data-target') === target;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', String(isActive));
            });

            emojiGroups.forEach((group) => {
                const isActive = group.getAttribute('data-slowcloud-emoji-group') === target;
                group.classList.toggle('is-active', isActive);
                group.hidden = !isActive;
            });
        };

        emojiToggle.addEventListener('click', () => {
            const isHidden = emojiPanel.hidden;
            emojiPanel.hidden = !isHidden ? true : false;
            emojiToggle.setAttribute('aria-expanded', String(isHidden));
        });

        emojiToggle.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            const isHidden = emojiPanel.hidden;
            emojiPanel.hidden = !isHidden ? true : false;
            emojiToggle.setAttribute('aria-expanded', String(isHidden));
        });

        emojiTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                switchEmojiGroup(tab.getAttribute('data-target') || 'emoji');
            });
        });

        emojiPanel.querySelectorAll('[data-slowcloud-emoji]').forEach((button) => {
            button.addEventListener('click', () => {
                const emoji = button.getAttribute('data-slowcloud-emoji') || '';
                const start = commentTextarea.selectionStart ?? commentTextarea.value.length;
                const end = commentTextarea.selectionEnd ?? commentTextarea.value.length;
                const current = commentTextarea.value;

                commentTextarea.value = `${current.slice(0, start)}${emoji}${current.slice(end)}`;
                commentTextarea.focus();

                const nextPosition = start + emoji.length;
                commentTextarea.setSelectionRange(nextPosition, nextPosition);
                closeEmojiPanel();
            });
        });

        switchEmojiGroup(emojiTabs[0]?.getAttribute('data-target') || 'emoji');

        document.addEventListener('click', (event) => {
            if (!emojiPanel.hidden && !emojiPanel.contains(event.target) && !emojiToggle.contains(event.target)) {
                closeEmojiPanel();
            }
        });
    }

    const cancelReplyLink = document.querySelector('#cancel-comment-reply-link');
    const cancelReplyHome = document.querySelector('[data-slowcloud-cancel-reply-home]');
    const commentRespond = document.querySelector('.slowcloud-comment-respond');

    if (cancelReplyLink && cancelReplyHome && commentRespond) {
        const setReplyState = (isReplying) => {
            commentRespond.classList.toggle('slowcloud-is-replying', isReplying);
        };

        setReplyState(false);

        document.addEventListener('click', (event) => {
            const cancelLink = event.target.closest('#cancel-comment-reply-link');
            if (cancelLink) {
                window.setTimeout(() => {
                    cancelReplyHome.appendChild(cancelReplyLink);
                    setReplyState(false);
                }, 0);
                return;
            }

            const replyLink = event.target.closest('.comment-reply a');
            if (!replyLink) {
                return;
            }

            const replyContainer = replyLink.closest('.comment-reply');
            if (!replyContainer) {
                return;
            }

            window.setTimeout(() => {
                replyContainer.appendChild(cancelReplyLink);
                setReplyState(true);
            }, 0);
        });
    }
})();
