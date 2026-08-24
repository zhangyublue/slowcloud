import {EditorState} from '@codemirror/state';
import {EditorView, keymap, drawSelection, highlightActiveLine, lineNumbers, highlightActiveLineGutter, Decoration, ViewPlugin} from '@codemirror/view';
import {defaultKeymap, history, historyKeymap, indentWithTab, undo, redo} from '@codemirror/commands';
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete';
import {syntaxTree} from '@codemirror/language';
import {markdown, markdownLanguage} from '@codemirror/lang-markdown';
import {LanguageDescription, HighlightStyle, syntaxHighlighting} from '@codemirror/language';
import {tags} from '@lezer/highlight';
import {javascript} from '@codemirror/lang-javascript';
import {python} from '@codemirror/lang-python';
import {php} from '@codemirror/lang-php';
import {json} from '@codemirror/lang-json';
import {css} from '@codemirror/lang-css';
import {html} from '@codemirror/lang-html';
import {sql} from '@codemirror/lang-sql';
import {rust} from '@codemirror/lang-rust';
import {cpp} from '@codemirror/lang-cpp';
import {java} from '@codemirror/lang-java';
import {GFM} from '@lezer/markdown';
import {highlightSelectionMatches} from '@codemirror/search';

const markdownCodeLanguages = [
    LanguageDescription.of({name: 'JavaScript', alias: ['js', 'jsx'], extensions: ['js'], support: javascript()}),
    LanguageDescription.of({name: 'TypeScript', alias: ['ts', 'tsx'], extensions: ['ts'], support: javascript({typescript: true})}),
    LanguageDescription.of({name: 'Python', alias: ['py'], extensions: ['py'], support: python()}),
    LanguageDescription.of({name: 'PHP', alias: ['php'], extensions: ['php'], support: php()}),
    LanguageDescription.of({name: 'JSON', alias: ['json'], extensions: ['json'], support: json()}),
    LanguageDescription.of({name: 'CSS', alias: ['css'], extensions: ['css'], support: css()}),
    LanguageDescription.of({name: 'HTML', alias: ['html', 'xml'], extensions: ['html'], support: html()}),
    LanguageDescription.of({name: 'SQL', alias: ['sql'], extensions: ['sql'], support: sql()}),
    LanguageDescription.of({name: 'Rust', alias: ['rust', 'rs'], extensions: ['rs'], support: rust()}),
    LanguageDescription.of({name: 'C++', alias: ['cpp', 'c++'], extensions: ['cpp', 'cc', 'cxx'], support: cpp()}),
    LanguageDescription.of({name: 'Java', alias: ['java'], extensions: ['java'], support: java()})
];

const slowcloudCodeHighlighting = syntaxHighlighting(HighlightStyle.define([
    {tag: [tags.keyword, tags.controlKeyword, tags.operatorKeyword, tags.definitionKeyword], color: '#8A3FFC'},
    {tag: [tags.string, tags.special(tags.string)], color: '#0B7A75'},
    {tag: tags.comment, color: '#77838F'},
    {tag: [tags.number, tags.bool, tags.null], color: '#B65300'},
    {tag: [tags.function(tags.variableName), tags.labelName], color: '#1769AA'},
    {tag: [tags.typeName, tags.className], color: '#B83232'},
    {tag: [tags.propertyName, tags.variableName], color: '#334155'}
]));

