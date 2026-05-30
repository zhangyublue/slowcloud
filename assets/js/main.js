(() => {
    const root = document.documentElement;
    const body = document.body;
    const toggle = document.querySelector('[data-slowcloud-theme-toggle]');
    const coverHeader = document.querySelector('[data-slowcloud-cover]');
    const categoryLists = document.querySelectorAll('.category-list');
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

    const applyTheme = (theme) => {
        root.dataset.slowcloudTheme = theme;

        if (!toggle) {
            return;
        }

        const isDark = theme === 'dark';
        toggle.setAttribute('aria-pressed', String(isDark));
        toggle.setAttribute('aria-label', isDark ? '切换到白天模式' : '切换到黑夜模式');
    };

    applyTheme(getPreferredTheme());
    root.classList.add('slowcloud-ready');

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

    const applyHeaderContrast = (mode) => {
        if (!coverHeader) {
            return;
        }

        coverHeader.dataset.slowcloudContrast = mode;
    };

    const detectHeaderContrast = () => {
        if (!coverHeader) {
            return;
        }

        const coverUrl = coverHeader.dataset.slowcloudCover;
        if (!coverUrl) {
            coverHeader.dataset.slowcloudContrast = root.dataset.slowcloudTheme === 'dark' ? 'light' : 'dark';
            return;
        }

        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.decoding = 'async';

        image.onload = () => {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d', { willReadFrequently: true });
            if (!context) {
                applyHeaderContrast('light');
                return;
            }

            const sampleWidth = 48;
            const sampleHeight = 48;
            canvas.width = sampleWidth;
            canvas.height = sampleHeight;
            context.drawImage(image, 0, 0, sampleWidth, sampleHeight);

            const { data } = context.getImageData(0, 0, sampleWidth, sampleHeight);
            let luminanceSum = 0;
            let visiblePixels = 0;

            for (let index = 0; index < data.length; index += 4) {
                const alpha = data[index + 3] / 255;
                if (alpha < 0.2) {
                    continue;
                }

                const red = data[index];
                const green = data[index + 1];
                const blue = data[index + 2];
                luminanceSum += 0.299 * red + 0.587 * green + 0.114 * blue;
                visiblePixels += 1;
            }

            if (!visiblePixels) {
                applyHeaderContrast('light');
                return;
            }

            const averageLuminance = luminanceSum / visiblePixels;
            applyHeaderContrast(averageLuminance > 156 ? 'dark' : 'light');
        };

        image.onerror = () => {
            applyHeaderContrast(root.dataset.slowcloudTheme === 'dark' ? 'light' : 'dark');
        };

        image.src = coverUrl;
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
                button.classList.toggle('icon-slowcloudarrow-left', isOpen);
                button.classList.toggle('icon-slowcloudarrow-right', !isOpen);
                button.setAttribute('aria-expanded', String(isOpen));
                button.setAttribute('aria-label', `${isOpen ? '收起' : '展开'} ${link.textContent.trim()}`);
            });

            item.insertBefore(button, childList);
        });
    });
})();
