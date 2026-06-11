(function () {
    var config = window.SlowcloudCodeHighlight || {};
    var prismConfig = config.prism || {};
    var themeQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var highlightRunId = 0;
    var highlightTimer = null;

    function getStoredTheme() {
        try {
            return window.sessionStorage.getItem('slowcloud-theme');
        } catch (error) {
            return null;
        }
    }

    function currentTheme() {
        var stored = getStoredTheme();

        if (stored === 'dark' || stored === 'light') {
            return stored;
        }

        if (config.themeMode === 'dark' || config.themeMode === 'light') {
            return config.themeMode;
        }

        return themeQuery && themeQuery.matches ? 'dark' : 'light';
    }

    function applyPrismTheme() {
        var theme = currentTheme();
        var coy = document.getElementById('slowcloud-prism-theme-coy');
        var okaidia = document.getElementById('slowcloud-prism-theme-okaidia');

        document.documentElement.setAttribute('data-slowcloud-code-theme', theme);

        if (coy) {
            coy.disabled = theme !== 'light';
        }

        if (okaidia) {
            okaidia.disabled = theme !== 'dark';
        }
    }

    function normalizeLanguageName(language) {
        var map = {
            'c++': 'cpp',
            'c#': 'csharp',
            'cs': 'csharp',
            'html': 'markup',
            'htm': 'markup',
            'js': 'javascript',
            'md': 'markdown',
            'py': 'python',
            'rb': 'ruby',
            'shell': 'bash',
            'sh': 'bash',
            'text': 'plaintext',
            'txt': 'plaintext',
            'vue': 'markup',
            'xml': 'markup'
        };

        language = String(language || '').trim().toLowerCase();
        return map[language] || language || 'none';
    }

    function detectLanguage(code) {
        var className = code.className || '';
        var preClassName = code.parentNode ? code.parentNode.className || '' : '';
	    var match = (className + ' ' + preClassName).match(/(?:^|\s)(?:lang|language)-([^\s]+)/i);
	    var ignored = {
	        'line': true,
	        'line-numbers': true,
	        'line-numbers-rows': true
	    };
        var classes;
        var i;

        if (match) {
            return normalizeLanguageName(match[1]);
        }

        classes = (className + ' ' + preClassName).split(/\s+/);

        for (i = 0; i < classes.length; i++) {
            if (classes[i] && !ignored[classes[i]] && classes[i] !== 'token') {
                return normalizeLanguageName(classes[i]);
            }
        }

        return 'none';
    }

    function addLanguageClass(element, language) {
        if (!element || !language) {
            return;
        }

        Array.prototype.slice.call(element.classList).forEach(function (className) {
            if (/^(?:lang|language)-/.test(className)) {
                element.classList.remove(className);
            }
        });

        element.classList.add('language-' + language);
    }

    function unwrapCodeBlock(pre) {
        var wrap = pre && pre.parentNode;
        var parent = wrap && wrap.parentNode;

        if (!wrap || !parent || !wrap.classList || !wrap.classList.contains('slowcloud-code-window')) {
            return;
        }

        parent.insertBefore(pre, wrap);
        parent.removeChild(wrap);
    }

    function prepareCodeBlock(code) {
        var pre = code.parentNode;
        var language;

        if (!pre || pre.nodeName.toLowerCase() !== 'pre') {
            return null;
        }

        unwrapCodeBlock(pre);
        language = detectLanguage(code);

        Array.prototype.slice.call(code.querySelectorAll('.line-numbers-rows, .line-numbers-sizer')).forEach(function (node) {
            node.parentNode.removeChild(node);
        });

	    code.setAttribute('data-slowcloud-raw-code', code.textContent);
	    code.textContent = code.getAttribute('data-slowcloud-raw-code');
        pre.classList.remove('line-numbers');
        addLanguageClass(pre, language);
        addLanguageClass(code, language);
        pre.classList.add('line-numbers');

        return {
            code: code,
            pre: pre,
            language: language
        };
    }

    function loadPrismLanguage(language) {
        return new Promise(function (resolve) {
            var autoloader = window.Prism && window.Prism.plugins && window.Prism.plugins.autoloader;

            if (!autoloader || !language || language === 'none' || window.Prism.languages[language]) {
                resolve();
                return;
            }

            autoloader.loadLanguages(language, function () {
                resolve();
            }, function () {
                resolve();
            });
        });
    }

    function wrapCodeBlock(item) {
        var wrap;
        var bar;
        var dots;
        var title;

        if (!item || !item.pre || !item.pre.parentNode) {
            return;
        }

        wrap = document.createElement('div');
        bar = document.createElement('div');
        dots = document.createElement('span');
        title = document.createElement('span');

        wrap.className = 'slowcloud-code-window';
        bar.className = 'slowcloud-code-window__bar';
        dots.className = 'slowcloud-code-window__dots';
        title.className = 'slowcloud-code-window__title';
        title.textContent = item.language && item.language !== 'none' ? item.language : 'code';

        bar.appendChild(dots);
        bar.appendChild(title);
        item.pre.parentNode.insertBefore(wrap, item.pre);
        wrap.appendChild(bar);
        wrap.appendChild(item.pre);
    }

    function highlightCodeBlocks() {
        window.clearTimeout(highlightTimer);
        highlightTimer = window.setTimeout(runHighlight, 40);
    }

    function runHighlight() {
        var runId = highlightRunId + 1;
        var containers = document.querySelectorAll('.slowcloud-entry-content');
        var blocks;

        highlightRunId = runId;
        applyPrismTheme();

        if (!window.Prism || !containers.length) {
            return;
        }

        if (window.Prism.plugins && window.Prism.plugins.autoloader && prismConfig.components) {
            window.Prism.plugins.autoloader.languages_path = prismConfig.components;
        }

        blocks = Array.prototype.slice.call(document.querySelectorAll('.slowcloud-entry-content pre > code'));

        blocks.forEach(function (code) {
            var item = prepareCodeBlock(code);

            if (!item) {
                return;
            }

            loadPrismLanguage(item.language).then(function () {
                if (runId !== highlightRunId) {
                    return;
                }

                window.Prism.highlightElement(item.code, false, function () {
                    if (runId !== highlightRunId) {
                        return;
                    }

                    wrapCodeBlock(item);
                });
            });
        });
    }

    function bindThemeSync() {
        window.addEventListener('slowcloud-theme-change', highlightCodeBlocks);

        if (themeQuery) {
            if (typeof themeQuery.addEventListener === 'function') {
                themeQuery.addEventListener('change', highlightCodeBlocks);
            } else if (typeof themeQuery.addListener === 'function') {
                themeQuery.addListener(highlightCodeBlocks);
            }
        }

        document.addEventListener('click', function (event) {
            if (event.target && event.target.closest && event.target.closest('[data-slowcloud-theme-toggle]')) {
                window.setTimeout(highlightCodeBlocks, 0);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            bindThemeSync();
            highlightCodeBlocks();
        });
    } else {
        bindThemeSync();
        highlightCodeBlocks();
    }
})();
