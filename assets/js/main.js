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

    const applyHeaderContrast = (mode) => {
        if (!coverHeader) {
            return;
        }

        coverHeader.dataset.slowcloudContrast = mode;
    };

    const applyTheme = (theme) => {
        root.dataset.slowcloudTheme = theme;
        applyHeaderContrast(theme === 'dark' ? 'light' : 'dark');

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
})();
