(function () {
    var config = window.SlowcloudEditorEnhance || {};
    var labels = config.labels || {};
    var activeEditor = config.editor || null;
    var prismConfig = config.prism || {};
    var headingButton = null;
    var menu = null;
    var outsideCloseBound = false;
    var assets = {};
    var prismReady = null;
    var highlightTimer = null;
    var highlightRunId = 0;
    var themeQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function injectStyle(id, href) {
        if (!href || document.getElementById(id)) {
            return;
        }

        var link = document.createElement('link');
        link.id = id;
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }

    function loadScript(src, attributes) {
        if (!src) {
            return Promise.reject(new Error('Missing script URL.'));
        }

        if (assets[src] === 'loaded') {
            return Promise.resolve();
        }

        if (assets[src]) {
            return assets[src];
        }

        assets[src] = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = src;

            if (attributes) {
                Object.keys(attributes).forEach(function (key) {
                    script.setAttribute(key, attributes[key]);
                });
            }

            script.onload = function () {
                assets[src] = 'loaded';
                resolve();
            };
            script.onerror = function () {
                assets[src] = null;
                reject(new Error('Failed to load ' + src));
            };
            document.head.appendChild(script);
        });

        return assets[src];
    }

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

        document.documentElement.setAttribute('data-slowcloud-editor-theme', theme);

        if (coy) {
            coy.disabled = theme !== 'light';
        }

        if (okaidia) {
            okaidia.disabled = theme !== 'dark';
        }
    }

    function ensurePrism() {
        if (prismReady) {
            return prismReady;
        }

        injectStyle('slowcloud-prism-line-numbers', prismConfig.lineNumbersStyle);
        injectStyle('slowcloud-prism-theme-coy', prismConfig.coyStyle);
        injectStyle('slowcloud-prism-theme-okaidia', prismConfig.okaidiaStyle);
        applyPrismTheme();

        window.Prism = window.Prism || {};
        window.Prism.manual = true;

        prismReady = loadScript(prismConfig.core)
            .then(function () {
                if (window.Prism && window.Prism.plugins && window.Prism.plugins.autoloader) {
                    window.Prism.plugins.autoloader.languages_path = prismConfig.components || '';
                }

                return loadScript(prismConfig.autoloader, {
                    'data-autoloader-path': prismConfig.components || ''
                });
            })
            .then(function () {
                if (window.Prism && window.Prism.plugins && window.Prism.plugins.autoloader) {
                    window.Prism.plugins.autoloader.languages_path = prismConfig.components || '';
                }

                return loadScript(prismConfig.lineNumbersScript);
            });

        return prismReady;
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

        if (match) {
            return normalizeLanguageName(match[1]);
        }

        var ignored = {
            'line': true,
            'line-numbers': true,
            'line-numbers-rows': true
        };
        var classes = (className + ' ' + preClassName).split(/\s+/);

        for (var i = 0; i < classes.length; i++) {
            if (classes[i] && !ignored[classes[i]] && !/^token$/.test(classes[i])) {
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

        if (!pre || pre.nodeName.toLowerCase() !== 'pre') {
            return null;
        }

        unwrapCodeBlock(pre);

        var language = detectLanguage(code);
        var lineMarkers = Array.prototype.slice.call(code.querySelectorAll('span.line'));

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
            language: language,
            lineMarkers: lineMarkers
        };
    }

    function restoreLineMarkers(item) {
        if (!item || !item.code || !item.lineMarkers.length) {
            return;
        }

        var lineNumbers = item.code.querySelector('.line-numbers-rows');
        var sizer = item.code.querySelector('.line-numbers-sizer');
        var lines;

        if (lineNumbers) {
            lineNumbers.parentNode.removeChild(lineNumbers);
        }

        if (sizer) {
            sizer.parentNode.removeChild(sizer);
        }

        lines = item.code.innerHTML.split('\n');

        item.lineMarkers.forEach(function (marker, index) {
            if (index < lines.length) {
                lines[index] = marker.outerHTML + lines[index];
            }
        });

        item.code.innerHTML = lines.join('\n');

        if (lineNumbers) {
            item.code.appendChild(lineNumbers);
        }
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
        if (!item || !item.pre || !item.pre.parentNode) {
            return;
        }

        var wrap = document.createElement('div');
        var bar = document.createElement('div');
        var dots = document.createElement('span');
        var title = document.createElement('span');

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

    function highlightPreviewCode() {
        window.clearTimeout(highlightTimer);
        highlightTimer = window.setTimeout(runPreviewHighlight, 40);
    }

    function runPreviewHighlight() {
        var preview = document.getElementById('wmd-preview');
        var runId = highlightRunId + 1;

        highlightRunId = runId;

        if (!preview) {
            return;
        }

        preview.classList.add('slowcloud-editor-preview');

        ensurePrism().then(function () {
            if (runId !== highlightRunId) {
                return;
            }

            if (!window.Prism) {
                return;
            }

            applyPrismTheme();

            var blocks = Array.prototype.slice.call(preview.querySelectorAll('pre > code'));
            var pending = blocks.length;

            if (!pending) {
                return;
            }

            function complete() {
                pending -= 1;

                if (pending <= 0 && window.jQuery) {
                    window.jQuery('#text').trigger('resize');
                }
            }

            blocks.forEach(function (code) {
                var item = prepareCodeBlock(code);

                if (!item) {
                    complete();
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

                        restoreLineMarkers(item);
                        wrapCodeBlock(item);
                        complete();
                    });
                });
            });
        }).catch(function () {});
    }

    function textArea() {
        return document.getElementById('text');
    }

    function triggerInput(textarea) {
        var event;

        if (typeof Event === 'function') {
            event = new Event('input', { bubbles: true });
        } else {
            event = document.createEvent('Event');
            event.initEvent('input', true, true);
        }

        textarea.dispatchEvent(event);

        if (window.jQuery) {
            window.jQuery(textarea).parents('form').trigger('write');
        }
    }

    function refreshPreview() {
        if (activeEditor && typeof activeEditor.refreshPreview === 'function') {
            activeEditor.refreshPreview();
        }
    }

    function lineRange(value, start, end) {
        var lineStart = value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
        var lineEnd = value.indexOf('\n', end);

        if (lineEnd === -1) {
            lineEnd = value.length;
        }

        return {
            start: lineStart,
            end: lineEnd,
            text: value.slice(lineStart, lineEnd)
        };
    }

    function cleanHeadingText(line) {
        return line.replace(/^\s{0,3}#{1,6}\s*/, '').replace(/\s+#{1,6}\s*$/, '');
    }

    function currentHeadingLevel(textarea) {
        var range = lineRange(textarea.value, textarea.selectionStart || 0, textarea.selectionEnd || 0);
        var match = range.text.match(/^\s{0,3}(#{1,6})(?:\s+|$)/);
        return match ? match[1].length : 0;
    }

    function setHeading(level) {
        var textarea = textArea();

        if (!textarea) {
            return;
        }

        textarea.focus();

        var value = textarea.value;
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || start;
        var hasSelection = start !== end;
        var range = lineRange(value, start, end);
        var selected = value.slice(start, end);
        var replacement;
        var newStart;
        var newEnd;

        if (hasSelection && selected.indexOf('\n') !== -1) {
            replacement = selected.split('\n').map(function (line) {
                var text = cleanHeadingText(line);

                if (!text.trim()) {
                    return '';
                }

                return level > 0 ? Array(level + 1).join('#') + ' ' + text : text;
            }).join('\n');
            newStart = start;
            newEnd = start + replacement.length;
            textarea.value = value.slice(0, start) + replacement + value.slice(end);
        } else {
            var source = hasSelection ? selected : range.text;
            var text = cleanHeadingText(source).trim();

            if (!text) {
                text = labels.placeholder || '标题文字';
            }

            replacement = level > 0 ? Array(level + 1).join('#') + ' ' + text : text;

            if (hasSelection) {
                newStart = start;
                newEnd = start + replacement.length;
                textarea.value = value.slice(0, start) + replacement + value.slice(end);
            } else {
                newStart = range.start;
                newEnd = range.start + replacement.length;
                textarea.value = value.slice(0, range.start) + replacement + value.slice(range.end);
            }
        }

        textarea.selectionStart = newStart;
        textarea.selectionEnd = newEnd;

        triggerInput(textarea);
        refreshPreview();
        closeMenu();
    }

    function insertCodeFence() {
        var textarea = textArea();

        if (!textarea) {
            return;
        }

        var value = textarea.value;
        var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : value.length;
        var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : start;
        var selected = value.slice(start, end);
        var prefix = start > 0 && value.charAt(start - 1) !== '\n' ? '\n\n' : '';
        var suffix = end < value.length && value.charAt(end) !== '\n' ? '\n\n' : '';
        var body = selected || '';
        var replacement;
        var cursorStart;
        var cursorEnd;

        if (body) {
            body = body.replace(/^\n+/, '').replace(/\n+$/, '');
            replacement = prefix + '```js\n' + body + '\n```' + suffix;
            cursorStart = start + prefix.length + '```js\n'.length;
            cursorEnd = cursorStart + body.length;
        } else {
            replacement = prefix + '```js\n\n```' + suffix;
            cursorStart = start + prefix.length + '```js\n'.length;
            cursorEnd = cursorStart;
        }

        textarea.value = value.slice(0, start) + replacement + value.slice(end);
        textarea.focus();
        textarea.selectionStart = cursorStart;
        textarea.selectionEnd = cursorEnd;

        triggerInput(textarea);
        refreshPreview();
        closeMenu();
    }

    function buildMenu() {
        var element = document.createElement('div');
        element.className = 'slowcloud-heading-menu';
        element.setAttribute('role', 'menu');
        element.hidden = true;

        for (var level = 1; level <= 6; level++) {
            element.appendChild(buildMenuButton('H' + level, level));
        }

        var divider = document.createElement('div');
        divider.className = 'slowcloud-heading-menu-divider';
        element.appendChild(divider);
        element.appendChild(buildMenuButton(labels.body || '正文', 0));

        document.body.appendChild(element);
        return element;
    }

    function buildMenuButton(text, level) {
        var button = document.createElement('button');
        button.type = 'button';
        button.textContent = text;
        button.setAttribute('role', 'menuitem');
        button.setAttribute('data-level', String(level));
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setHeading(level);
        });

        return button;
    }

    function markActiveLevel(level) {
        var buttons = menu ? menu.querySelectorAll('button[data-level]') : [];

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.toggle('active', Number(buttons[i].getAttribute('data-level')) === level);
        }
    }

    function positionMenu() {
        if (!headingButton || !menu) {
            return;
        }

        var rect = headingButton.getBoundingClientRect();
        var left = rect.left + window.pageXOffset;
        var top = rect.bottom + window.pageYOffset + 4;
        var maxLeft = window.pageXOffset + document.documentElement.clientWidth - menu.offsetWidth - 8;

        menu.style.left = Math.max(8 + window.pageXOffset, Math.min(left, maxLeft)) + 'px';
        menu.style.top = top + 'px';
    }

    function openMenu() {
        var textarea = textArea();

        if (!textarea || !headingButton || !menu) {
            return;
        }

        markActiveLevel(currentHeadingLevel(textarea));
        menu.hidden = false;
        headingButton.classList.add('active');
        headingButton.setAttribute('aria-expanded', 'true');
        positionMenu();
    }

    function closeMenu() {
        if (!menu || !headingButton) {
            return;
        }

        menu.hidden = true;
        headingButton.classList.remove('active');
        headingButton.setAttribute('aria-expanded', 'false');
    }

    function toggleMenu() {
        if (menu && !menu.hidden) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    function interceptHeadingButton() {
        headingButton = document.getElementById('wmd-heading-button');

        if (!headingButton || headingButton.getAttribute('data-slowcloud-heading') === '1') {
            return;
        }

        menu = menu || buildMenu();
        headingButton.setAttribute('data-slowcloud-heading', '1');
        headingButton.classList.add('slowcloud-heading-enhanced');
        headingButton.setAttribute('aria-haspopup', 'true');
        headingButton.setAttribute('aria-expanded', 'false');
        headingButton.title = labels.heading || headingButton.title || '标题级别';

        headingButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            toggleMenu();
        }, true);

        var textarea = textArea();
        if (textarea) {
            textarea.addEventListener('keydown', function (event) {
                if ((event.ctrlKey || event.metaKey) && !event.altKey && !event.shiftKey
                    && String.fromCharCode(event.charCode || event.keyCode).toLowerCase() === 'h') {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    toggleMenu();
                }
            }, true);
        }
    }

    function interceptCodeButton() {
        var codeButton = document.getElementById('wmd-code-button');
        var textarea;

        if (!codeButton || codeButton.getAttribute('data-slowcloud-code') === '1') {
            return;
        }

        codeButton.setAttribute('data-slowcloud-code', '1');
        codeButton.title = '代码块';

        codeButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            insertCodeFence();
        }, true);

        textarea = textArea();

        if (textarea && textarea.getAttribute('data-slowcloud-code-shortcut') !== '1') {
            textarea.setAttribute('data-slowcloud-code-shortcut', '1');
            textarea.addEventListener('keydown', function (event) {
                var key = event.key ? event.key.toLowerCase() : String.fromCharCode(event.charCode || event.keyCode).toLowerCase();

                if ((event.ctrlKey || event.metaKey) && !event.altKey && !event.shiftKey && key === 'k') {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    insertCodeFence();
                }
            }, true);
        }
    }

    function bindOutsideClose() {
        if (outsideCloseBound) {
            return;
        }

        outsideCloseBound = true;

        document.addEventListener('click', function (event) {
            if (!menu || menu.hidden) {
                return;
            }

            if ((headingButton && (event.target === headingButton || headingButton.contains(event.target)))
                || menu.contains(event.target)) {
                return;
            }

            closeMenu();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        window.addEventListener('resize', positionMenu);
        window.addEventListener('scroll', positionMenu, true);

        if (themeQuery) {
            if (typeof themeQuery.addEventListener === 'function') {
                themeQuery.addEventListener('change', function () {
                    applyPrismTheme();
                    highlightPreviewCode();
                });
            } else if (typeof themeQuery.addListener === 'function') {
                themeQuery.addListener(function () {
                    applyPrismTheme();
                    highlightPreviewCode();
                });
            }
        }
    }

    function forceMarkdownEditor() {
        var attempts = 0;

        function run() {
            var textarea = textArea();
            var scope = textarea && textarea.parentNode ? textarea.parentNode : document;
            var notice = scope.querySelector('.message.notice');
            var yesButton = notice ? notice.querySelector('button.yes') : null;

            if (yesButton) {
                yesButton.click();
                return;
            }

            attempts += 1;

            if (attempts < 20) {
                window.setTimeout(run, 25);
            }
        }

        window.setTimeout(run, 0);
    }

    function init() {
        interceptHeadingButton();
        interceptCodeButton();
        bindOutsideClose();
        highlightPreviewCode();
    }

    forceMarkdownEditor();

    if (activeEditor && activeEditor.hooks && typeof activeEditor.hooks.chain === 'function') {
        activeEditor.hooks.chain('onPreviewRefresh', function () {
            highlightPreviewCode();
        });

        activeEditor.hooks.chain('makeButton', function (buttons) {
            window.setTimeout(init, 0);
            return buttons;
        });
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        window.setTimeout(init, 0);
    }
})();