const markdownMarkerClasses = {
    'slowcloud-cm-mark-heading': Decoration.mark({class: 'slowcloud-cm-mark-heading'}),
    'slowcloud-cm-heading-content': Decoration.mark({class: 'slowcloud-cm-heading-content'}),
    'slowcloud-cm-strong-content': Decoration.mark({class: 'slowcloud-cm-strong-content'}),
    'slowcloud-cm-emphasis-content': Decoration.mark({class: 'slowcloud-cm-emphasis-content'}),
    'slowcloud-cm-strike-content': Decoration.mark({class: 'slowcloud-cm-strike-content'}),
    'slowcloud-cm-inline-code-content': Decoration.mark({class: 'slowcloud-cm-inline-code-content'}),
    'slowcloud-cm-link-label': Decoration.mark({class: 'slowcloud-cm-link-label'}),
    'slowcloud-cm-link-url': Decoration.mark({class: 'slowcloud-cm-link-url'}),
    'slowcloud-cm-mark-code': Decoration.mark({class: 'slowcloud-cm-mark-code'}),
    'slowcloud-cm-mark-list': Decoration.mark({class: 'slowcloud-cm-mark-list'}),
    'slowcloud-cm-mark-quote': Decoration.mark({class: 'slowcloud-cm-mark-quote'}),
    'slowcloud-cm-mark-task-open': Decoration.mark({class: 'slowcloud-cm-mark-task-open'}),
    'slowcloud-cm-mark-task-done': Decoration.mark({class: 'slowcloud-cm-mark-task-done'})
};

function markdownMarkerDecoration(name, source) {
    if (/^SetextHeading[1-6]$/.test(name)) {
        return markdownMarkerClasses['slowcloud-cm-heading-content'];
    }

    switch (name) {
        case 'HeaderMark': return markdownMarkerClasses['slowcloud-cm-mark-heading'];
        case 'CodeMark': return source.length <= 2 ? markdownMarkerClasses['slowcloud-cm-inline-code-content'] : markdownMarkerClasses['slowcloud-cm-mark-code'];
        case 'ListMark': return markdownMarkerClasses['slowcloud-cm-mark-list'];
        case 'QuoteMark': return markdownMarkerClasses['slowcloud-cm-mark-quote'];
        case 'TaskMarker': return source.toLowerCase() === '[x]' ? markdownMarkerClasses['slowcloud-cm-mark-task-done'] : markdownMarkerClasses['slowcloud-cm-mark-task-open'];
        default: return null;
    }
}

