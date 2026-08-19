import {EditorState} from '@codemirror/state';
import {EditorView, keymap, drawSelection, highlightActiveLine, lineNumbers, highlightActiveLineGutter} from '@codemirror/view';
import {defaultKeymap, history, historyKeymap, indentWithTab, undo, redo} from '@codemirror/commands';
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete';
import {markdown, markdownLanguage} from '@codemirror/lang-markdown';
import {highlightSelectionMatches} from '@codemirror/search';

export function mountSlowcloudEditor(options) {
    const textarea = options.textarea;
    const form = textarea.form;
    document.querySelectorAll('#wmd-preview').forEach(element => element.remove());
    let applyingPadding = false;
    const root = document.createElement('div');
    root.className = 'slowcloud-cm-editor';
    root.innerHTML = '<div class="slowcloud-cm-toolbar" role="toolbar"></div>'
        + '<div class="slowcloud-cm-workspace"><div class="slowcloud-cm-edit"></div>'
        + '<div class="slowcloud-cm-divider" tabindex="0" title="调整预览宽度"></div>'
        + '<div class="slowcloud-cm-preview" id="wmd-preview"></div></div>';
    textarea.parentNode.insertBefore(root, textarea);
    textarea.hidden = true;

    const edit = root.querySelector('.slowcloud-cm-edit');
    const preview = root.querySelector('.slowcloud-cm-preview');
    const toolbar = root.querySelector('.slowcloud-cm-toolbar');
    const counter = document.createElement('span');
    counter.className = 'slowcloud-cm-count';

    function updateCount(source) {
        counter.textContent = '字数 ' + source.replace(/\s/g, '').length;
    }

    function contentForSave(source) {
        return source.replace(/\n+$/, '');
    }

    function prepareCustomMarkdown(source) {
        let fenced = false;

        return source.split(/(\r?\n)/).map(line => {
            if (/^\s*(`{3,}|~{3,})/.test(line)) {
                fenced = !fenced;
                return line;
            }

            if (fenced) return line;

            return line.replace(/^(\s*(?:[-+*]|\d+[.)])\s+)\[([ xX])\]\s*(.*)$/, (match, prefix, state, content) => {
                return prefix + '<slowcloud-task data-slowcloud-syntax="task" checked="' + (state.toLowerCase() === 'x') + '">' + content + '</slowcloud-task>';
            });
        }).join('');
    }

    function renderCustomTags(container) {
        container.querySelectorAll('slowcloud-task[data-slowcloud-syntax="task"]').forEach(task => {
            const item = task.parentElement;
            const list = item && item.parentElement;
            const checked = task.getAttribute('checked') === 'true';
            const icon = document.createElement('i');

            icon.className = 'iconfont slowcloud-task-list__icon ' + (checked ? 'icon-slowcloudcheckbox' : 'icon-slowcloudcheckbox-uncheck');
            icon.setAttribute('aria-hidden', 'true');

            if (item && item.tagName === 'LI') {
                item.classList.add('slowcloud-task-list__item', checked ? 'slowcloud-task-list__item--checked' : 'slowcloud-task-list__item--unchecked');
            }
            if (list && (list.tagName === 'UL' || list.tagName === 'OL')) list.classList.add('slowcloud-task-list');

            task.parentNode.insertBefore(icon, task);
            while (task.firstChild) task.parentNode.insertBefore(task.firstChild, task);
            task.remove();
        });
    }

    const state = EditorState.create({
        doc: textarea.value,
        extensions: [
            lineNumbers(), highlightActiveLineGutter(), highlightActiveLine(), drawSelection(),
            history(), closeBrackets(),
            highlightSelectionMatches(), markdown({base: markdownLanguage}),
            keymap.of([...defaultKeymap, ...historyKeymap, ...closeBracketsKeymap, indentWithTab]),
            EditorView.lineWrapping,
            EditorView.updateListener.of(update => {
                if (!update.docChanged) return;
                const source = contentForSave(update.state.doc.toString());

                if (!applyingPadding) {
                    textarea.value = source;
                    textarea.dispatchEvent(new Event('input', {bubbles: true}));
                    if (window.jQuery) window.jQuery(form).trigger('write');
                }

                applyingPadding = false;
                renderPreview(source);
                updateCount(source);
                requestAnimationFrame(ensureVisibleLines);
            })
        ]
    });
    const view = new EditorView({state, parent: edit});

    view.dom.querySelector('.cm-gutters').addEventListener('mousedown', event => {
        const item = event.target.closest('.cm-gutterElement');
        const lineNumber = item ? Number(item.textContent) : 0;

        if (!Number.isInteger(lineNumber) || lineNumber < 1 || lineNumber > view.state.doc.lines) {
            return;
        }

        event.preventDefault();
        view.dispatch({selection: {anchor: view.state.doc.line(lineNumber).from}});
        view.focus();
    });

    function ensureVisibleLines() {
        const visibleLines = Math.ceil(edit.clientHeight / view.defaultLineHeight);
        const missing = Math.max(0, visibleLines - view.state.doc.lines);

        if (missing > 0) {
            applyingPadding = true;
            view.dispatch({changes: {from: view.state.doc.length, insert: '\n'.repeat(missing)}});
        }
    }

    function renderPreview(source) {
        if (!window.HyperDown) return;
        const converter = new window.HyperDown();
        converter.enableHtml(true);
        converter.enableLine(true);
        let html = converter.makeHtml(prepareCustomMarkdown(source));
        const rendered = document.createElement('div');
        rendered.innerHTML = html;
        renderCustomTags(rendered);
        html = rendered.innerHTML;

        if (window.DOMPurify) html = window.DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
        preview.innerHTML = html;
        preview.dispatchEvent(new CustomEvent('slowcloud:preview-refresh', {bubbles: true}));
    }

    function insert(before, after, fallback) {
        const range = view.state.selection.main;
        const selected = view.state.sliceDoc(range.from, range.to) || fallback || '';
        view.dispatch(view.state.replaceSelection(before + selected + after));
        view.focus();
    }

    function applyHeading(level) {
        const range = view.state.selection.main;
        const firstLine = view.state.doc.lineAt(range.from).number;
        const lastLine = view.state.doc.lineAt(range.to).number;
        const changes = [];

        for (let number = firstLine; number <= lastLine; number += 1) {
            const line = view.state.doc.line(number);
            const match = line.text.match(/^(\s{0,3})(?:#{1,6}\s+)?/);
            const indent = match ? match[1] : '';
            const prefixLength = match ? match[0].length : 0;
            changes.push({
                from: line.from,
                to: line.from + prefixLength,
                insert: indent + '#'.repeat(level) + ' '
            });
        }

        view.dispatch({changes});
        const firstLineAfterChange = view.state.doc.line(firstLine);
        const headingPrefix = firstLineAfterChange.text.match(/^\s{0,3}#+\s/);
        view.dispatch({selection: {anchor: firstLineAfterChange.from + (headingPrefix ? headingPrefix[0].length : level + 1)}});
        view.focus();
    }

    function createHeadingControl() {
        const control = document.createElement('div');
        const trigger = document.createElement('button');
        const menu = document.createElement('div');
        control.className = 'slowcloud-cm-heading-control';
        trigger.type = 'button';
        trigger.className = 'btn btn-xs slowcloud-cm-command';
        trigger.title = '标题级别';
        trigger.setAttribute('aria-label', '标题级别');
        trigger.innerHTML = '<i class="iconfont icon-slowcloudh" aria-hidden="true"></i>';
        menu.className = 'slowcloud-cm-heading-menu';
        menu.setAttribute('aria-label', '标题级别');

        for (let level = 1; level <= 6; level += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-xs slowcloud-cm-command';
            button.title = '标题 ' + level;
            button.setAttribute('aria-label', '标题 ' + level);
            button.innerHTML = '<i class="iconfont icon-slowcloud' + (12 + level) + 'biaoti' + level + '" aria-hidden="true"></i>';
            button.addEventListener('click', () => applyHeading(level));
            menu.appendChild(button);
        }

        trigger.addEventListener('click', () => applyHeading(2));
        control.appendChild(trigger);
        control.appendChild(menu);
        return control;
    }

    const commands = [
        ['bold', '加粗', 'icon-slowcloud01jiacu', () => insert('**', '**', '加粗文字')],
        ['italic', '斜体', 'icon-slowcloud02xieti', () => insert('*', '*', '斜体文字')],
        ['strike', '删除线', 'icon-slowcloudshanchuxian', () => insert('~~', '~~', '删除文字')],
        ['link', '链接', 'icon-slowcloudlianjie', () => insert('[', '](https://)', '链接文字')],
        ['image', '图片', 'icon-slowcloudshangchuantupian', () => insert('![', '](https://)', '图片描述')],
        ['quote', '引用', 'icon-slowcloudyinyong', () => insert('> ', '', '引用文字')],
        ['ul', '无序列表', 'icon-slowcloud20xiangmufuhao', () => insert('- ', '', '列表项目')],
        ['ol', '有序列表', 'icon-slowcloud21bianhaogeshi', () => insert('1. ', '', '列表项目')],
        ['code', '代码块', 'icon-slowclouddaimakuai', () => insert('```\n', '\n```', '代码')],
        ['inline-code', '行内代码', 'icon-slowcloudicon-daimakuai', () => insert('`', '`', '代码')],
        ['task', '任务列表', 'icon-slowcloudcheckbox_checked', () => insert('- [ ] ', '', '任务项目')],
        ['table', '表格', 'icon-slowcloudbiaodanzujian-biaoge', () => insert('| 表头 | 表头 |\n| --- | --- |\n| 内容 | 内容 |', '', '')],
        ['hr', '分割线', 'icon-slowcloudfengexian', () => insert('---\n', '', '')],
        ['more', '插入空行', 'icon-slowcloudshanchubeifen', () => insert('<!--more-->\n', '', '')],
        ['undo', '撤销', 'icon-slowcloudundo', () => { undo(view); view.focus(); }],
        ['redo', '重做', 'icon-slowcloudredo', () => { redo(view); view.focus(); }],
        ['fullscreen', '分栏全屏', 'icon-slowcloudfenlan', null]
    ];
    toolbar.appendChild(createHeadingControl());
    commands.forEach(item => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'btn btn-xs slowcloud-cm-command';
        button.dataset.command = item[0]; button.title = item[1]; button.setAttribute('aria-label', item[1]);
        button.innerHTML = '<i class="iconfont ' + item[2] + '" aria-hidden="true"></i>';
        if (item[3]) button.addEventListener('click', item[3]);
        if (item[0] === 'fullscreen') {
            button.addEventListener('click', () => {
                const fullscreen = root.classList.toggle('slowcloud-cm-fullscreen');
                const label = fullscreen ? '退出分栏' : '分栏全屏';
                button.title = label;
                button.setAttribute('aria-label', label);
                requestAnimationFrame(ensureVisibleLines);
            });
        }
        toolbar.appendChild(button);
    });
    const upload = document.createElement('button');
    upload.type = 'button'; upload.className = 'btn btn-xs slowcloud-cm-command';
    upload.title = '附件'; upload.setAttribute('aria-label', '附件');
    upload.innerHTML = '<i class="iconfont icon-slowcloudfujian" aria-hidden="true"></i>';
    upload.addEventListener('click', () => {
        const tab = document.querySelector('#tab-files-btn');
        if (tab) tab.click();
    });
    toolbar.appendChild(upload);
    toolbar.appendChild(counter);

    window.Typecho = window.Typecho || {};
    window.Typecho.insertFileToEditor = (file, url, isImage) => {
        insert(isImage ? '![' : '[', '](' + url + ')', file);
    };
    form.addEventListener('submit', () => { textarea.value = contentForSave(view.state.doc.toString()); });
    root.querySelector('.slowcloud-cm-divider').addEventListener('mousedown', event => {
        event.preventDefault();
        const move = e => { const rect = root.querySelector('.slowcloud-cm-workspace').getBoundingClientRect();
            preview.style.width = Math.max(240, Math.min(rect.width - 240, rect.right - e.clientX)) + 'px'; };
        const stop = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', stop); };
        document.addEventListener('mousemove', move); document.addEventListener('mouseup', stop);
    });
    renderPreview(textarea.value);
    updateCount(textarea.value);
    requestAnimationFrame(ensureVisibleLines);
    window.addEventListener('resize', ensureVisibleLines);
    return {view, refreshPreview: () => renderPreview(contentForSave(view.state.doc.toString()))};
}