const markdownMarkerHighlighter = ViewPlugin.fromClass(class {
    constructor(view) {
        this.decorations = this.buildDecorations(view);
    }

    update(update) {
        if (update.docChanged || update.viewportChanged) this.decorations = this.buildDecorations(update.view);
    }

    buildDecorations(view) {
        const decorations = [];
        const doc = view.state.doc;

        let inFence = false;
        for (let number = 1; number <= doc.lines; number += 1) {
            const line = doc.line(number);
            const text = line.text;
            if (/^\s*(`{3,}|~{3,})/.test(text)) {
                inFence = !inFence;
                continue;
            }
            if (!inFence && /^\s{0,3}#{1,6}(?=\s|$)/.test(text)) {
                decorations.push(markdownMarkerClasses['slowcloud-cm-heading-content'].range(line.from, line.to));
            }
        }

        syntaxTree(view.state).iterate({
            enter: node => {
                const source = doc.sliceString(node.from, node.to);

                if (node.name === 'StrongEmphasis') {
                    decorations.push(markdownMarkerClasses['slowcloud-cm-strong-content'].range(node.from, node.to));
                    return false;
                }
                if (node.name === 'Emphasis') {
                    decorations.push(markdownMarkerClasses['slowcloud-cm-emphasis-content'].range(node.from, node.to));
                    return false;
                }
                if (node.name === 'Strikethrough') {
                    decorations.push(markdownMarkerClasses['slowcloud-cm-strike-content'].range(node.from, node.to));
                    return false;
                }
                if (node.name === 'InlineCode') {
                    decorations.push(markdownMarkerClasses['slowcloud-cm-inline-code-content'].range(node.from, node.to));
                    return false;
                }
                if (node.name === 'Link') {
                    const labelEnd = source.indexOf(']');
                    const urlEnd = source.lastIndexOf(')');

                    if (source.startsWith('[') && labelEnd > 0 && source[labelEnd + 1] === '(' && urlEnd > labelEnd + 2) {
                        decorations.push(markdownMarkerClasses['slowcloud-cm-link-label'].range(node.from + 1, node.from + labelEnd));
                        decorations.push(markdownMarkerClasses['slowcloud-cm-link-url'].range(node.from + labelEnd + 2, node.from + urlEnd));
                    }
                    return;
                }

                const decoration = markdownMarkerDecoration(node.name, source);
                if (decoration) decorations.push(decoration.range(node.from, node.to));
            }
        });

        return Decoration.set(decorations, true);
    }
}, {decorations: plugin => plugin.decorations});

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
    let previousBodyOverflow = '';

    function updateCount(source) {
        counter.textContent = '字数 ' + source.replace(/\s/g, '').length;
    }

    function contentForSave(source) {
        return source.replace(/\n+$/, '');
    }

    function timelineAttributes(source) {
        const attributes = {};
        const pattern = /\s+([a-z-]+)="([^"]*)"/gy;
        let offset = 0;
        let match;

        while (offset < source.length) {
            pattern.lastIndex = offset;
            match = pattern.exec(source);
            if (!match) return null;
            attributes[match[1]] = match[2];
            offset = pattern.lastIndex;
        }
        return attributes;
    }

    function timelineColorIsValid(color) {
        return /^(?:#[0-9a-f]{3,4}|#[0-9a-f]{6}(?:[0-9a-f]{2})?|(?:rgb|hsl)a?\(\s*[0-9.%]+(?:\s*[,/]\s*[0-9.%]+){2,3}\s*\)|[a-z]+)$/i.test(color);
    }

    function compileTimelines(source, convertMarkdown) {
        const timelines = [];
        const timelinePattern = /^[ \t]*\[timeline((?:\s+[a-z-]+="[^"]*")*)\][ \t]*(?:\r?\n)(.*?)^[ \t]*\[\/timeline\][ \t]*$/gms;
        const itemPattern = /^[ \t]*\[timeline-item((?:\s+[a-z-]+="[^"]*")*)\][ \t]*(?:\r?\n)(.*?)^[ \t]*\[\/timeline-item\][ \t]*(?:\r?\n|$)/gms;
        const sidePattern = /^[ \t]*\[timeline-item-(left|right)\][ \t]*(?:\r?\n)(.*?)^[ \t]*\[\/timeline-item-\1\][ \t]*(?:\r?\n|$)/gms;

        const compiled = source.replace(timelinePattern, (block, timelineSource, body) => {
            const timeline = timelineAttributes(timelineSource);
            const mode = timeline && (timeline.mode || 'left');
            if (!timeline || !['left', 'right', 'medium'].includes(mode)) return block;

            const matches = [...body.matchAll(itemPattern)];
            if (!matches.length || body.replace(itemPattern, '').trim() !== '') return block;

            const items = [];
            for (const match of matches) {
                const item = timelineAttributes(match[1]);
                const color = item && (item.color || 'blue');
                const solid = item && (item.solid || 'false');
                const gap = item && (item.gap || '25');
                const line = item && (item.line || 'solid');
                if (!item
                    || !timelineColorIsValid(color)
                    || !['true', 'false'].includes(solid)
                    || !/^\d+(?:\.\d+)?$/.test(gap)
                    || !['solid', 'dash'].includes(line)) return block;
                const itemTag = '<slowcloud-timeline-item color="' + color
                    + '" solid="' + solid + '" gap="' + gap + '" line="' + line + '">';

                if (mode !== 'medium') {
                    items.push(itemTag + convertMarkdown(match[2]) + '</slowcloud-timeline-item>');
                    continue;
                }

                const sides = [...match[2].matchAll(sidePattern)];
                if (!sides.length || match[2].replace(sidePattern, '').trim() !== '') return block;
                const content = {};
                for (const side of sides) {
                    if (content[side[1]] !== undefined) return block;
                    content[side[1]] = convertMarkdown(side[2]);
                }
                items.push(itemTag
                    + (content.left !== undefined ? '<slowcloud-timeline-item-left>' + content.left + '</slowcloud-timeline-item-left>' : '')
                    + (content.right !== undefined ? '<slowcloud-timeline-item-right>' + content.right + '</slowcloud-timeline-item-right>' : '')
                    + '</slowcloud-timeline-item>');
            }

            const id = timelines.length;
            timelines.push('<slowcloud-timeline data-slowcloud-syntax="timeline" mode="' + mode + '">' + items.join('') + '</slowcloud-timeline>');
            return '<!--slowcloud-timeline:' + id + '-->';
        });

        return {source: compiled, timelines};
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

        container.querySelectorAll('slowcloud-timeline[data-slowcloud-syntax="timeline"]').forEach(timeline => {
            const mode = ['left', 'right', 'medium'].includes(timeline.getAttribute('mode')) ? timeline.getAttribute('mode') : 'left';
            const component = document.createElement('section');
            component.className = 'slowcloud-timeline slowcloud-timeline--' + mode;
            let previousLine = 'solid';

            [...timeline.children].filter(child => child.tagName === 'SLOWCLOUD-TIMELINE-ITEM').forEach(item => {
                const colorValue = item.getAttribute('color') || 'blue';
                const color = timelineColorIsValid(colorValue) ? colorValue : 'blue';
                const solid = item.getAttribute('solid') === 'true';
                const gap = /^\d+(?:\.\d+)?$/.test(item.getAttribute('gap')) ? item.getAttribute('gap') : '25';
                const line = item.getAttribute('line') === 'dash' ? 'dash' : 'solid';
                const entry = document.createElement('article');
                const rail = document.createElement('div');
                const dot = document.createElement('span');
                const left = document.createElement('div');
                const right = document.createElement('div');
                entry.className = 'slowcloud-timeline__item' + (['blue', 'green', 'red', 'gray'].includes(color) ? ' slowcloud-timeline__item--' + color : '') + (solid ? ' slowcloud-timeline__item--solid' : '');
                entry.style.setProperty('--slowcloud-timeline-color', color);
                entry.style.setProperty('--slowcloud-timeline-gap', gap + 'px');
                entry.style.setProperty('--slowcloud-timeline-line-style', line === 'dash' ? 'dashed' : 'solid');
                entry.style.setProperty('--slowcloud-timeline-incoming-line-style', previousLine === 'dash' ? 'dashed' : 'solid');
                rail.className = 'slowcloud-timeline__rail';
                dot.className = 'slowcloud-timeline__dot';
                left.className = 'slowcloud-timeline__content slowcloud-timeline__content--left';
                right.className = 'slowcloud-timeline__content slowcloud-timeline__content--right';
                rail.appendChild(dot);

                if (mode === 'medium') {
                    [...item.children].forEach(child => {
                        const target = child.tagName === 'SLOWCLOUD-TIMELINE-ITEM-LEFT' ? left : right;
                        while (child.firstChild) target.appendChild(child.firstChild);
                    });
                    entry.append(left, rail, right);
                } else {
                    while (item.firstChild) right.appendChild(item.firstChild);
                    if (mode === 'right') entry.append(right, rail);
                    else entry.append(rail, right);
                }
                component.appendChild(entry);
                previousLine = line;
            });
            timeline.replaceWith(component);
        });

        alignTimelines(container);
    }

    function timelineContentNaturalWidth(content) {
        const previous = {
            position: content.style.position,
            visibility: content.style.visibility,
            display: content.style.display,
            width: content.style.width,
            maxWidth: content.style.maxWidth
        };
        Object.assign(content.style, {
            position: 'absolute',
            visibility: 'hidden',
            display: 'block',
            width: 'max-content',
            maxWidth: 'none'
        });
        const width = Math.ceil(content.getBoundingClientRect().width);
        Object.assign(content.style, previous);
        return width;
    }

    function alignTimelines(container) {
        const mobile = window.matchMedia('(max-width: 640px)').matches;
        container.querySelectorAll('.slowcloud-timeline--right, .slowcloud-timeline--medium').forEach(timeline => {
            const items = [...timeline.querySelectorAll('.slowcloud-timeline__item')];
            items.forEach(item => item.style.removeProperty('--slowcloud-timeline-left-width'));

            if (mobile) {
                timeline.querySelectorAll('.slowcloud-timeline__content--constrained').forEach(content => {
                    content.classList.remove('slowcloud-timeline__content--constrained');
                });
                return;
            }
            if (timeline.clientWidth <= 0) return;

            const isMedium = timeline.classList.contains('slowcloud-timeline--medium');
            const contents = timeline.querySelectorAll(isMedium
                ? '.slowcloud-timeline__content--left'
                : '.slowcloud-timeline__content');
            let naturalWidth = 0;
            contents.forEach(content => {
                content.classList.remove('slowcloud-timeline__content--constrained');
                naturalWidth = Math.max(naturalWidth, timelineContentNaturalWidth(content));
            });

            const gaps = isMedium ? 36 : 18;
            const reserved = isMedium ? 14 + 60 : 14;
            const leftWidth = Math.min(naturalWidth, Math.max(0, timeline.clientWidth - gaps - reserved));
            items.forEach(item => item.style.setProperty('--slowcloud-timeline-left-width', leftWidth + 'px'));
            contents.forEach(content => content.classList.toggle(
                'slowcloud-timeline__content--constrained',
                content.scrollWidth > content.clientWidth + 1
            ));
            if (isMedium) {
                timeline.querySelectorAll('.slowcloud-timeline__content--right').forEach(content => content.classList.toggle(
                    'slowcloud-timeline__content--constrained',
                    content.scrollWidth > content.clientWidth + 1
                ));
            }
        });

        container.querySelectorAll('.slowcloud-timeline__item').forEach(item => {
            const left = item.querySelector('.slowcloud-timeline__content--left');
            const right = item.querySelector('.slowcloud-timeline__content--right');
            if (!item.closest('.slowcloud-timeline--medium')) return;
            const itemBox = item.getBoundingClientRect();
            const leftTarget = left && left.firstElementChild;
            const rightTarget = right && right.firstElementChild;
            const leftBox = leftTarget && leftTarget.getBoundingClientRect();
            const rightBox = rightTarget && rightTarget.getBoundingClientRect();
            const target = !leftBox || !rightBox || leftBox.height <= rightBox.height ? leftBox || rightBox : rightBox;
            if (!target) return;
            item.style.setProperty('--slowcloud-timeline-dot-offset', Math.round(target.top - itemBox.top + target.height / 2) + 'px');
        });
    }

    const state = EditorState.create({
        doc: textarea.value,
        extensions: [
            lineNumbers(), highlightActiveLineGutter(), highlightActiveLine(), drawSelection(),
            history(), closeBrackets(),
            highlightSelectionMatches(), markdown({base: markdownLanguage, codeLanguages: markdownCodeLanguages, extensions: GFM}), slowcloudCodeHighlighting, markdownMarkerHighlighter,
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
        const timeline = compileTimelines(source, content => converter.makeHtml(prepareCustomMarkdown(content)));
        let html = converter.makeHtml(prepareCustomMarkdown(timeline.source));
        timeline.timelines.forEach((component, id) => {
            html = html.replace('<!--slowcloud-timeline:' + id + '-->', component);
        });
        const rendered = document.createElement('div');
        rendered.innerHTML = html;
        renderCustomTags(rendered);
        html = rendered.innerHTML;

        if (window.DOMPurify) html = window.DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
        preview.innerHTML = html;
        requestAnimationFrame(() => requestAnimationFrame(() => alignTimelines(preview)));
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

    const toolbarIcons = {
        heading: '<path d="M4.5 5v14M11.5 5v14M4.5 12h7M15.5 7h4M15.5 12h4M15.5 17h4"/>',
        heading1: '<path d="M4 6v12M10 6v12M4 12h6M16 8l2-2v12"/>',
        heading2: '<path d="M4 6v12M10 6v12M4 12h6M15 8.5a2.5 2.5 0 0 1 5 0c0 3.5-5 4.5-5 8h5"/>',
        heading3: '<path d="M4 6v12M10 6v12M4 12h6M15 7h4l-2.5 5H18a2.5 2.5 0 0 1 0 5h-3"/>',
        heading4: '<path d="M4 6v12M10 6v12M4 12h6M19 18V6l-4 8h6"/>',
        heading5: '<path d="M4 6v12M10 6v12M4 12h6M20 6h-5v5h2.5a2.5 2.5 0 0 1 0 5H15"/>',
        heading6: '<path d="M4 6v12M10 6v12M4 12h6M20 7h-2.5A2.5 2.5 0 0 0 15 9.5v6a2.5 2.5 0 0 0 5 0v-1a2.5 2.5 0 0 0-5 0"/>',
        bold: '<path d="M7 5h5a3 3 0 0 1 0 6H7zm0 6h6a3.5 3.5 0 0 1 0 7H7z"/>',
        italic: '<path d="M10 5h8M6 19h8M15 5 9 19"/>',
        strike: '<path d="M16.5 7.5A6 6 0 0 0 12 5.7c-2.5 0-4.2 1.2-4.2 3 0 1.8 1.6 2.7 4.2 3.4s4.2 1.7 4.2 3.5c0 1.9-1.8 3.2-4.4 3.2a6.8 6.8 0 0 1-4.7-1.8M4 12h16"/>',
        link: '<path d="M10 13.5a4 4 0 0 0 5.7.1l2-2a4 4 0 0 0-5.7-5.7l-1.1 1.1M14 10.5a4 4 0 0 0-5.7-.1l-2 2a4 4 0 0 0 5.7 5.7l1.1-1.1"/>',
        image: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><circle cx="9" cy="10" r="1.3"/><path d="m5 17 4.5-4 3 2.5 2.5-2 4 3.5"/>',
        quote: '<path d="M5 7h4.5L7.2 12H10v6H4v-6zm10 0h4.5L17.2 12H20v6h-6v-6z" fill="currentColor" stroke="none"/>',
        unorderedList: '<circle cx="5" cy="7" r="1"/><circle cx="5" cy="12" r="1"/><circle cx="5" cy="17" r="1"/><path d="M9 7h10M9 12h10M9 17h10"/>',
        orderedList: '<path d="M4.5 6.5h1v4M4 10.5h2M4 13.5h1a1.5 1.5 0 0 1 0 3H4l2 2H4M9 7h10M9 12h10M9 17h10"/>',
        codeBlock: '<path d="m9 8-4 4 4 4M15 8l4 4-4 4M13 6l-2 12"/>',
        inlineCode: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="m10 9-3 3 3 3M14 9l3 3-3 3"/>',
        task: '<rect x="5" y="5" width="14" height="14" rx="1.5"/><path d="m8 12 2.4 2.5L16 9"/>',
        table: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M4 10h16M4 15h16M10 5v14M15 5v14"/>',
        horizontalRule: '<rect x="6" y="3.5" width="12" height="4" rx=".35"/><path d="M3 12h18"/><rect x="6" y="16.5" width="12" height="4" rx=".35"/>',
        lineBreak: '<rect x="6" y="3.5" width="13" height="3.5" rx=".35"/><path d="M4 9.5 8 12l-4 2.5z" fill="#1E6FE5" stroke="none"/><rect x="9" y="10.25" width="10" height="3.5" rx=".35"/><rect x="6" y="17" width="13" height="3.5" rx=".35"/>',
        undo: '<path d="M9 8 5 12l4 4M5 12h8a5 5 0 0 1 5 5"/>',
        redo: '<path d="m15 8 4 4-4 4M19 12h-8a5 5 0 0 0-5 5"/>',
        splitPane: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M12 5v14M8.5 9v6M15.5 9v6"/>',
        timeline: '<path d="M7 3v18M12 6h9M12 12h11M12 18h8"/><circle cx="7" cy="8" r="2.4"/><circle cx="7" cy="16" r="2.4"/>',
        attachment: '<path d="m18.5 11.5-7.8 7.8a4 4 0 0 1-5.7-5.7l8.1-8.1a2.75 2.75 0 0 1 3.9 3.9L9 17.4a1.5 1.5 0 0 1-2.1-2.1l7.4-7.4"/>'
    };

    function toolbarIcon(name) {
        return '<svg class="slowcloud-cm-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
            + toolbarIcons[name] + '</svg>';
    }

    function createTimelineDialog() {
        const dialog = document.createElement('dialog');
        dialog.className = 'slowcloud-cm-timeline-dialog';
        dialog.innerHTML = '<form method="dialog"><strong>插入时光轴</strong>'
            + '<button type="submit" value="left">左侧模式</button>'
            + '<button type="submit" value="right">右侧模式</button>'
            + '<button type="submit" value="medium">中间模式</button>'
            + '<button type="submit" value="cancel">取消</button></form>';
        dialog.addEventListener('close', () => {
            const mode = dialog.returnValue;
            if (!['left', 'right', 'medium'].includes(mode)) { view.focus(); return; }
            const templates = {
                left: '[timeline mode="left"]\n[timeline-item color="blue"]\nMarkdown 内容 1\n[/timeline-item]\n[timeline-item color="green"]\nMarkdown 内容 2\n[/timeline-item]\n[timeline-item color="red"]\nMarkdown 内容 3\n[/timeline-item]\n[/timeline]',
                right: '[timeline mode="right"]\n[timeline-item color="blue"]\nMarkdown 内容 1\n[/timeline-item]\n[timeline-item color="green"]\nMarkdown 内容 2\n[/timeline-item]\n[timeline-item color="red"]\nMarkdown 内容 3\n[/timeline-item]\n[/timeline]',
                medium: '[timeline mode="medium"]\n[timeline-item color="blue"]\n[timeline-item-left]\n左侧 Markdown 内容 1\n[/timeline-item-left]\n[timeline-item-right]\n右侧 Markdown 内容 1\n[/timeline-item-right]\n[/timeline-item]\n[timeline-item color="green"]\n[timeline-item-left]\n左侧 Markdown 内容 2\n[/timeline-item-left]\n[timeline-item-right]\n右侧 Markdown 内容 2\n[/timeline-item-right]\n[/timeline-item]\n[timeline-item color="red"]\n[timeline-item-left]\n左侧 Markdown 内容 3\n[/timeline-item-left]\n[timeline-item-right]\n右侧 Markdown 内容 3\n[/timeline-item-right]\n[/timeline-item]\n[/timeline]'
            };
            insert('', '', templates[mode]);
        });
        root.appendChild(dialog);
        return dialog;
    }

    function createHeadingControl() {
        const control = document.createElement('div');
        const trigger = document.createElement('button');
        const menu = document.createElement('div');
        control.className = 'slowcloud-cm-heading-control';
        trigger.type = 'button';
        trigger.className = 'btn btn-xs slowcloud-cm-command';
        trigger.dataset.tooltip = '标题级别';
        trigger.setAttribute('aria-label', '标题级别');
        trigger.innerHTML = toolbarIcon('heading');
        menu.className = 'slowcloud-cm-heading-menu';
        menu.setAttribute('aria-label', '标题级别');

        for (let level = 1; level <= 6; level += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-xs slowcloud-cm-command';
            button.dataset.tooltip = '标题 ' + level;
            button.setAttribute('aria-label', '标题 ' + level);
            button.innerHTML = toolbarIcon('heading' + level);
            button.addEventListener('click', () => applyHeading(level));
            menu.appendChild(button);
        }

        trigger.addEventListener('click', () => applyHeading(2));
        control.appendChild(trigger);
        control.appendChild(menu);
        return control;
    }

    const timelineDialog = createTimelineDialog();
    const commands = [
        ['bold', '加粗', 'bold', () => insert('**', '**', '加粗文字')],
        ['italic', '斜体', 'italic', () => insert('*', '*', '斜体文字')],
        ['strike', '删除线', 'strike', () => insert('~~', '~~', '删除文字')],
        ['link', '链接', 'link', () => insert('[', '](https://)', '链接文字')],
        ['image', '图片', 'image', () => insert('![', '](https://)', '图片描述')],
        ['quote', '引用', 'quote', () => insert('> ', '', '引用文字')],
        ['ul', '无序列表', 'unorderedList', () => insert('- ', '', '列表项目')],
        ['ol', '有序列表', 'orderedList', () => insert('1. ', '', '列表项目')],
        ['code', '代码块', 'codeBlock', () => insert('```\n', '\n```', '代码')],
        ['inline-code', '行内代码', 'inlineCode', () => insert('`', '`', '代码')],
        ['task', '任务列表', 'task', () => insert('- [ ] ', '', '任务项目')],
        ['table', '表格', 'table', () => insert('| 表头 | 表头 |\n| --- | --- |\n| 内容 | 内容 |', '', '')],
        ['hr', '分割线', 'horizontalRule', () => insert('---\n', '', '')],
        ['more', '插入空行', 'lineBreak', () => insert('<br>', '', '')],
        ['timeline', '插入时光轴', 'timeline', () => timelineDialog.showModal()],
        ['undo', '撤销', 'undo', () => { undo(view); view.focus(); }],
        ['redo', '重做', 'redo', () => { redo(view); view.focus(); }],
        ['fullscreen', '分栏全屏', 'splitPane', null]
    ];
    toolbar.appendChild(createHeadingControl());
    commands.forEach(item => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'btn btn-xs slowcloud-cm-command';
        button.dataset.command = item[0]; button.dataset.tooltip = item[1]; button.setAttribute('aria-label', item[1]);
        button.innerHTML = toolbarIcon(item[2]);
        if (item[3]) button.addEventListener('click', item[3]);
        if (item[0] === 'fullscreen') {
            button.addEventListener('click', () => {
                const fullscreen = root.classList.toggle('slowcloud-cm-fullscreen');
                const label = fullscreen ? '退出分栏' : '分栏全屏';
                if (fullscreen) {
                    previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = previousBodyOverflow;
                }
                button.dataset.tooltip = label;
                button.setAttribute('aria-label', label);
                requestAnimationFrame(() => {
                    ensureVisibleLines();
                    requestAnimationFrame(() => alignTimelines(preview));
                });
            });
        }
        toolbar.appendChild(button);
    });
    const upload = document.createElement('button');
    upload.type = 'button'; upload.className = 'btn btn-xs slowcloud-cm-command';
    upload.dataset.tooltip = '附件'; upload.setAttribute('aria-label', '附件');
    upload.innerHTML = toolbarIcon('attachment');
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
        const workspace = root.querySelector('.slowcloud-cm-workspace');
        const move = e => {
            const rect = workspace.getBoundingClientRect();
            const dividerWidth = 6;
            const leftWidth = Math.max(240, Math.min(rect.width - dividerWidth - 240, e.clientX - rect.left));
            workspace.style.gridTemplateColumns = leftWidth + 'px ' + dividerWidth + 'px minmax(240px, 1fr)';
        };
        const stop = () => {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', stop);
            requestAnimationFrame(() => alignTimelines(preview));
        };
        document.addEventListener('mousemove', move); document.addEventListener('mouseup', stop);
    });
    renderPreview(textarea.value);
    updateCount(textarea.value);
    preview.addEventListener('load', () => alignTimelines(preview), true);
    requestAnimationFrame(ensureVisibleLines);
    window.addEventListener('resize', () => {
        ensureVisibleLines();
        alignTimelines(preview);
    });
    return {view, refreshPreview: () => renderPreview(contentForSave(view.state.doc.toString()))};
}
