<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function slowcloud_assign_settings_group(
    \Typecho\Widget\Helper\Form\Element $element,
    string $group,
    string $title
): \Typecho\Widget\Helper\Form\Element {
    $element->setAttribute('data-slowcloud-group', $group);
    $element->setAttribute('data-slowcloud-group-title', $title);

    return $element;
}

function slowcloud_theme_settings_enhancer(): \Typecho\Widget\Helper\Form\Element\Fake
{
    $enhancer = new \Typecho\Widget\Helper\Form\Element\Fake('slowcloud_theme_settings_enhancer', '');
    $enhancer->description(<<<'HTML'
<style>
.typecho-option.slowcloud-settings-enhancer {
    display: none;
}
.slowcloud-settings-group {
    margin: 0 0 18px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
}
.slowcloud-settings-group > summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px;
    cursor: pointer;
    font-weight: 600;
    color: #111827;
    background: #f8fafc;
    user-select: none;
}
.slowcloud-settings-group > summary::-webkit-details-marker {
    display: none;
}
.slowcloud-settings-group > summary::after {
    content: "▾";
    color: #64748b;
    transition: transform .2s ease;
}
.slowcloud-settings-group:not([open]) > summary::after {
    transform: rotate(-90deg);
}
.slowcloud-settings-group-body {
    padding: 4px 18px 16px;
    background: #fff;
}
.slowcloud-settings-group-body > .typecho-option {
    margin-top: 12px;
}
.slowcloud-link-list-builder {
    display: grid;
    gap: 12px;
    margin-top: 10px;
}
.slowcloud-link-list-row {
    display: grid;
    grid-template-columns: minmax(120px, 0.8fr) minmax(180px, 1fr) minmax(220px, 1.4fr) auto;
    gap: 10px;
    align-items: end;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f8fafc;
}
.slowcloud-link-list-fields label {
    display: grid;
    gap: 5px;
    color: #475569;
    font-size: 12px;
}
.slowcloud-link-list-fields {
    display: contents;
}
.slowcloud-link-list-fields input,
.slowcloud-link-list-fields textarea {
    width: 100%;
    box-sizing: border-box;
}
.slowcloud-link-list-fields textarea {
    min-height: 36px;
    height: 36px;
    font-family: Menlo, Consolas, monospace;
    resize: vertical;
}
.slowcloud-link-list-actions {
    display: flex;
    justify-content: flex-start;
}
.slowcloud-link-list-remove {
    white-space: nowrap;
}
.slowcloud-link-list-storage {
    display: none;
}
@media (max-width: 782px) {
    .slowcloud-link-list-row {
        grid-template-columns: 1fr;
        align-items: stretch;
    }
}
</style>
<script>
(function () {
    function splitSlowcloudLinkLine(line) {
        var first = line.indexOf('|');
        var second = first >= 0 ? line.indexOf('|', first + 1) : -1;
        if (first < 0 || second < 0) {
            return null;
        }

        return [
            line.slice(0, first).trim(),
            line.slice(first + 1, second).trim(),
            line.slice(second + 1).trim()
        ];
    }

    function normalizeSlowcloudLinkEntry(name, second, third) {
        var svgInSecond = second.toLowerCase().indexOf('<svg') >= 0;
        return {
            name: name || '',
            url: svgInSecond ? third : second,
            svg: svgInSecond ? second : third
        };
    }

    function parseSlowcloudLegacyLinkList(value) {
        var entries = [];
        var lines = value.split(/\r\n|\r|\n/);

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (!line || line.indexOf('|') < 0) {
                continue;
            }

            var parts = splitSlowcloudLinkLine(line);
            if (parts) {
                entries.push(normalizeSlowcloudLinkEntry(parts[0], parts[1], parts[2]));
                continue;
            }

            var pair = line.split('|');
            var svgLines = [];
            while (i + 1 < lines.length) {
                var nextLine = lines[i + 1].trim();
                if (!nextLine) {
                    i++;
                    if (svgLines.length && svgLines.join('').toLowerCase().indexOf('</svg>') >= 0) {
                        break;
                    }
                    continue;
                }

                if (svgLines.length && nextLine.indexOf('|') >= 0 && svgLines.join('').toLowerCase().indexOf('</svg>') >= 0) {
                    break;
                }

                svgLines.push(nextLine);
                i++;
                if (nextLine.toLowerCase().indexOf('</svg>') >= 0) {
                    break;
                }
            }

            if (pair[0] && pair[1] && svgLines.length) {
                entries.push({
                    name: pair[0].trim(),
                    url: pair.slice(1).join('|').trim(),
                    svg: svgLines.join('')
                });
            }
        }

        return entries;
    }

    function parseSlowcloudLinkListValue(value) {
        value = (value || '').trim();
        if (!value) {
            return [];
        }

        try {
            var parsed = JSON.parse(value);
            if (Array.isArray(parsed)) {
                return parsed.map(function (item) {
                    return {
                        name: item && item.name ? String(item.name) : '',
                        url: item && item.url ? String(item.url) : '',
                        svg: item && item.svg ? String(item.svg) : ''
                    };
                }).filter(function (item) {
                    return item.name || item.url || item.svg;
                });
            }
        } catch (error) {
        }

        return parseSlowcloudLegacyLinkList(value);
    }

    function syncSlowcloudLinkList(storage, builder) {
        var rows = Array.prototype.slice.call(builder.querySelectorAll('.slowcloud-link-list-row'));
        var data = rows.map(function (row) {
            return {
                name: row.querySelector('[data-slowcloud-link-name]').value.trim(),
                url: row.querySelector('[data-slowcloud-link-url]').value.trim(),
                svg: row.querySelector('[data-slowcloud-link-svg]').value.trim()
            };
        }).filter(function (item) {
            return item.name || item.url || item.svg;
        });

        storage.value = data.length ? JSON.stringify(data) : '';
    }

    function createSlowcloudLinkRow(item, storage, builder) {
        var row = document.createElement('div');
        row.className = 'slowcloud-link-list-row';
        row.innerHTML = ''
            + '<div class="slowcloud-link-list-fields">'
            + '<label><span>名称</span><input type="text" class="text" data-slowcloud-link-name></label>'
            + '<label><span>跳转地址</span><input type="text" class="text" data-slowcloud-link-url placeholder="https://example.com 或 /about"></label>'
            + '<label><span>SVG 图标</span><textarea data-slowcloud-link-svg placeholder="<svg viewBox=&quot;0 0 24 24&quot;>...</svg>"></textarea></label>'
            + '</div>'
            + '<button type="button" class="btn btn-xs slowcloud-link-list-remove">删除</button>';

        row.querySelector('[data-slowcloud-link-name]').value = item.name || '';
        row.querySelector('[data-slowcloud-link-url]').value = item.url || '';
        row.querySelector('[data-slowcloud-link-svg]').value = item.svg || '';
        row.querySelector('.slowcloud-link-list-remove').addEventListener('click', function () {
            row.parentNode.removeChild(row);
            syncSlowcloudLinkList(storage, builder);
        });

        Array.prototype.forEach.call(row.querySelectorAll('input, textarea'), function (field) {
            field.addEventListener('input', function () {
                syncSlowcloudLinkList(storage, builder);
            });
        });

        return row;
    }

    function buildSlowcloudLinkLists() {
        var options = Array.prototype.slice.call(document.querySelectorAll('.typecho-option[data-slowcloud-link-list]'));
        options.forEach(function (option) {
            if (option.getAttribute('data-slowcloud-link-list-ready') === '1') {
                return;
            }

            var storageName = option.getAttribute('data-slowcloud-link-list');
            var storage = option.querySelector('textarea[name="' + storageName + '"], input[name="' + storageName + '"]')
                || option.querySelector('textarea, input[type="hidden"]');
            var host = option.querySelector('li') || option;
            if (!storage || !host) {
                return;
            }

            option.setAttribute('data-slowcloud-link-list-ready', '1');
            storage.classList.add('slowcloud-link-list-storage');

            var builder = document.createElement('div');
            builder.className = 'slowcloud-link-list-builder';

            var rows = document.createElement('div');
            rows.className = 'slowcloud-link-list-rows';
            rows.style.display = 'grid';
            rows.style.gap = '12px';
            builder.appendChild(rows);

            var addWrap = document.createElement('div');
            addWrap.className = 'slowcloud-link-list-actions';
            var addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-s';
            addButton.textContent = option.getAttribute('data-slowcloud-link-add-label') || '添加';
            addWrap.appendChild(addButton);
            builder.appendChild(addWrap);

            var entries = parseSlowcloudLinkListValue(storage.value);
            if (!entries.length) {
                entries = [{ name: '', url: '', svg: '' }];
            }

            entries.forEach(function (entry) {
                rows.appendChild(createSlowcloudLinkRow(entry, storage, rows));
            });

            addButton.addEventListener('click', function () {
                rows.appendChild(createSlowcloudLinkRow({ name: '', url: '', svg: '' }, storage, rows));
                syncSlowcloudLinkList(storage, rows);
            });

            host.appendChild(builder);
            syncSlowcloudLinkList(storage, rows);
        });
    }

    function buildSlowcloudThemeSettings() {
        var items = Array.prototype.slice.call(document.querySelectorAll('.typecho-option[data-slowcloud-group]'));
        if (!items.length) {
            return;
        }

        var processed = {};

        items.forEach(function (item) {
            var group = item.getAttribute('data-slowcloud-group');
            if (!group || processed[group]) {
                return;
            }

            var groupItems = items.filter(function (current) {
                return current.getAttribute('data-slowcloud-group') === group;
            });

            if (!groupItems.length) {
                return;
            }

            processed[group] = true;

            var title = groupItems[0].getAttribute('data-slowcloud-group-title') || '设置分组';
            var details = document.createElement('details');
            details.className = 'slowcloud-settings-group';
            details.open = true;

            var summary = document.createElement('summary');
            summary.textContent = title;

            var body = document.createElement('div');
            body.className = 'slowcloud-settings-group-body';

            var anchor = groupItems[0];
            anchor.parentNode.insertBefore(details, anchor);
            details.appendChild(summary);
            details.appendChild(body);

            groupItems.forEach(function (groupItem) {
                body.appendChild(groupItem);
            });
        });

        var enhancer = document.getElementById('typecho-option-item-slowcloud_theme_settings_enhancer-0');
        if (enhancer) {
            enhancer.classList.add('slowcloud-settings-enhancer');
        }

        buildSlowcloudLinkLists();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildSlowcloudThemeSettings);
    } else {
        buildSlowcloudThemeSettings();
    }
})();
</script>
HTML);

    return $enhancer;
}

function slowcloud_theme_asset_url(string $path, $archive = null): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return \Typecho\Common::url(ltrim($path, '/'), (string) ($options->siteUrl ?? ''));
}

function slowcloud_theme_file_version(string $path): string
{
    $relativePath = ltrim($path, '/');
    $themePrefix = 'usr/themes/slowcloud/';

    if (strpos($relativePath, $themePrefix) === 0) {
        $relativePath = substr($relativePath, strlen($themePrefix));
    }

    $file = __DIR__ . '/' . $relativePath;
    return is_file($file) ? (string) filemtime($file) : '1.0.0';
}

function slowcloud_theme_versioned_theme_url(string $path, $archive = null): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $url = (string) $options->themeUrl(ltrim($path, '/'), 'slowcloud');
    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . 'v=' . rawurlencode(slowcloud_theme_file_version($path));
}

function slowcloud_theme_asset_cdn_url(string $path, $archive = null): string
{
    return slowcloud_rewrite_cdn_url($archive, slowcloud_theme_asset_url($path, $archive));
}

function slowcloud_owo_config(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $config = [];
    $file = __DIR__ . '/assets/json/slowcloud.owo.json';

    if (!is_file($file)) {
        return $config;
    }

    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return $config;
    }

    $groups = [
        '泡泡' => 'paopao',
        '阿鲁' => 'aru',
        '颜文字' => 'kaomoji',
    ];

    foreach ($groups as $label => $key) {
        if (empty($data[$label]) || !is_array($data[$label])) {
            continue;
        }

        $items = [];
        foreach ($data[$label] as $item) {
            if (!is_array($item) || !isset($item['data'])) {
                continue;
            }

            $value = (string) $item['data'];
            $icon = isset($item['icon']) ? (string) $item['icon'] : '';
            $name = isset($item['text']) ? (string) $item['text'] : $value;

            if (preg_match('/^:sc[pa]\((.*)\)$/u', $value, $matches)) {
                $name = $matches[1];
            }

            $items[] = [
                'name' => $name,
                'value' => $value,
                'icon' => $icon,
                'image' => $icon !== '' && preg_match('/^assets\/owo\//', $icon) === 1,
            ];
        }

        if ($items) {
            $config[$key] = [
                'label' => $label,
                'items' => $items,
            ];
        }
    }

    return $config;
}

function slowcloud_comment_emoji_groups($archive = null): array
{
    $groups = [];

    $owoGroups = slowcloud_owo_config();

    foreach (['paopao', 'aru'] as $key) {
        if (empty($owoGroups[$key]['items'])) {
            continue;
        }

        $groups[$key] = $owoGroups[$key];
        foreach ($groups[$key]['items'] as &$item) {
            if (!empty($item['image'])) {
                $item['url'] = slowcloud_theme_asset_cdn_url('usr/themes/slowcloud/' . ltrim((string) $item['icon'], '/'), $archive);
            }
        }
        unset($item);
    }

    $kaomojiItems = $owoGroups['kaomoji']['items'] ?? [];
    if (!$kaomojiItems) {
        $kaomojiItems = array_map(static function (string $kaomoji): array {
            return [
                'name' => $kaomoji,
                'value' => $kaomoji,
                'icon' => '',
                'image' => false,
            ];
        }, ['|´・ω・)ノ', 'ヾ(≧▽≦*)o', '(*/ω＼*)', '(๑•̀ㅂ•́)و✧', '(╯°□°）╯︵ ┻━┻', 'φ(゜▽゜*)♪', '(～￣▽￣)～', 'Σ(っ °Д °;)っ', 'QAQ', '( •̀ ω •́ )✧', '(❁´◡`❁)', '(っ °Д °;)っ']);
    }

    $groups['kaomoji'] = [
        'label' => '颜文字',
        'items' => $kaomojiItems,
    ];

    $groups['emoji'] = [
        'label' => 'emoji',
        'items' => array_map(static function (string $emoji): array {
            return [
                'name' => $emoji,
                'value' => $emoji,
                'icon' => '',
                'image' => false,
            ];
        }, ['😀', '😆', '🥹', '😂', '🥰', '🤔', '😌', '😴', '🙌', '✨', '☁️', '🌙', '🍵', '🌿', '🎈', '💭']),
    ];

    return $groups;
}

function slowcloud_replace_owo_shortcodes($archive, string $html): string
{
    if ($html === '' || (strpos($html, ':scp(') === false && strpos($html, ':sca(') === false)) {
        return $html;
    }

    $config = slowcloud_owo_config();
    $pairs = [
        'paopao' => '\:scp',
        'aru' => '\:sca',
    ];

    foreach ($pairs as $key => $prefix) {
        if (empty($config[$key]['items'])) {
            continue;
        }

        $map = [];
        foreach ($config[$key]['items'] as $item) {
            if (empty($item['image']) || empty($item['icon'])) {
                continue;
            }

            $map[(string) $item['name']] = [
                'url' => slowcloud_theme_asset_cdn_url('usr/themes/slowcloud/' . ltrim((string) $item['icon'], '/'), $archive),
                'alt' => (string) $item['name'],
            ];
        }

        if (!$map) {
            continue;
        }

        $pattern = '/' . $prefix . '\(\s*(' . implode('|', array_map(static function (string $name): string {
            return preg_quote($name, '/');
        }, array_keys($map))) . ')\s*\)/u';

        $html = (string) preg_replace_callback($pattern, static function (array $matches) use ($map): string {
            $item = $map[$matches[1]] ?? null;
            if (!$item) {
                return $matches[0];
            }

            return '<img class="slowcloud-owo-image owo_image" src="'
                . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8')
                . '" alt="'
                . htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8')
                . '" loading="lazy">';
        }, $html);
    }

    return $html;
}

function slowcloud_register_admin_editor_enhance(): void
{
    static $registered = false;

    if ($registered || !defined('__TYPECHO_ADMIN__')) {
        return;
    }

    $registered = true;

    \Typecho\Plugin::factory('admin/editor-js.php')->markdownEditor = 'slowcloud_render_admin_editor_enhance';
}

function slowcloud_force_admin_markdown_editor(): void
{
    if (!defined('__TYPECHO_ADMIN__')) {
        return;
    }

    $options = \Widget\Options::alloc();
    $options->markdown = true;
    slowcloud_register_admin_editor_enhance();
}

function slowcloud_admin_draft_field_values($content): array
{
    $draft = $content->draft ?? null;
    $cid = is_array($draft) ? (int) ($draft['cid'] ?? 0) : 0;

    if ($cid <= 0) {
        return [];
    }

    $db = \Typecho\Db::get();
    $rows = $db->fetchAll($db->select()->from('table.fields')->where('cid = ?', $cid));
    $fields = [];

    foreach ($rows as $row) {
        $name = (string) ($row['name'] ?? '');
        $type = (string) ($row['type'] ?? 'str');

        if ($name === '') {
            continue;
        }

        $fields[$name] = $type === 'json'
            ? json_decode((string) ($row['str_value'] ?? ''), true)
            : (string) ($row[$type . '_value'] ?? '');
    }

    return $fields;
}

function slowcloud_render_admin_editor_enhance($content): void
{
    $options = \Widget\Options::alloc();
    $scriptFile = $options->themeFile('slowcloud', 'assets/typecho/editor-enhance.js');
    $prismComponentsUrl = rtrim((string) $options->themeUrl('assets/typecho/prism/components', 'slowcloud'), '/') . '/';
    $themeMode = (string) ($options->themeMode ?? 'system');
    ?>
	    (function () {
		        [
		            <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/css/content-render.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
		            <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/css/code-highlight.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
		            <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/editor-enhance.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
		        ].forEach(function (href) {
	            var link = document.createElement('link');
	            link.rel = 'stylesheet';
	            link.href = href;
	            document.head.appendChild(link);
	        });
	    })();
    window.SlowcloudEditorEnhance = {
        labels: {
            heading: <?php echo json_encode(_t('标题级别'), JSON_UNESCAPED_UNICODE); ?>,
            body: <?php echo json_encode(_t('正文'), JSON_UNESCAPED_UNICODE); ?>,
            placeholder: <?php echo json_encode(_t('标题文字'), JSON_UNESCAPED_UNICODE); ?>,
            toc: <?php echo json_encode(_t('文章目录'), JSON_UNESCAPED_UNICODE); ?>
        },
        themeMode: <?php echo json_encode($themeMode, JSON_UNESCAPED_UNICODE); ?>,
        fieldValues: <?php echo json_encode(slowcloud_admin_draft_field_values($content), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
        prism: {
            core: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/prism.js'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            autoloader: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/plugins/autoloader/prism-autoloader.js'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            components: <?php echo json_encode($prismComponentsUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            lineNumbersScript: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/plugins/line-numbers/prism-line-numbers.js'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            lineNumbersStyle: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/plugins/line-numbers/prism-line-numbers.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            coyStyle: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/themes/prism-coy.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            okaidiaStyle: <?php echo json_encode(slowcloud_theme_versioned_theme_url('assets/typecho/prism/themes/prism-okaidia.css'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
        },
        editor: editor
    };
    <?php if (is_file($scriptFile)) {
        echo file_get_contents($scriptFile);
    } ?>
    <?php
}

function themeConfig($form)
{
    $options = \Widget\Options::alloc();

    $basicLayout = new \Typecho\Widget\Helper\Form\Element\Radio(
        'basicLayout',
        [
            'default' => _t('默认选项'),
            'classic' => _t('三栏经典布局'),
        ],
        'default',
        _t('基本布局'),
        _t('默认选项：Slowcloud 默认布局；三栏经典布局：固定导航栏，三栏独立滚动。')
    );
    $form->addInput(slowcloud_assign_settings_group($basicLayout, 'basic-layout', '基本布局'));

    $tabTitle = new \Typecho\Widget\Helper\Form\Element\Text(
        'tabTitle',
        null,
        'slowcloud',
        _t('浏览器 Tab 文字'),
        _t('用于浏览器标签页显示的文字，不填写时默认使用站点标题')
    );
    $form->addInput(slowcloud_assign_settings_group($tabTitle, 'browser-tab', '浏览器 Tab 设置'));

    $logoUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        \Typecho\Common::url('usr/themes/slowcloud/assets/img/head.png', (string) $options->siteUrl),
        _t('网站 Logo'),
        _t('填写图片 URL 后，主题头部将显示 Logo')
    );
    $logoUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($logoUrl, 'browser-tab', '浏览器 Tab 设置'));

    $headerBackgroundUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerBackgroundUrl',
        null,
        \Typecho\Common::url('usr/themes/slowcloud/assets/img/xiyang5.png', (string) $options->siteUrl),
        _t('Header 背景图'),
        _t('填写图片 URL 后，站点头部将使用这张图片作为横幅背景')
    );
    $headerBackgroundUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($headerBackgroundUrl, 'header-display', 'Header 展示设置'));

    $headerHeight = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerHeight',
        null,
        '120px',
        _t('Header 高度'),
        _t('支持 CSS 高度值，例如 120px、720px、80vh')
    );
    $form->addInput(slowcloud_assign_settings_group($headerHeight, 'header-display', 'Header 展示设置'));

    $headerMenuMode = new \Typecho\Widget\Helper\Form\Element\Radio(
        'headerMenuMode',
        [
            'combined' => _t('组合导航'),
        ],
        'combined',
        _t('Header 导航模式'),
        _t('自动菜单与自定义菜单会同时显示，顺序由下方“导航显示顺序”控制。')
    );
    $form->addInput(slowcloud_assign_settings_group($headerMenuMode, 'header-menu', 'Header 导航设置'));

    $headerAutoMenuSources = new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'headerAutoMenuSources',
        [
            'home' => _t('首页'),
            'timeline' => _t('时光轴'),
            'categories' => _t('分类'),
            'friend-links' => _t('友链'),
            'social-links' => _t('社交平台'),
            'latest-posts' => _t('最新文章'),
            'pages' => _t('独立页面'),
        ],
        ['home', 'timeline', 'categories', 'friend-links', 'social-links', 'latest-posts', 'pages'],
        _t('自动菜单内容'),
        _t('分类、友链、社交平台和最新文章会自动作为带子项的菜单；数据分别读取站点分类、友链和社交平台设置、最新发布文章。')
    );
    $form->addInput(slowcloud_assign_settings_group($headerAutoMenuSources, 'header-menu', 'Header 导航设置'));

    $headerLatestPostCount = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerLatestPostCount',
        null,
        '5',
        _t('最新文章数量'),
        _t('自动菜单启用“最新文章”时显示的文章数，范围为 1 到 20。')
    );
    $headerLatestPostCount->addRule('isInteger', _t('请填写整数'));
    $form->addInput(slowcloud_assign_settings_group($headerLatestPostCount, 'header-menu', 'Header 导航设置'));

    $headerMenuOrder = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerMenuOrder',
        null,
        'home,timeline,categories,friend-links,social-links,latest-posts,pages,custom',
        _t('导航显示顺序'),
        _t('以逗号分隔：home（首页）、timeline（时光轴）、categories（分类）、friend-links（友链）、social-links（社交平台）、latest-posts（最新文章）、pages（独立页面）、custom（自定义菜单）。未写的已启用自动项会排在最后。')
    );
    $form->addInput(slowcloud_assign_settings_group($headerMenuOrder, 'header-menu', 'Header 导航设置'));

    $headerCustomMenu = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'headerCustomMenu',
        null,
        '',
        _t('自定义菜单项'),
        _t('每行一项，格式为 菜单名称|链接；子项使用 父菜单 > 子菜单|链接。例如：关于|/about 和 资源 > GitHub|https://github.com/your-name。')
    );
    $form->addInput(slowcloud_assign_settings_group($headerCustomMenu, 'header-menu', 'Header 导航设置'));

    $siteWidth = new \Typecho\Widget\Helper\Form\Element\Text(
        'siteWidth',
        null,
        '1200px',
        _t('站点主体宽度'),
        _t('控制 header、正文和页脚的内容宽度，例如 1200px、90vw、72rem')
    );
    $form->addInput(slowcloud_assign_settings_group($siteWidth, 'layout-options', '布局设置'));

    $mainBackground = new \Typecho\Widget\Helper\Form\Element\Text(
        'mainBackground',
        null,
        'transparent',
        _t('主体背景色'),
        _t('控制 header 下方主体区域背景色，例如 #ffffff、#f7f7f7、rgba(255,255,255,.72)')
    );
    $form->addInput(slowcloud_assign_settings_group($mainBackground, 'layout-options', '布局设置'));

    $leftColumnBg = new \Typecho\Widget\Helper\Form\Element\Text(
        'leftColumnBg',
        null,
        'rgba(255, 255, 255, 0.82)',
        _t('左栏背景色'),
        _t('首页左侧栏背景色，例如 #ffffff、rgba(255,255,255,.82)')
    );
    $form->addInput(slowcloud_assign_settings_group($leftColumnBg, 'layout-options', '布局设置'));

    $centerColumnBg = new \Typecho\Widget\Helper\Form\Element\Text(
        'centerColumnBg',
        null,
        'rgba(255, 255, 255, 0.62)',
        _t('中栏背景色'),
        _t('首页中间栏背景色，例如 #f8f8f8、rgba(255,255,255,.62)')
    );
    $form->addInput(slowcloud_assign_settings_group($centerColumnBg, 'layout-options', '布局设置'));

    $rightColumnBg = new \Typecho\Widget\Helper\Form\Element\Text(
        'rightColumnBg',
        null,
        'rgba(255, 255, 255, 0.74)',
        _t('右栏背景色'),
        _t('首页右侧栏背景色，例如 #fafafa、rgba(255,255,255,.74)')
    );
    $form->addInput(slowcloud_assign_settings_group($rightColumnBg, 'layout-options', '布局设置'));

    $introText = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'introText',
        null,
        _t('一朵慢慢飘动的云，记录轻盈又真实的日常。'),
        _t('首页简介'),
        _t('显示在首页头图和站点副标题位置')
    );
    $form->addInput(slowcloud_assign_settings_group($introText, 'site-copy', '站点文案设置'));

    $authorAvatar = new \Typecho\Widget\Helper\Form\Element\Text(
        'authorAvatar',
        null,
        \Typecho\Common::url('usr/themes/slowcloud/assets/img/avatar.jpg', (string) $options->siteUrl),
        _t('博主头像'),
        _t('填写图片 URL，用于内容区左侧信息栏头像')
    );
    $authorAvatar->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($authorAvatar, 'author-card', '博主信息栏'));

    $authorName = new \Typecho\Widget\Helper\Form\Element\Text(
        'authorName',
        null,
        'slowcloud',
        _t('博主名称'),
        _t('用于内容区左侧信息栏名称，不填写时默认使用站点标题')
    );
    $form->addInput(slowcloud_assign_settings_group($authorName, 'author-card', '博主信息栏'));

    $authorBio = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'authorBio',
        null,
        _t('一朵慢慢飘动的云，记录轻盈又真实的日常。'),
        _t('博主描述'),
        _t('用于内容区左侧信息栏描述，不填写时默认使用首页简介')
    );
    $form->addInput(slowcloud_assign_settings_group($authorBio, 'author-card', '博主信息栏'));

    $githubUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'githubUrl',
        null,
        'https://github.com',
        _t('GitHub 地址'),
        _t('填写后会显示在左侧作者区域，例如 https://github.com/your-name')
    );
    $githubUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($githubUrl, 'author-card', '博主信息栏'));

    $bilibiliUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'bilibiliUrl',
        null,
        'https://bilibili.com',
        _t('Bilibili 地址'),
        _t('填写后会显示在左侧作者区域，例如 https://space.bilibili.com/123456')
    );
    $bilibiliUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($bilibiliUrl, 'author-card', '博主信息栏'));

    $customSocialLinks = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'customSocialLinks',
        null,
        '',
        _t('自定义入口'),
        _t('支持添加多条自定义入口，展示在“其他平台”上方。每条包含名称、SVG 图标和跳转地址。')
    );
    $customSocialLinks->setAttribute('data-slowcloud-link-list', 'customSocialLinks');
    $customSocialLinks->setAttribute('data-slowcloud-link-add-label', _t('添加入口'));
    $form->addInput(slowcloud_assign_settings_group($customSocialLinks, 'author-card', '博主信息栏'));

    $customPlatformLinks = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'customPlatformLinks',
        null,
        '',
        _t('自定义平台'),
        _t('支持添加多条自定义平台，展示在“社交平台”中。每条包含名称、SVG 图标和跳转地址。')
    );
    $customPlatformLinks->setAttribute('data-slowcloud-link-list', 'customPlatformLinks');
    $customPlatformLinks->setAttribute('data-slowcloud-link-add-label', _t('添加平台'));
    $form->addInput(slowcloud_assign_settings_group($customPlatformLinks, 'author-card', '博主信息栏'));

    $friendLinks = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'friendLinks',
        null,
        "派大星|https://baidu.com\n海绵宝宝|https://baidu.com\n蟹老板|https://baidu.com\n泡芙阿姨|https://baidu.com",
        _t('友链列表'),
        _t('每行一条，格式为 名称|https://example.com ，例如 OpenAI|https://openai.com')
    );
    $form->addInput(slowcloud_assign_settings_group($friendLinks, 'author-card', '博主信息栏'));

    $showSidebar = new \Typecho\Widget\Helper\Form\Element\Radio(
        'showSidebar',
        [
            '1' => _t('显示'),
            '0' => _t('隐藏'),
        ],
        '1',
        _t('侧边栏'),
        _t('控制首页和文章页是否显示侧边信息')
    );
    $form->addInput(slowcloud_assign_settings_group($showSidebar, 'layout-options', '布局设置'));

    $themeMode = new \Typecho\Widget\Helper\Form\Element\Radio(
        'themeMode',
        [
            'light' => _t('默认白天'),
            'dark' => _t('默认黑夜'),
            'system' => _t('跟随系统'),
        ],
        'system',
        _t('默认主题模式'),
        _t('用户未手动切换主题时，站点默认使用的主题模式')
    );
    $form->addInput(slowcloud_assign_settings_group($themeMode, 'layout-options', '布局设置'));

    $uploadCdnUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'uploadCdnUrl',
        null,
        null,
        _t('上传图片 CDN 地址'),
        _t('填写 CDN 域名根地址，例如 https://cdn.example.com；保存后会改写文章正文、摘要和海报图中的 usr/uploads 图片地址，也会改写 slowcloud 主题表情图片地址，请确保 CDN 已正确回源到站点根目录。')
    );
    $uploadCdnUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($uploadCdnUrl, 'content-delivery', '内容分发设置'));

    $sitemapEnabled = new \Typecho\Widget\Helper\Form\Element\Radio(
        'sitemapEnabled',
        [
            '1' => _t('启用'),
            '0' => _t('关闭'),
        ],
        '1',
        _t('Sitemap'),
        _t('启用后可通过 /sitemap.xml 或 /index.php/sitemap.xml 访问站点地图，包含首页、文章页和独立页面。')
    );
    $form->addInput(slowcloud_assign_settings_group($sitemapEnabled, 'seo-options', 'SEO 设置'));

    $icpBeian = new \Typecho\Widget\Helper\Form\Element\Text(
        'icpBeian',
        null,
        null,
        _t('ICP备案号'),
        _t('例如 京ICP备12345678号，仅填写展示文本即可')
    );
    $form->addInput(slowcloud_assign_settings_group($icpBeian, 'site-compliance', '备案信息设置'));

    $icpBeianUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'icpBeianUrl',
        null,
        'https://beian.miit.gov.cn/',
        _t('ICP备案链接'),
        _t('可选，填写备案跳转地址；不填写时默认跳转到工信部备案管理系统')
    );
    $form->addInput(slowcloud_assign_settings_group($icpBeianUrl, 'site-compliance', '备案信息设置'));

    $publicSecurityBeian = new \Typecho\Widget\Helper\Form\Element\Text(
        'publicSecurityBeian',
        null,
        null,
        _t('公安联网备案号'),
        _t('例如 京公网安备11000002000001号，仅填写展示文本即可')
    );
    $form->addInput(slowcloud_assign_settings_group($publicSecurityBeian, 'site-compliance', '备案信息设置'));

    $publicSecurityBeianUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'publicSecurityBeianUrl',
        null,
        'https://beian.mps.gov.cn/',
        _t('公安联网备案链接'),
        _t('可选，填写备案跳转地址；不填写时默认跳转到全国互联网安全管理服务平台')
    );
    $form->addInput(slowcloud_assign_settings_group($publicSecurityBeianUrl, 'site-compliance', '备案信息设置'));

    $form->addInput(slowcloud_theme_settings_enhancer());
}

function themeConfigHandle($settings, $isInit): void
{
    $options = \Widget\Options::alloc();
    $theme = (string) ($options->theme ?? 'slowcloud');
    $db = \Typecho\Db::get();
    $value = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($value === false) {
        $value = json_encode([]);
    }

    $existing = $db->fetchRow(
        $db->select('name')
            ->from('table.options')
            ->where('name = ?', 'theme:' . $theme)
            ->where('user = ?', 0)
            ->limit(1)
    );

    if ($existing) {
        $db->query(
            $db->update('table.options')
                ->rows(['value' => $value])
                ->where('name = ?', 'theme:' . $theme)
                ->where('user = ?', 0)
        );
    } else {
        $db->query(
            $db->insert('table.options')->rows([
                'name'  => 'theme:' . $theme,
                'user'  => 0,
                'value' => $value,
            ])
        );
    }

    try {
        slowcloud_ensure_stats_storage();
    } catch (\Throwable $e) {
    }
}

function themeInit($archive): void
{
    if (slowcloud_is_stats_track_request($archive)) {
        slowcloud_handle_stats_track_request($archive);
        return;
    }

    if (slowcloud_is_robots_request($archive)) {
        $archive->response->setStatus(200);
        $archive->setThemeFile('robots.php');
        return;
    }

    if (!slowcloud_sitemap_enabled($archive) || !slowcloud_is_sitemap_request($archive)) {
        return;
    }

    $archive->response->setStatus(200);
    $archive->setThemeFile('sitemap.php');
}

function slowcloud_is_stats_track_request($archive): bool
{
    $request = $archive->request ?? \Widget\Options::alloc()->request;
    return trim((string) $request->get('slowcloud_stats_track', '')) === '1';
}

function slowcloud_handle_stats_track_request($archive): void
{
    $request = $archive->request ?? \Widget\Options::alloc()->request;
    $ok = false;

    try {
        $requestMethod = strtoupper((string) $request->getServer('REQUEST_METHOD', 'GET'));
        $ok = $requestMethod === 'POST' && slowcloud_stats_storage_ready();

        if ($ok) {
            $ok = slowcloud_track_site_visit_from_request($request);
        }
    } catch (\Throwable $e) {
        $ok = false;
    }

    $archive->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    $archive->response->throwJson(['ok' => $ok]);
}

function slowcloud_sitemap_enabled($archive): bool
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return (string) ($options->sitemapEnabled ?? '1') !== '0';
}

function slowcloud_is_sitemap_request($archive): bool
{
    if (!is_object($archive) || !isset($archive->request) || !method_exists($archive->request, 'getRequestUri')) {
        return false;
    }

    $path = parse_url((string) $archive->request->getRequestUri(), PHP_URL_PATH);
    if (!is_string($path)) {
        return false;
    }

    return preg_match('#/(?:index\.php/)?sitemap\.xml/?$#i', $path) === 1;
}

function slowcloud_is_robots_request($archive): bool
{
    if (!is_object($archive) || !isset($archive->request) || !method_exists($archive->request, 'getRequestUri')) {
        return false;
    }

    $path = parse_url((string) $archive->request->getRequestUri(), PHP_URL_PATH);
    if (!is_string($path)) {
        return false;
    }

    return preg_match('#/(?:index\.php/)?robots\.txt/?$#i', $path) === 1;
}

function slowcloud_sitemap_xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function slowcloud_sitemap_lastmod($value): string
{
    $timestamp = (int) $value;
    return $timestamp > 0 ? gmdate('c', $timestamp) : gmdate('c');
}

function slowcloud_sitemap_entries($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $homeLastmod = 0;
    $contentEntries = [];

    \Widget\Contents\Page\Rows::alloc()->to($pages);
    while ($pages->next()) {
        $modified = (int) ($pages->modified ?? $pages->created ?? 0);
        $homeLastmod = max($homeLastmod, $modified);
        $contentEntries[] = [
            'loc' => slowcloud_seo_normalize_site_url($archive, (string) $pages->permalink),
            'lastmod' => slowcloud_sitemap_lastmod($modified),
            'changefreq' => 'monthly',
            'priority' => '0.8',
        ];
    }

    \Widget\Contents\Post\Recent::alloc(['pageSize' => 10000])->to($posts);
    while ($posts->next()) {
        $modified = (int) ($posts->modified ?? $posts->created ?? 0);
        $homeLastmod = max($homeLastmod, $modified);
        $entry = [
            'loc' => slowcloud_seo_normalize_site_url($archive, (string) $posts->permalink),
            'lastmod' => slowcloud_sitemap_lastmod($modified),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];

        $poster = slowcloud_poster($posts);
        if ($poster !== '') {
            $entry['image'] = [
                'loc' => slowcloud_seo_absolute_url($archive, $poster),
                'title' => (string) $posts->title,
                'caption' => slowcloud_poster_alt($posts),
            ];
        }

        $contentEntries[] = $entry;
    }

    $entries = [
        [
            'loc' => slowcloud_seo_absolute_url($archive, (string) ($options->siteUrl ?? '')),
            'lastmod' => slowcloud_sitemap_lastmod($homeLastmod),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ],
    ];

    return array_merge($entries, $contentEntries);
}

function slowcloud_render_sitemap($archive): void
{
    if (!headers_sent()) {
        header('Content-Type: application/xml; charset=UTF-8');
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    foreach (slowcloud_sitemap_entries($archive) as $entry) {
        echo "  <url>\n";
        echo '    <loc>' . slowcloud_sitemap_xml_escape((string) $entry['loc']) . "</loc>\n";
        echo '    <lastmod>' . slowcloud_sitemap_xml_escape((string) $entry['lastmod']) . "</lastmod>\n";
        echo '    <changefreq>' . slowcloud_sitemap_xml_escape((string) $entry['changefreq']) . "</changefreq>\n";
        echo '    <priority>' . slowcloud_sitemap_xml_escape((string) $entry['priority']) . "</priority>\n";

        if (!empty($entry['image']['loc'])) {
            echo "    <image:image>\n";
            echo '      <image:loc>' . slowcloud_sitemap_xml_escape((string) $entry['image']['loc']) . "</image:loc>\n";
            if (!empty($entry['image']['title'])) {
                echo '      <image:title>' . slowcloud_sitemap_xml_escape((string) $entry['image']['title']) . "</image:title>\n";
            }
            if (!empty($entry['image']['caption'])) {
                echo '      <image:caption>' . slowcloud_sitemap_xml_escape((string) $entry['image']['caption']) . "</image:caption>\n";
            }
            echo "    </image:image>\n";
        }

        echo "  </url>\n";
    }

    echo "</urlset>\n";
}

function slowcloud_render_robots($archive): void
{
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }

    $options = $archive->options ?? \Widget\Options::alloc();
    $sitemapUrl = slowcloud_seo_normalize_site_url($archive, \Typecho\Common::url('sitemap.xml', (string) ($options->index ?? $options->siteUrl ?? '')));

    echo "User-agent: *\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /install/\n";
    echo "Disallow: /var/\n";
    echo "Disallow: /usr/\n";
    echo "Disallow: /action/\n";
    echo "Disallow: /search/\n";
    echo "Allow: /usr/uploads/\n";
    echo "Allow: /usr/themes/slowcloud/assets/\n";
    if (slowcloud_sitemap_enabled($archive)) {
        echo "\nSitemap: " . $sitemapUrl . "\n";
    }
}

function postMeta(\Widget\Archive $archive, string $metaType = 'archive')
{
    $titleTag = $metaType === 'archive' ? 'h2' : 'h1';
    $charset = $archive->options->charset ?? \Widget\Options::alloc()->charset;
    ?>
    <header class="slowcloud-article-header">
        <<?php echo $titleTag; ?> class="slowcloud-article-title" itemprop="name headline">
            <?php if ($metaType === 'archive'): ?>
                <a href="<?php $archive->permalink(); ?>" itemprop="url"><?php $archive->title(); ?></a>
            <?php else: ?>
                <?php $archive->title(); ?>
            <?php endif; ?>
        </<?php echo $titleTag; ?>>

        <?php if ($metaType !== 'page'): ?>
            <p class="slowcloud-article-meta">
                <span class="slowcloud-meta-item">
                    <span class="slowcloud-meta-icon iconfont icon-slowcloudadmin" aria-hidden="true"></span>
                    <span><?php $archive->author(); ?></span>
                </span>
                <span class="slowcloud-meta-item">
                    <span class="slowcloud-meta-icon iconfont icon-slowcloudriqi2" aria-hidden="true"></span>
                    <time datetime="<?php $archive->date('c'); ?>" itemprop="datePublished"><?php $archive->date('Y-m-d'); ?></time>
                </span>
                <span class="slowcloud-meta-item">
                    <span class="slowcloud-meta-icon iconfont icon-slowcloudview" aria-hidden="true"></span>
                    <span><?php echo htmlspecialchars(slowcloud_views_text($archive), ENT_QUOTES, $charset); ?></span>
                </span>
                <span class="slowcloud-meta-item">
                    <span class="slowcloud-meta-icon iconfont icon-slowcloudcommet" aria-hidden="true"></span>
                    <span><?php $archive->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></span>
                </span>
            </p>
        <?php endif; ?>
    </header>
    <?php
}

function themeFields($layout)
{
    slowcloud_force_admin_markdown_editor();

    $poster = new \Typecho\Widget\Helper\Form\Element\Text(
        'poster',
        null,
        null,
        _t('文章海报'),
        _t('填写图片 URL，用于文章卡片和文章详情页顶部展示')
    );
    $layout->addItem($poster->addRule('url', _t('请填写正确的 URL 地址')));

    $posterAlt = new \Typecho\Widget\Helper\Form\Element\Text(
        'posterAlt',
        null,
        null,
        _t('文章海报 Alt 文本'),
        _t('可选。用于文章封面图片的 alt、结构化数据和图片 SEO；不填写时使用文章标题。')
    );
    $layout->addItem($posterAlt);

    $posterListStyle = new \Typecho\Widget\Helper\Form\Element\Radio(
        'posterListStyle',
        [
            'standard' => _t('默认海报'),
            'media' => _t('横向媒体'),
            'cover' => _t('沉浸封面'),
        ],
        'standard',
        _t('列表海报样式'),
        _t('仅文章海报不为空时生效。无海报文章会保持普通文本卡片。')
    );
    $layout->addItem($posterListStyle);

    $showToc = new \Typecho\Widget\Helper\Form\Element\Radio(
        'showToc',
        [
            '0' => _t('不展示'),
            '1' => _t('展示'),
        ],
        '0',
        _t('文章目录'),
        _t('默认不展示。开启后会根据正文中的二级到四级标题自动生成文章目录。')
    );
    $layout->addItem($showToc);

    $seoTitle = new \Typecho\Widget\Helper\Form\Element\Text(
        'seoTitle',
        null,
        null,
        _t('SEO 标题'),
        _t('可选。用于 title、Open Graph 和结构化数据 headline；不填写时使用文章标题。')
    );
    $layout->addItem($seoTitle);

    $seoDescription = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'seoDescription',
        null,
        null,
        _t('SEO 描述'),
        _t('可选。建议 80-160 字，用于 description、Open Graph 和结构化数据；不填写时使用文章摘要。')
    );
    $layout->addItem($seoDescription);

    $seoCanonical = new \Typecho\Widget\Helper\Form\Element\Text(
        'seoCanonical',
        null,
        null,
        _t('Canonical 地址'),
        _t('可选。仅在需要指定规范 URL 时填写，留空时自动使用当前文章永久链接。')
    );
    $layout->addItem($seoCanonical->addRule('url', _t('请填写正确的 URL 地址')));

    $seoNoindex = new \Typecho\Widget\Helper\Form\Element\Radio(
        'seoNoindex',
        [
            '0' => _t('允许收录'),
            '1' => _t('不收录'),
        ],
        '0',
        _t('搜索引擎收录'),
        _t('选择“不收录”时会输出 noindex,follow，适合临时页或不希望进入搜索结果的内容。')
    );
    $layout->addItem($seoNoindex);
}

function slowcloud_intro($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();

    $intro = trim((string) ($options->introText ?? ''));
    if ($intro !== '') {
        return $intro;
    }

    return (string) ($options->description ?? '');
}

function slowcloud_poster($archive): string
{
    $poster = slowcloud_field_value($archive, 'poster');
    if ($poster !== '') {
        return slowcloud_rewrite_upload_url($archive, $poster);
    }

    return '';
}

function slowcloud_field_value($archive, string $name): string
{
    $previewValue = slowcloud_preview_field_value($archive, $name);
    if ($previewValue !== null) {
        return $previewValue;
    }

    $value = trim((string) ($archive->fields->{$name} ?? ''));
    if ($value !== '') {
        return $value;
    }

    $requestFields = \Typecho\Request::getInstance()->getArray('fields');
    return is_array($requestFields) && isset($requestFields[$name])
        ? trim((string) $requestFields[$name])
        : '';
}

function slowcloud_preview_field_value($archive, string $name): ?string
{
    $parameter = $archive->parameter ?? null;
    if (empty($parameter) || empty($parameter->preview) || (string) ($archive->type ?? '') !== 'revision') {
        return null;
    }

    $cid = (int) ($archive->cid ?? 0);
    if ($cid <= 0) {
        return null;
    }

    static $cache = [];

    if (!isset($cache[$cid])) {
        $fields = [];
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll($db->select()->from('table.fields')->where('cid = ?', $cid));

        foreach ($rows as $row) {
            $fieldName = (string) ($row['name'] ?? '');
            $type = (string) ($row['type'] ?? 'str');

            if ($fieldName === '') {
                continue;
            }

            $fields[$fieldName] = $type === 'json'
                ? json_decode((string) ($row['str_value'] ?? ''), true)
                : ($row[$type . '_value'] ?? '');
        }

        $cache[$cid] = $fields;
    }

    if (!array_key_exists($name, $cache[$cid])) {
        return null;
    }

    $value = $cache[$cid][$name];
    return is_array($value) ? trim(json_encode($value, JSON_UNESCAPED_UNICODE)) : trim((string) $value);
}

function slowcloud_poster_alt($archive): string
{
    $alt = slowcloud_field_value($archive, 'posterAlt');
    if ($alt !== '') {
        return slowcloud_seo_clean_text($alt, 120);
    }

    return slowcloud_seo_clean_text((string) ($archive->title ?? slowcloud_seo_archive_title($archive)), 120);
}

function slowcloud_post_card_style($archive): string
{
    $style = slowcloud_field_value($archive, 'posterListStyle');
    return in_array($style, ['standard', 'media', 'cover'], true) ? $style : 'standard';
}

function slowcloud_show_article_toc($archive): bool
{
    return slowcloud_field_value($archive, 'showToc') === '1';
}

function slowcloud_post_tags($archive, string $split = ', ', ?string $default = null): void
{
    $tags = $archive->tags ?? [];
    if (empty($tags) || !is_array($tags)) {
        echo $default ?? '';
        return;
    }

    $charset = (string) (($archive->options->charset ?? null) ?: 'UTF-8');
    $items = [];
    foreach ($tags as $tag) {
        $name = trim((string) ($tag['name'] ?? ''));
        $permalink = trim((string) ($tag['permalink'] ?? ''));
        if ($name === '') {
            continue;
        }

        $label = '#' . htmlspecialchars($name, ENT_QUOTES, $charset);
        $items[] = $permalink !== ''
            ? '<a href="' . htmlspecialchars($permalink, ENT_QUOTES, $charset) . '">' . $label . '</a>'
            : $label;
    }

    echo !empty($items) ? implode($split, $items) : ($default ?? '');
}

function slowcloud_seo_clean_text(string $text, int $length = 160): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
    $text = str_replace("\xc2\xa0", ' ', $text);
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));

    if ($text === '') {
        return '';
    }

    return \Typecho\Common::subStr($text, 0, $length);
}

function slowcloud_seo_escape(string $value, string $charset): string
{
    return htmlspecialchars($value, ENT_QUOTES, $charset);
}

function slowcloud_seo_is($archive, string $type): bool
{
    if (!is_object($archive) || !method_exists($archive, 'is')) {
        return false;
    }

    try {
        return (bool) $archive->is($type);
    } catch (\Throwable $e) {
        return false;
    }
}

function slowcloud_seo_archive_title($archive): string
{
    if (!is_object($archive)) {
        return '';
    }

    if (method_exists($archive, 'archiveTitle')) {
        $title = slowcloud_capture_output(static function () use ($archive): void {
            $archive->archiveTitle([
                'category' => _t('分类 %s'),
                'search' => _t('搜索 %s'),
                'tag' => _t('标签 %s'),
                'author' => _t('%s 的文章'),
            ], '', '');
        });

        $title = slowcloud_seo_clean_text($title, 120);
        if ($title !== '') {
            return $title;
        }
    }

    if (!slowcloud_seo_is($archive, 'single')) {
        return '';
    }

    return slowcloud_seo_clean_text((string) ($archive->title ?? ''), 120);
}

function slowcloud_seo_absolute_url($archive, string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    $options = $archive->options ?? \Widget\Options::alloc();
    return \Typecho\Common::url(ltrim($url, '/'), (string) ($options->siteUrl ?? ''));
}

function slowcloud_seo_strip_url_query(string $url): string
{
    $parts = parse_url($url);
    if (!is_array($parts)) {
        return $url;
    }

    $cleanUrl = '';
    if (isset($parts['scheme'])) {
        $cleanUrl .= $parts['scheme'] . '://';
    }
    if (isset($parts['user'])) {
        $cleanUrl .= $parts['user'];
        if (isset($parts['pass'])) {
            $cleanUrl .= ':' . $parts['pass'];
        }
        $cleanUrl .= '@';
    }
    if (isset($parts['host'])) {
        $cleanUrl .= $parts['host'];
    }
    if (isset($parts['port'])) {
        $cleanUrl .= ':' . $parts['port'];
    }

    $cleanUrl .= $parts['path'] ?? '';

    return $cleanUrl !== '' ? $cleanUrl : $url;
}

function slowcloud_seo_host_key(array $parts): string
{
    if (empty($parts['host'])) {
        return '';
    }

    return strtolower((string) $parts['host']) . ':' . (string) ($parts['port'] ?? '');
}

function slowcloud_seo_site_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return trim((string) ($options->siteUrl ?? ''));
}

function slowcloud_seo_request_host_key(): string
{
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }

    $parts = parse_url('http://' . $host);
    return is_array($parts) ? slowcloud_seo_host_key($parts) : '';
}

function slowcloud_seo_normalize_site_url($archive, string $url): string
{
    $url = slowcloud_seo_absolute_url($archive, $url);
    $siteUrl = slowcloud_seo_site_url($archive);
    $urlParts = parse_url($url);
    $siteParts = $siteUrl !== '' ? parse_url($siteUrl) : false;

    if (!is_array($urlParts) || !is_array($siteParts) || empty($siteParts['host'])) {
        return $url;
    }

    $urlHostKey = slowcloud_seo_host_key($urlParts);
    $siteHostKey = slowcloud_seo_host_key($siteParts);
    $requestHostKey = slowcloud_seo_request_host_key();

    if ($urlHostKey === '' || ($urlHostKey !== $siteHostKey && $urlHostKey !== $requestHostKey)) {
        return $url;
    }

    $normalized = ($siteParts['scheme'] ?? 'http') . '://' . $siteParts['host'];
    if (isset($siteParts['port'])) {
        $normalized .= ':' . $siteParts['port'];
    }

    $sitePath = isset($siteParts['path']) ? rtrim($siteParts['path'], '/') : '';
    $path = $urlParts['path'] ?? '/';
    if ($sitePath !== '' && $path !== $sitePath && strpos($path, $sitePath . '/') !== 0) {
        $path = $sitePath . '/' . ltrim($path, '/');
    }

    $normalized .= $path !== '' ? $path : '/';
    if (isset($urlParts['query'])) {
        $normalized .= '?' . $urlParts['query'];
    }
    if (isset($urlParts['fragment'])) {
        $normalized .= '#' . $urlParts['fragment'];
    }

    return $normalized;
}

function slowcloud_seo_current_url($archive): string
{
    $url = '';
    if (is_object($archive) && method_exists($archive, 'getArchiveUrl')) {
        $url = trim((string) $archive->getArchiveUrl());
    }

    if ($url === '') {
        $url = trim((string) ($archive->permalink ?? ''));
    }

    if ($url === '') {
        $options = $archive->options ?? \Widget\Options::alloc();
        $url = (string) ($options->siteUrl ?? '');
    }

    return slowcloud_seo_strip_url_query(slowcloud_seo_normalize_site_url($archive, $url));
}

function slowcloud_seo_canonical($archive): string
{
    $customCanonical = slowcloud_field_value($archive, 'seoCanonical');
    if ($customCanonical !== '') {
        return slowcloud_seo_strip_url_query(slowcloud_seo_normalize_site_url($archive, $customCanonical));
    }

    return slowcloud_seo_current_url($archive);
}

function slowcloud_seo_description($archive): string
{
    $description = '';

    if (slowcloud_seo_is($archive, 'single')) {
        $description = slowcloud_field_value($archive, 'seoDescription');
    }

    if ($description === '' && slowcloud_seo_is($archive, 'single')) {
        $description = trim((string) ($archive->plainExcerpt ?? ''));
    }

    if ($description === '' && is_object($archive) && method_exists($archive, 'getArchiveDescription')) {
        $description = trim((string) $archive->getArchiveDescription());
    }

    if ($description === '') {
        $description = slowcloud_intro($archive);
    }

    $options = $archive->options ?? \Widget\Options::alloc();
    if ($description === '') {
        $description = (string) ($options->description ?? '');
    }

    return slowcloud_seo_clean_text($description, 160);
}

function slowcloud_seo_local_image_file($archive, string $url): string
{
    $url = slowcloud_seo_absolute_url($archive, $url);
    $urlParts = parse_url($url);
    if (!is_array($urlParts) || empty($urlParts['path'])) {
        return '';
    }

    $siteUrl = slowcloud_seo_site_url($archive);
    $siteParts = $siteUrl !== '' ? parse_url($siteUrl) : false;
    $requestHostKey = slowcloud_seo_request_host_key();
    $urlHostKey = slowcloud_seo_host_key($urlParts);
    $siteHostKey = is_array($siteParts) ? slowcloud_seo_host_key($siteParts) : '';

    if ($urlHostKey !== '' && $urlHostKey !== $siteHostKey && $urlHostKey !== $requestHostKey) {
        return '';
    }

    $path = $urlParts['path'];
    if (is_array($siteParts) && !empty($siteParts['path'])) {
        $sitePath = rtrim((string) $siteParts['path'], '/');
        if ($sitePath !== '' && strpos($path, $sitePath . '/') === 0) {
            $path = substr($path, strlen($sitePath));
        }
    }

    $file = __TYPECHO_ROOT_DIR__ . '/' . ltrim(rawurldecode($path), '/');
    return is_file($file) ? $file : '';
}

function slowcloud_image_dimensions($archive, string $url): array
{
    static $cache = [];

    $url = trim($url);
    if ($url === '') {
        return [];
    }

    if (isset($cache[$url])) {
        return $cache[$url];
    }

    $file = slowcloud_seo_local_image_file($archive, $url);
    if ($file === '' || !function_exists('getimagesize')) {
        return $cache[$url] = [];
    }

    $size = @getimagesize($file);
    if (!is_array($size) || empty($size[0]) || empty($size[1])) {
        return $cache[$url] = [];
    }

    return $cache[$url] = [
        'width' => (int) $size[0],
        'height' => (int) $size[1],
    ];
}

function slowcloud_image_dimension_attrs($archive, string $url, string $charset): string
{
    $dimensions = slowcloud_image_dimensions($archive, $url);
    if (empty($dimensions['width']) || empty($dimensions['height'])) {
        return '';
    }

    return ' width="' . slowcloud_seo_escape((string) $dimensions['width'], $charset) . '"'
        . ' height="' . slowcloud_seo_escape((string) $dimensions['height'], $charset) . '"';
}

function slowcloud_image_srcset_attrs($archive, string $url, string $charset, string $sizes = ''): string
{
    $dimensions = slowcloud_image_dimensions($archive, $url);
    if (empty($dimensions['width'])) {
        return '';
    }

    $attrs = ' srcset="' . slowcloud_seo_escape($url . ' ' . (int) $dimensions['width'] . 'w', $charset) . '"';
    if ($sizes !== '') {
        $attrs .= ' sizes="' . slowcloud_seo_escape($sizes, $charset) . '"';
    }

    return $attrs;
}

function slowcloud_seo_image_object($archive, string $url, string $alt = ''): array
{
    $image = [
        '@type' => 'ImageObject',
        'url' => $url,
    ];

    $dimensions = slowcloud_image_dimensions($archive, $url);
    if (!empty($dimensions['width']) && !empty($dimensions['height'])) {
        $image['width'] = $dimensions['width'];
        $image['height'] = $dimensions['height'];
    }
    if ($alt !== '') {
        $image['caption'] = $alt;
    }

    return $image;
}

function slowcloud_seo_image($archive): string
{
    $image = '';
    if (slowcloud_seo_is($archive, 'single')) {
        $image = slowcloud_poster($archive);
    }

    if ($image === '') {
        $image = slowcloud_header_background($archive);
    }

    if ($image === '') {
        $image = slowcloud_logo_url($archive);
    }

    return slowcloud_seo_absolute_url($archive, $image);
}

function slowcloud_seo_datetime($value): string
{
    if (is_object($value) && method_exists($value, 'format')) {
        return (string) $value->format('c');
    }

    $timestamp = (int) $value;
    return $timestamp > 0 ? date('c', $timestamp) : '';
}

function slowcloud_seo_term_names($terms): array
{
    if (!is_array($terms)) {
        return [];
    }

    $names = [];
    foreach ($terms as $term) {
        $name = is_array($term) ? trim((string) ($term['name'] ?? '')) : trim((string) $term);
        if ($name !== '') {
            $names[$name] = $name;
        }
    }

    return array_values($names);
}

function slowcloud_seo_word_count($archive): int
{
    if (!slowcloud_seo_is($archive, 'single')) {
        return 0;
    }

    $text = slowcloud_seo_clean_text((string) ($archive->text ?? ''), 100000);
    return $text !== '' ? \Typecho\Common::strLen($text) : 0;
}

function slowcloud_seo_primary_category_data($archive): array
{
    $categories = $archive->categories ?? [];
    if (!is_array($categories) || empty($categories)) {
        return [];
    }

    $selected = null;
    foreach ($categories as $category) {
        if ((int) ($category['parent'] ?? 0) > 0) {
            $selected = $category;
            break;
        }
    }

    if ($selected === null) {
        $selected = end($categories);
    }

    $name = trim((string) ($selected['name'] ?? ''));
    if ($name === '') {
        return [];
    }

    return [
        'name' => $name,
        'url' => slowcloud_seo_normalize_site_url($archive, trim((string) ($selected['permalink'] ?? ''))),
    ];
}

function slowcloud_seo_robots($archive): string
{
    $themeFile = slowcloud_safe_theme_file($archive);
    $customNoindex = slowcloud_field_value($archive, 'seoNoindex') === '1';

    if (
        $customNoindex
        || (
        $themeFile === '404.php'
        || slowcloud_seo_is($archive, 'search')
        || slowcloud_seo_is($archive, 'feed')
        || isset($_GET['preview'])
        )
    ) {
        return 'noindex,follow';
    }

    return 'index,follow';
}

function slowcloud_seo_search_target($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $target = \Typecho\Router::url(
        'search',
        ['keywords' => '{search_term_string}'],
        (string) ($options->index ?? $options->siteUrl ?? '')
    );

    if ($target === '#') {
        $target = slowcloud_join_url((string) ($options->siteUrl ?? ''), 'search/{search_term_string}/');
    }

    return slowcloud_seo_normalize_site_url($archive, $target);
}

function slowcloud_seo_context($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $siteName = trim((string) ($options->title ?? ''));
    $titleSuffix = slowcloud_tab_title($archive);
    if ($titleSuffix === '') {
        $titleSuffix = $siteName !== '' ? $siteName : 'slowcloud';
    }

    $isIndex = slowcloud_seo_is($archive, 'index') || slowcloud_seo_is($archive, 'front');
    $isPost = slowcloud_seo_is($archive, 'post');
    $isPage = slowcloud_seo_is($archive, 'page');
    $isSingle = slowcloud_seo_is($archive, 'single');
    $archiveTitle = slowcloud_seo_archive_title($archive);
    $customTitle = $isSingle ? slowcloud_seo_clean_text(slowcloud_field_value($archive, 'seoTitle'), 120) : '';
    if ($customTitle !== '') {
        $archiveTitle = $customTitle;
    }

    $title = $archiveTitle !== '' && !$isIndex
        ? $archiveTitle . ' - ' . $titleSuffix
        : $titleSuffix;

    $authorName = '';
    if (isset($archive->author) && is_object($archive->author)) {
        $authorName = trim((string) ($archive->author->screenName ?? $archive->author->name ?? ''));
    }
    if ($authorName === '') {
        $authorName = slowcloud_author_name($archive);
    }

    $keywords = [];
    if ($isSingle) {
        $keywords = array_values(array_unique(array_merge(
            slowcloud_seo_term_names($archive->categories ?? []),
            slowcloud_seo_term_names($archive->tags ?? [])
        )));
    } elseif ($archiveTitle !== '' && (slowcloud_seo_is($archive, 'category') || slowcloud_seo_is($archive, 'tag'))) {
        $keywords = [$archiveTitle];
    }

    $image = slowcloud_seo_image($archive);
    $imageAlt = $isSingle ? slowcloud_poster_alt($archive) : '';
    $imageObject = $image !== '' ? slowcloud_seo_image_object($archive, $image, $imageAlt) : [];

    return [
        'site_name' => $siteName !== '' ? $siteName : $titleSuffix,
        'title' => $title,
        'page_title' => $archiveTitle,
        'description' => slowcloud_seo_description($archive),
        'canonical' => slowcloud_seo_canonical($archive),
        'image' => $image,
        'image_alt' => $imageAlt,
        'image_object' => $imageObject,
        'type' => $isPost ? 'article' : 'website',
        'robots' => slowcloud_seo_robots($archive),
        'author' => $authorName,
        'published_time' => $isSingle ? slowcloud_seo_datetime($archive->created ?? 0) : '',
        'modified_time' => $isSingle ? slowcloud_seo_datetime($archive->modified ?? ($archive->created ?? 0)) : '',
        'word_count' => slowcloud_seo_word_count($archive),
        'keywords' => $keywords,
        'category' => slowcloud_seo_primary_category_data($archive),
        'is_index' => $isIndex,
        'is_post' => $isPost,
        'is_page' => $isPage,
        'is_single' => $isSingle,
    ];
}

function slowcloud_seo_json(array $data): string
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return is_string($json) ? $json : '{}';
}

function slowcloud_seo_breadcrumb_items($archive, array $context): array
{
    if (!empty($context['is_index'])) {
        return [];
    }

    $options = $archive->options ?? \Widget\Options::alloc();
    $items = [
        [
            'name' => _t('首页'),
            'url' => slowcloud_seo_absolute_url($archive, (string) ($options->siteUrl ?? '')),
        ],
    ];

    if (!empty($context['is_post']) && !empty($context['category']['name'])) {
        $items[] = [
            'name' => $context['category']['name'],
            'url' => $context['category']['url'] ?? '',
        ];
    }

    $pageTitle = (string) ($context['page_title'] ?: $context['title']);
    if ($pageTitle !== '') {
        $items[] = [
            'name' => $pageTitle,
            'url' => (string) $context['canonical'],
        ];
    }

    return $items;
}

function slowcloud_render_json_ld($archive, array $context): void
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $siteUrl = slowcloud_seo_absolute_url($archive, (string) ($options->siteUrl ?? ''));
    $siteUrl = $siteUrl !== '' ? rtrim($siteUrl, '/') . '/' : '';
    $siteId = $siteUrl !== '' ? $siteUrl . '#website' : '';
    $siteDescription = slowcloud_seo_clean_text(slowcloud_intro($archive), 160);
    $graph = [];

    if ($siteUrl !== '') {
        $website = [
            '@type' => 'WebSite',
            '@id' => $siteId,
            'url' => $siteUrl,
            'name' => $context['site_name'],
        ];

        if ($siteDescription !== '') {
            $website['description'] = $siteDescription;
        }

        $website['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => slowcloud_seo_search_target($archive),
            'query-input' => 'required name=search_term_string',
        ];

        $graph[] = $website;
    }

    $pageNode = [
        '@type' => !empty($context['is_post']) ? 'BlogPosting' : 'WebPage',
        '@id' => $context['canonical'] !== '' ? $context['canonical'] . '#webpage' : '',
        'url' => $context['canonical'],
        'name' => $context['title'],
        'headline' => $context['page_title'] !== '' ? $context['page_title'] : $context['title'],
    ];

    if ($siteId !== '') {
        $pageNode['isPartOf'] = ['@id' => $siteId];
    }
    if ($context['description'] !== '') {
        $pageNode['description'] = $context['description'];
    }
    if ($context['image'] !== '') {
        $pageNode['image'] = !empty($context['image_object'])
            ? [$context['image_object']]
            : [$context['image']];
        $pageNode['thumbnailUrl'] = $context['image'];
    }
    if (!empty($context['is_post'])) {
        if ($context['published_time'] !== '') {
            $pageNode['datePublished'] = $context['published_time'];
        }
        if ($context['modified_time'] !== '') {
            $pageNode['dateModified'] = $context['modified_time'];
        }
        if ($context['author'] !== '') {
            $pageNode['author'] = [
                '@type' => 'Person',
                'name' => $context['author'],
            ];
        }
        if (!empty($context['category']['name'])) {
            $pageNode['articleSection'] = $context['category']['name'];
        }
        if (!empty($context['keywords'])) {
            $pageNode['keywords'] = implode(',', $context['keywords']);
        }
        if (!empty($context['word_count'])) {
            $pageNode['wordCount'] = (int) $context['word_count'];
        }
        $pageNode['inLanguage'] = 'zh-CN';
        $pageNode['isAccessibleForFree'] = true;

        $publisher = [
            '@type' => 'Organization',
            'name' => $context['site_name'],
        ];
        $logoUrl = slowcloud_seo_absolute_url($archive, slowcloud_logo_url($archive));
        if ($logoUrl !== '') {
            $publisher['logo'] = slowcloud_seo_image_object($archive, $logoUrl);
        }
        $pageNode['publisher'] = $publisher;
        $pageNode['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => $context['canonical'],
        ];
    }

    $graph[] = $pageNode;

    $breadcrumbs = slowcloud_seo_breadcrumb_items($archive, $context);
    if (!empty($breadcrumbs)) {
        $listItems = [];
        foreach ($breadcrumbs as $index => $item) {
            $listItem = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
            ];
            if (!empty($item['url'])) {
                $listItem['item'] = $item['url'];
            }
            $listItems[] = $listItem;
        }

        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    echo '<script type="application/ld+json">' . slowcloud_seo_json([
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ]) . '</script>' . "\n";
}

function slowcloud_render_seo_meta($archive, ?array $context = null): void
{
    $context = $context ?? slowcloud_seo_context($archive);
    $options = $archive->options ?? \Widget\Options::alloc();
    $charset = (string) ($options->charset ?? 'UTF-8');
    $canonical = (string) $context['canonical'];

    echo '<meta name="description" content="' . slowcloud_seo_escape((string) $context['description'], $charset) . '">' . "\n";
    if (!empty($context['keywords'])) {
        echo '<meta name="keywords" content="' . slowcloud_seo_escape(implode(',', $context['keywords']), $charset) . '">' . "\n";
    }
    echo '<meta name="robots" content="' . slowcloud_seo_escape((string) $context['robots'], $charset) . '">' . "\n";

    if ($canonical !== '' && strpos((string) $context['robots'], 'noindex') !== 0) {
        echo '<link rel="canonical" href="' . slowcloud_seo_escape($canonical, $charset) . '">' . "\n";
    }

    echo '<meta property="og:type" content="' . slowcloud_seo_escape((string) $context['type'], $charset) . '">' . "\n";
    echo '<meta property="og:title" content="' . slowcloud_seo_escape((string) $context['title'], $charset) . '">' . "\n";
    echo '<meta property="og:description" content="' . slowcloud_seo_escape((string) $context['description'], $charset) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . slowcloud_seo_escape((string) $context['site_name'], $charset) . '">' . "\n";
    if ($canonical !== '') {
        echo '<meta property="og:url" content="' . slowcloud_seo_escape($canonical, $charset) . '">' . "\n";
    }
    if ($context['image'] !== '') {
        echo '<meta property="og:image" content="' . slowcloud_seo_escape((string) $context['image'], $charset) . '">' . "\n";
        if (!empty($context['image_object']['width']) && !empty($context['image_object']['height'])) {
            echo '<meta property="og:image:width" content="' . slowcloud_seo_escape((string) $context['image_object']['width'], $charset) . '">' . "\n";
            echo '<meta property="og:image:height" content="' . slowcloud_seo_escape((string) $context['image_object']['height'], $charset) . '">' . "\n";
        }
        if (!empty($context['image_alt'])) {
            echo '<meta property="og:image:alt" content="' . slowcloud_seo_escape((string) $context['image_alt'], $charset) . '">' . "\n";
        }
    }
    if ($context['type'] === 'article') {
        if ($context['published_time'] !== '') {
            echo '<meta property="article:published_time" content="' . slowcloud_seo_escape((string) $context['published_time'], $charset) . '">' . "\n";
        }
        if ($context['modified_time'] !== '') {
            echo '<meta property="article:modified_time" content="' . slowcloud_seo_escape((string) $context['modified_time'], $charset) . '">' . "\n";
        }
        if ($context['author'] !== '') {
            echo '<meta property="article:author" content="' . slowcloud_seo_escape((string) $context['author'], $charset) . '">' . "\n";
        }
        if (!empty($context['category']['name'])) {
            echo '<meta property="article:section" content="' . slowcloud_seo_escape((string) $context['category']['name'], $charset) . '">' . "\n";
        }
        foreach ($context['keywords'] as $keyword) {
            echo '<meta property="article:tag" content="' . slowcloud_seo_escape((string) $keyword, $charset) . '">' . "\n";
        }
    }

    echo '<meta name="twitter:card" content="' . ($context['image'] !== '' ? 'summary_large_image' : 'summary') . '">' . "\n";
    echo '<meta name="twitter:title" content="' . slowcloud_seo_escape((string) $context['title'], $charset) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . slowcloud_seo_escape((string) $context['description'], $charset) . '">' . "\n";
    if ($context['image'] !== '') {
        echo '<meta name="twitter:image" content="' . slowcloud_seo_escape((string) $context['image'], $charset) . '">' . "\n";
    }
    if ($canonical !== '') {
        $domain = parse_url($canonical, PHP_URL_HOST);
        if (is_string($domain) && $domain !== '') {
            echo '<meta name="twitter:domain" content="' . slowcloud_seo_escape($domain, $charset) . '">' . "\n";
        }
    }

    slowcloud_render_json_ld($archive, $context);
}

function slowcloud_render_typecho_header($archive, string $rule = ''): void
{
    if (!is_object($archive) || !method_exists($archive, 'header')) {
        return;
    }

    $header = slowcloud_capture_output(static function () use ($archive, $rule): void {
        $archive->header($rule);
    });

    $filtered = preg_replace('#<link\s+rel=(["\'])canonical\1[^>]*>\s*#i', '', $header);
    echo $filtered ?? $header;
}

function slowcloud_upload_cdn_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $cdnUrl = trim((string) ($options->uploadCdnUrl ?? ''));

    if ($cdnUrl === '' || !preg_match('#^https?://#i', $cdnUrl)) {
        return '';
    }

    return rtrim($cdnUrl, '/');
}

function slowcloud_join_url(string $base, string $path): string
{
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function slowcloud_rewrite_cdn_url($archive, string $url, ?string $requiredPrefix = null): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    $cdnUrl = slowcloud_upload_cdn_url($archive);
    if ($cdnUrl === '') {
        return $url;
    }

    $siteUrl = rtrim((string) (($archive->options ?? \Widget\Options::alloc())->siteUrl ?? ''), '/');
    $siteParts = $siteUrl !== '' ? parse_url($siteUrl) : false;
    $urlParts = parse_url($url);

    if ($urlParts === false) {
        return $url;
    }

    if (!isset($urlParts['scheme'], $urlParts['host'])) {
        $path = $urlParts['path'] ?? '';
        if ($requiredPrefix === null || strpos(ltrim($path, '/'), ltrim($requiredPrefix, '/')) === 0) {
            return slowcloud_join_url($cdnUrl, ltrim($path, '/'))
                . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '')
                . (isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '');
        }

        return $url;
    }

    if ($siteParts === false || !isset($siteParts['host'])) {
        return $url;
    }

    $sameHost = strcasecmp($urlParts['host'], $siteParts['host']) === 0;
    $samePort = ($urlParts['port'] ?? null) === ($siteParts['port'] ?? null);
    if (!$sameHost || !$samePort) {
        return $url;
    }

    $sitePath = isset($siteParts['path']) ? rtrim($siteParts['path'], '/') : '';
    $urlPath = $urlParts['path'] ?? '';
    $relativePath = $urlPath;

    if ($sitePath !== '' && strpos($urlPath, $sitePath . '/') === 0) {
        $relativePath = substr($urlPath, strlen($sitePath) + 1);
    } else {
        $relativePath = ltrim($urlPath, '/');
    }

    if ($requiredPrefix !== null && strpos($relativePath, ltrim($requiredPrefix, '/')) !== 0) {
        return $url;
    }

    return slowcloud_join_url($cdnUrl, $relativePath)
        . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '')
        . (isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '');
}

function slowcloud_rewrite_upload_url($archive, string $url): string
{
    return slowcloud_rewrite_cdn_url($archive, $url, 'usr/uploads/');
}

function slowcloud_rewrite_upload_html($archive, string $html): string
{
    $cdnUrl = slowcloud_upload_cdn_url($archive);
    if ($cdnUrl === '' || $html === '') {
        return $html;
    }

    return (string) preg_replace_callback(
        '/\b(src|href)=("|\')(.*?)\2/i',
        static function (array $matches) use ($archive): string {
            return $matches[1] . '=' . $matches[2]
                . slowcloud_rewrite_upload_url($archive, html_entity_decode($matches[3], ENT_QUOTES, 'UTF-8'))
                . $matches[2];
        },
        $html
    );
}

function slowcloud_bilibili_video_data(string $url): ?array
{
    $url = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
    if ($url === '') {
        return null;
    }

    $parts = parse_url($url);
    if ($parts === false || !isset($parts['host'], $parts['path'])) {
        return null;
    }

    $host = strtolower((string) $parts['host']);
    if ($host !== 'bilibili.com' && $host !== 'www.bilibili.com') {
        return null;
    }

    if (!preg_match('~^/video/(BV[0-9A-Za-z]{10})/?$~', (string) $parts['path'], $matches)) {
        return null;
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str((string) $parts['query'], $query);
    }

    $page = filter_var($query['p'] ?? 1, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 1000],
    ]);
    $time = filter_var($query['t'] ?? 0, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 86400],
    ]);

    return [
        'bvid' => $matches[1],
        'page' => $page === false ? 1 : $page,
        'time' => $time === false ? 0 : $time,
    ];
}

function slowcloud_bilibili_embed_element(\DOMDocument $document, array $video): \DOMElement
{
    $params = [
        'bvid' => $video['bvid'],
        'page' => $video['page'],
        'high_quality' => 1,
        'danmaku' => 0,
    ];

    if ($video['time'] > 0) {
        $params['t'] = $video['time'];
    }

    $figure = $document->createElement('figure');
    $figure->setAttribute('class', 'slowcloud-bilibili-embed');

    $player = $document->createElement('div');
    $player->setAttribute('class', 'slowcloud-bilibili-embed__player');

    $iframe = $document->createElement('iframe');
    $iframe->setAttribute('src', 'https://player.bilibili.com/player.html?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    $iframe->setAttribute('title', 'Bilibili video');
    $iframe->setAttribute('loading', 'lazy');
    $iframe->setAttribute('scrolling', 'no');
    $iframe->setAttribute('frameborder', '0');
    $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
    $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

    $player->appendChild($iframe);
    $figure->appendChild($player);

    return $figure;
}

function slowcloud_embed_bilibili_videos(string $html): string
{
    if ($html === '' || stripos($html, 'bilibili.com/video/') === false || !class_exists('DOMDocument')) {
        return $html;
    }

    $document = new \DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8"><div id="slowcloud-content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return $html;
    }

    $paragraphs = $document->getElementsByTagName('p');
    $replace = [];

    foreach ($paragraphs as $paragraph) {
        $link = null;
        $valid = true;

        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMText && trim($child->nodeValue) === '') {
                continue;
            }

            if ($child instanceof \DOMElement && strtolower($child->tagName) === 'a' && $link === null) {
                $link = $child;
                continue;
            }

            $valid = false;
            break;
        }

        $video = $valid && $link instanceof \DOMElement
            ? slowcloud_bilibili_video_data((string) $link->getAttribute('href'))
            : null;

        if ($video !== null) {
            $replace[] = [$paragraph, $video];
        }
    }

    foreach ($replace as [$paragraph, $video]) {
        if ($paragraph->parentNode) {
            $paragraph->parentNode->replaceChild(slowcloud_bilibili_embed_element($document, $video), $paragraph);
        }
    }

    $root = $document->getElementById('slowcloud-content-root');
    if (!$root) {
        return $html;
    }

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $document->saveHTML($child);
    }

    return $result !== '' ? $result : $html;
}

function slowcloud_sanitize_inline_svg(string $svg): string
{
    $svg = trim($svg);
    if ($svg === '' || stripos($svg, '<svg') === false || stripos($svg, '</svg>') === false) {
        return '';
    }
    if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)) {
        return '';
    }

    $previous = libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $loaded = $dom->loadXML($svg, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$dom->documentElement || strtolower($dom->documentElement->tagName) !== 'svg') {
        return '';
    }

    $allowedTags = [
        'svg' => true,
        'g' => true,
        'path' => true,
        'circle' => true,
        'rect' => true,
        'line' => true,
        'polyline' => true,
        'polygon' => true,
        'ellipse' => true,
        'defs' => true,
        'clippath' => true,
        'mask' => true,
        'use' => true,
        'title' => true,
        'desc' => true,
        'lineargradient' => true,
        'radialgradient' => true,
        'stop' => true,
    ];
    $allowedAttrs = [
        'aria-hidden' => true,
        'class' => true,
        'clip-rule' => true,
        'clip-path' => true,
        'cx' => true,
        'cy' => true,
        'd' => true,
        'fill' => true,
        'fill-rule' => true,
        'height' => true,
        'id' => true,
        'mask' => true,
        'offset' => true,
        'opacity' => true,
        'points' => true,
        'r' => true,
        'rx' => true,
        'ry' => true,
        'stroke' => true,
        'stroke-dasharray' => true,
        'stroke-dashoffset' => true,
        'stroke-linecap' => true,
        'stroke-linejoin' => true,
        'stroke-miterlimit' => true,
        'stroke-opacity' => true,
        'stroke-width' => true,
        'stop-color' => true,
        'stop-opacity' => true,
        'transform' => true,
        'viewbox' => true,
        'width' => true,
        'x' => true,
        'x1' => true,
        'x2' => true,
        'href' => true,
        'xlink:href' => true,
        'xmlns' => true,
        'xmlns:xlink' => true,
        'y' => true,
        'y1' => true,
        'y2' => true,
    ];

    $walker = static function (\DOMNode $node) use (&$walker, $allowedTags, $allowedAttrs): void {
        if ($node instanceof \DOMElement) {
            if (!isset($allowedTags[strtolower($node->tagName)])) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
                return;
            }

            for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
                $attr = $node->attributes->item($i);
                if (!$attr) {
                    continue;
                }

                $name = $attr->name;
                $value = trim($attr->value);
                $lowerName = strtolower($name);
                $lowerValue = strtolower($value);
                $isExternalHref = in_array($lowerName, ['href', 'xlink:href'], true) && $value !== '' && $value[0] !== '#';
                $hasUnsafeUrl = preg_match('/(?:javascript|data):/i', $lowerValue)
                    || (strpos($lowerValue, 'url(') !== false && !preg_match('/url\(\s*#[-_a-z0-9]+\s*\)/i', $lowerValue));
                if (!isset($allowedAttrs[$lowerName]) || strpos($lowerName, 'on') === 0 || $isExternalHref || $hasUnsafeUrl) {
                    $node->removeAttribute($name);
                }
            }
        }

        for ($child = $node->firstChild; $child !== null;) {
            $next = $child->nextSibling;
            $walker($child);
            $child = $next;
        }
    };
    $walker($dom->documentElement);

    return trim((string) $dom->saveXML($dom->documentElement));
}

function slowcloud_is_safe_link_url(string $url): bool
{
    if ($url === '' || preg_match('/[\r\n]/', $url)) {
        return false;
    }

    if (preg_match('#^https?://#i', $url)) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    return strpos($url, '/') === 0 && strpos($url, '//') !== 0;
}

function slowcloud_parse_custom_social_link_entries(string $raw): array
{
    $jsonEntries = json_decode($raw, true);
    if (is_array($jsonEntries)) {
        $entries = [];
        foreach ($jsonEntries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $entries[] = [
                trim((string) ($entry['name'] ?? '')),
                trim((string) ($entry['url'] ?? '')),
                trim((string) ($entry['svg'] ?? '')),
            ];
        }

        return $entries;
    }

    $entries = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];
    $count = count($lines);

    for ($i = 0; $i < $count; $i++) {
        $line = trim((string) $lines[$i]);
        if ($line === '' || strpos($line, '|') === false) {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 3));
        if (count($parts) >= 3) {
            $entries[] = $parts;
            continue;
        }

        [$name, $url] = $parts;
        $svgLines = [];
        while ($i + 1 < $count) {
            $nextLine = trim((string) $lines[$i + 1]);
            if ($nextLine === '') {
                $i++;
                if (!empty($svgLines) && stripos(implode('', $svgLines), '</svg>') !== false) {
                    break;
                }
                continue;
            }

            if (!empty($svgLines) && strpos($nextLine, '|') !== false && stripos(implode('', $svgLines), '</svg>') !== false) {
                break;
            }

            $svgLines[] = $nextLine;
            $i++;
            if (stripos($nextLine, '</svg>') !== false) {
                break;
            }
        }

        if (!empty($svgLines)) {
            $entries[] = [$name, $url, implode('', $svgLines)];
        }
    }

    return $entries;
}

function slowcloud_heading_anchor(string $text, array &$used): string
{
    $text = slowcloud_seo_clean_text($text, 80);
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    $slug = trim((string) $slug, '-');
    if ($slug === '') {
        $slug = 'section';
    }

    $base = strtolower($slug);
    $count = $used[$base] ?? 0;
    $used[$base] = $count + 1;

    return $count > 0 ? $base . '-' . ($count + 1) : $base;
}

function slowcloud_build_toc_html(array $items): string
{
    if (count($items) < 1) {
        return '';
    }

    $html = '<nav class="slowcloud-article-toc" aria-label="' . htmlspecialchars(_t('文章目录'), ENT_QUOTES, 'UTF-8') . '">';
    $html .= '<div class="slowcloud-article-toc__title">' . htmlspecialchars(_t('文章目录'), ENT_QUOTES, 'UTF-8') . '</div>';
    $html .= '<ol class="slowcloud-article-toc__list">';

    foreach ($items as $item) {
        $level = max(2, min(4, (int) $item['level']));
        $html .= '<li class="slowcloud-article-toc__item slowcloud-article-toc__item--level-' . $level . '">';
        $html .= '<a href="#' . htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) $item['text'], ENT_QUOTES, 'UTF-8') . '</a>';
        $html .= '</li>';
    }

    $html .= '</ol></nav>';
    return $html;
}

function slowcloud_enhance_content_headings(string $html): array
{
    if ($html === '' || !class_exists('\DOMDocument')) {
        return [
            'html' => $html,
            'toc' => '',
        ];
    }

    $document = new \DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8"><div id="slowcloud-content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return [
            'html' => $html,
            'toc' => '',
        ];
    }

    $xpath = new \DOMXPath($document);
    $headings = $xpath->query('//*[@id="slowcloud-content-root"]//*[self::h2 or self::h3 or self::h4]');
    $items = [];
    $used = [];

    if ($headings) {
        foreach ($headings as $heading) {
            if (!$heading instanceof \DOMElement) {
                continue;
            }

            $text = trim((string) $heading->textContent);
            if ($text === '') {
                continue;
            }

            $id = trim((string) $heading->getAttribute('id'));
            if ($id === '') {
                $id = slowcloud_heading_anchor($text, $used);
                $heading->setAttribute('id', $id);
            } else {
                $used[$id] = ($used[$id] ?? 0) + 1;
            }

            $heading->setAttribute('tabindex', '-1');
            $items[] = [
                'level' => (int) substr($heading->tagName, 1),
                'id' => $id,
                'text' => $text,
            ];
        }
    }

    $root = $document->getElementById('slowcloud-content-root');
    $body = '';
    if ($root) {
        foreach ($root->childNodes as $child) {
            $body .= $document->saveHTML($child);
        }
    }

    return [
        'html' => $body !== '' ? $body : $html,
        'toc' => slowcloud_build_toc_html($items),
    ];
}

function slowcloud_render_content($archive): void
{
    ob_start();
    $archive->content();
    $html = slowcloud_rewrite_upload_html($archive, (string) ob_get_clean());
    $html = slowcloud_replace_owo_shortcodes($archive, $html);
    $html = slowcloud_embed_bilibili_videos($html);
    $enhanced = slowcloud_enhance_content_headings($html);
    echo (slowcloud_show_article_toc($archive) ? $enhanced['toc'] : '') . $enhanced['html'];
}

function slowcloud_render_excerpt($archive, int $length = 180, string $suffix = '...'): void
{
    ob_start();
    $archive->excerpt($length, $suffix);
    $html = slowcloud_rewrite_upload_html($archive, (string) ob_get_clean());
    echo slowcloud_replace_owo_shortcodes($archive, $html);
}

function slowcloud_views($archive): int
{
    return max(0, (int) ($archive->fields->views ?? 0));
}

function slowcloud_views_text($archive): string
{
    return sprintf(_t('%d 次浏览'), slowcloud_views($archive));
}

function slowcloud_record_post_view_by_cid(int $cid, $archive = null): int
{
    if ($cid <= 0) {
        return 0;
    }

    $db = \Typecho\Db::get();
    $fieldsTable = $db->getPrefix() . 'fields';
    $exists = $db->fetchRow($db->select('cid')
        ->from('table.fields')
        ->where('cid = ? AND name = ?', $cid, 'views')
        ->limit(1));

    if (!$exists) {
        try {
            $db->query($db->insert('table.fields')->rows([
                'cid' => $cid,
                'name' => 'views',
                'type' => 'int',
                'str_value' => null,
                'int_value' => 0,
                'float_value' => 0,
            ]));
        } catch (\Throwable $e) {
            // Another request may have created the field between the read and insert.
        }
    }

    $db->query("UPDATE {$fieldsTable} SET int_value = int_value + 1 WHERE cid = {$cid} AND name = 'views'");
    $row = $db->fetchRow($db->select('int_value')
        ->from('table.fields')
        ->where('cid = ? AND name = ?', $cid, 'views')
        ->limit(1));
    $views = max(0, (int) ($row['int_value'] ?? 0));

    if ($archive !== null && isset($archive->fields)) {
        $archive->fields->views = $views;
    }

    return $views;
}

function slowcloud_record_view($archive): int
{
    if (!isset($archive->cid) || (string) ($archive->type ?? '') !== 'post') {
        return 0;
    }

    // Views are recorded by the validated browser beacon, not during rendering.
    return slowcloud_views($archive);
}

function slowcloud_show_sidebar($archive): bool
{
    $options = $archive->options ?? \Widget\Options::alloc();

    return (string) ($options->showSidebar ?? '1') === '1';
}

function slowcloud_theme_mode($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $mode = trim((string) ($options->themeMode ?? 'system'));

    if (in_array($mode, ['light', 'dark', 'system'], true)) {
        return $mode;
    }

    return 'system';
}

function slowcloud_basic_layout($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return trim((string) ($options->basicLayout ?? 'default')) === 'classic' ? 'classic' : 'default';
}

function slowcloud_author_avatar($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $avatar = trim((string) ($options->authorAvatar ?? ''));

    if ($avatar !== '') {
        return $avatar;
    }

    return slowcloud_theme_asset_url('usr/themes/slowcloud/assets/img/avatar.jpg', $archive);
}

function slowcloud_comment_default_avatar($archive): string
{
    return slowcloud_theme_asset_url('usr/themes/slowcloud/assets/img/avatar.webp', $archive);
}

function slowcloud_comment_avatar_url($comment, int $size = 32, ?string $default = null, ?string $rating = null): string
{
    $mail = strtolower(trim((string) ($comment->mail ?? '')));
    $hash = $mail !== '' ? md5($mail) : '';
    $params = ['s' => $size];

    if ($rating !== null && $rating !== '') {
        $params['r'] = $rating;
    }

    $params['d'] = $default ?: slowcloud_comment_default_avatar($comment);

    return 'https://cravatar.cn/avatar/' . $hash . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function slowcloud_comment_avatar_srcset($comment, int $size = 32, ?string $default = null, ?string $rating = null): string
{
    return implode(', ', [
        slowcloud_comment_avatar_url($comment, $size * 2, $default, $rating) . ' 2x',
        slowcloud_comment_avatar_url($comment, $size * 3, $default, $rating) . ' 3x',
    ]);
}

function threadedComments($comments, $singleCommentOptions): void
{
    $commentClass = '';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }

    $avatarSize = (int) ($singleCommentOptions->avatarSize ?? 32);
    $defaultAvatar = $singleCommentOptions->defaultAvatar ?? slowcloud_comment_default_avatar($comments);
    $rating = $comments->options->commentsAvatarRating ?? 'G';
    $avatarUrl = slowcloud_comment_avatar_url($comments, $avatarSize, $defaultAvatar, $rating);
    $avatarSrcset = !empty($singleCommentOptions->avatarHighRes)
        ? slowcloud_comment_avatar_srcset($comments, $avatarSize, $defaultAvatar, $rating)
        : '';
    $charset = $comments->options->charset ?? 'UTF-8';
    ?>
    <li itemscope itemtype="http://schema.org/UserComments" id="<?php $comments->theId(); ?>" class="comment-body<?php
    if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass;
    ?>">
        <div class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
            <span itemprop="image">
                <img
                    class="avatar"
                    loading="lazy"
                    src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, $charset); ?>"
                    <?php if ($avatarSrcset !== ''): ?>srcset="<?php echo htmlspecialchars($avatarSrcset, ENT_QUOTES, $charset); ?>"<?php endif; ?>
                    alt="<?php echo htmlspecialchars((string) $comments->author, ENT_QUOTES, $charset); ?>"
                    width="<?php echo $avatarSize; ?>"
                    height="<?php echo $avatarSize; ?>"
                />
            </span>
            <div class="slowcloud-comment-main">
                <div class="slowcloud-comment-author-main">
                    <cite class="fn" itemprop="name"><?php $singleCommentOptions->beforeAuthor();
                        $comments->author();
                        $singleCommentOptions->afterAuthor(); ?></cite>
                    <?php if ($comments->authorId && $comments->authorId == $comments->ownerId): ?>
                        <span class="slowcloud-comment-author-badge"><?php _e('作者'); ?></span>
                    <?php endif; ?>
                    <div class="comment-meta">
                        <a href="<?php $comments->permalink(); ?>">
                            <time itemprop="commentTime" datetime="<?php $comments->date('c'); ?>"><?php
                                $singleCommentOptions->beforeDate();
                                $comments->date($singleCommentOptions->dateFormat);
                                $singleCommentOptions->afterDate();
                            ?></time>
                        </a>
                        <?php if ('approved' !== $comments->status) { ?>
                            <em class="comment-awaiting-moderation"><?php $singleCommentOptions->commentStatus(); ?></em>
                        <?php } ?>
                    </div>
                </div>
                <div class="comment-content" itemprop="commentText">
                    <?php
                    ob_start();
                    $comments->content();
                    echo slowcloud_replace_owo_shortcodes($comments, (string) ob_get_clean());
                    ?>
                </div>
                <div class="comment-reply">
                    <?php $comments->reply($singleCommentOptions->replyWord); ?>
                </div>
                <?php if ($comments->children) { ?>
                    <div class="comment-children" itemprop="discusses">
                        <?php $comments->threadedComments(); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </li>
    <?php
}

function slowcloud_author_name($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $name = trim((string) ($options->authorName ?? ''));

    if ($name !== '') {
        return $name;
    }

    return 'slowcloud';
}

function slowcloud_author_bio($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $bio = trim((string) ($options->authorBio ?? ''));

    if ($bio !== '') {
        return $bio;
    }

    return '一朵慢慢飘动的云，记录轻盈又真实的日常。';
}

function slowcloud_social_links($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $platforms = [
        [
            'key' => 'githubUrl',
            'name' => 'github',
            'icon' => 'icon-slowcloudgithub',
        ],
        [
            'key' => 'bilibiliUrl',
            'name' => 'bilibili',
            'icon' => 'icon-slowcloudbilibili',
        ],
    ];

    $links = [];
    foreach ($platforms as $platform) {
        $url = trim((string) ($options->{$platform['key']} ?? ''));
        if ($url === '') {
            continue;
        }

        $links[] = [
            'name' => $platform['name'],
            'iconType' => 'class',
            'icon' => $platform['icon'],
            'url' => $url,
        ];
    }

    return array_merge($links, slowcloud_custom_platform_links($archive));
}

function slowcloud_custom_social_links($archive): array
{
    return slowcloud_svg_links_from_option($archive, 'customSocialLinks');
}

function slowcloud_custom_platform_links($archive): array
{
    return slowcloud_svg_links_from_option($archive, 'customPlatformLinks');
}

function slowcloud_svg_links_from_option($archive, string $optionName): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $raw = trim((string) ($options->{$optionName} ?? ''));
    if ($raw === '') {
        return [];
    }

    $links = [];
    foreach (slowcloud_parse_custom_social_link_entries($raw) as $entry) {
        [$name, $second, $third] = array_map('trim', $entry);
        if ($name === '') {
            continue;
        }

        $svg = stripos($second, '<svg') !== false ? $second : $third;
        $url = stripos($second, '<svg') !== false ? $third : $second;
        if (!slowcloud_is_safe_link_url($url)) {
            continue;
        }

        $svg = slowcloud_sanitize_inline_svg($svg);
        if ($svg === '') {
            continue;
        }

        $links[] = [
            'name' => $name,
            'iconType' => 'svg',
            'icon' => $svg,
            'url' => $url,
        ];
    }

    return $links;
}

function slowcloud_friend_links($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $raw = trim((string) ($options->friendLinks ?? ''));
    if ($raw === '') {
        return [];
    }

    $links = [];
    $lines = preg_split('/\r\n|\r|\n/', $raw);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '|') === false) {
            continue;
        }

        [$name, $url] = array_map('trim', explode('|', $line, 2));
        if ($name === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            continue;
        }

        $links[] = [
            'name' => $name,
            'url' => $url,
        ];
    }

    return $links;
}

function slowcloud_site_stats($archive): array
{
    static $stats = null;

    if ($stats !== null) {
        return $stats;
    }

    $db = \Typecho\Db::get();

    $postStats = $db->fetchRow($db->select([
        'COUNT(table.contents.cid)' => 'posts',
        'SUM(table.contents.commentsNum)' => 'comments',
        'MIN(table.contents.created)' => 'first_created',
    ])->from('table.contents')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish'));

    $trafficStats = slowcloud_stats_overview();

    $firstCreated = isset($postStats['first_created']) ? (int) $postStats['first_created'] : 0;
    $days = $firstCreated > 0 ? max(1, (int) floor((time() - $firstCreated) / 86400) + 1) : 0;

    $stats = [
        [
            'label' => _t('博文数量'),
            'value' => (string) (int) ($postStats['posts'] ?? 0),
        ],
        [
            'label' => _t('评论数量'),
            'value' => (string) (int) ($postStats['comments'] ?? 0),
        ],
        [
            'label' => _t('运行时间'),
            'value' => $days > 0 ? sprintf(_t('%d 天'), $days) : _t('0 天'),
        ],
        [
            'label' => _t('访问数量'),
            'value' => (string) $trafficStats['total_pv'],
        ],
    ];

    return $stats;
}

function slowcloud_capture_output(callable $callback): string
{
    ob_start();
    $callback();
    return (string) ob_get_clean();
}

function slowcloud_timeline_page(): ?array
{
    \Widget\Contents\Page\Rows::alloc()->to($pages);

    while ($pages->next()) {
        if ((string) $pages->template !== 'timeline.php') {
            continue;
        }

        return [
            'title' => (string) $pages->title,
            'permalink' => (string) $pages->permalink,
        ];
    }

    return null;
}

function slowcloud_header_menu_items($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $mode = 'combined';

    try {
        if ($mode === 'combined') {
            $items = slowcloud_auto_header_menu_items(
                $archive,
                array_values(array_unique(array_merge(
                    ['timeline'],
                    slowcloud_header_menu_sources($options->headerAutoMenuSources ?? [])
                ))),
                (int) ($options->headerLatestPostCount ?? 5)
            );
            $items['custom'] = slowcloud_custom_header_menu_items((string) ($options->headerCustomMenu ?? ''));
            return slowcloud_order_header_menu_items($items, (string) ($options->headerMenuOrder ?? ''));
        }
    } catch (\Throwable $e) {
        // Navigation must not make the site unavailable when a data source is unavailable.
    }

    return slowcloud_default_header_menu_items($archive);
}

function slowcloud_header_menu_sources($value): array
{
    if (is_array($value)) {
        return array_values(array_filter($value, 'is_string'));
    }

    $value = trim((string) $value);
    if ($value === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return array_values(array_filter($decoded, 'is_string'));
    }

    return array_values(array_filter(preg_split('/\s*,\s*/', $value) ?: [], 'strlen'));
}

function slowcloud_order_header_menu_items(array $groups, string $rawOrder): array
{
    $items = [];
    $seen = [];
    $order = preg_split('/\s*,\s*/', trim($rawOrder)) ?: [];
    foreach ($order as $key) {
        $key = trim($key);
        if ($key === '' || isset($seen[$key]) || empty($groups[$key])) continue;
        $items = array_merge($items, $groups[$key]);
        $seen[$key] = true;
    }
    foreach ($groups as $key => $group) {
        if (!isset($seen[$key]) && !empty($group)) $items = array_merge($items, $group);
    }
    return $items;
}

function slowcloud_default_header_menu_items($archive): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $items = [[
        'name' => _t('首页'),
        'url' => (string) ($options->siteUrl ?? ''),
        'children' => [],
    ]];
    $timeline = slowcloud_timeline_page();
    if ($timeline !== null) {
        $items[] = ['name' => _t('时光轴'), 'url' => $timeline['permalink'], 'children' => []];
    }

    \Widget\Contents\Page\Rows::alloc()->to($pages);
    while ($pages->next()) {
        if ((string) $pages->template === 'timeline.php') {
            continue;
        }
        $items[] = ['name' => (string) $pages->title, 'url' => (string) $pages->permalink, 'children' => []];
    }

    return $items;
}

function slowcloud_auto_header_menu_items($archive, array $sources, int $latestCount): array
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $groups = [];
    if (in_array('home', $sources, true)) {
        $groups['home'] = [['name' => _t('首页'), 'url' => (string) ($options->siteUrl ?? ''), 'children' => []]];
    }
    if (in_array('timeline', $sources, true)) {
        $timeline = slowcloud_timeline_page();
        if ($timeline !== null) {
            $groups['timeline'] = [['name' => (string) $timeline['title'], 'url' => (string) $timeline['permalink'], 'children' => []]];
        }
    }
    if (in_array('categories', $sources, true)) {
        $categoryRows = [];
        \Widget\Metas\Category\Rows::alloc()->to($categories);
        while ($categories->next()) {
            $categoryRows[] = [
                'id' => (int) ($categories->mid ?? 0),
                'parent' => (int) ($categories->parent ?? 0),
                'name' => (string) $categories->name,
                'url' => (string) $categories->permalink,
                'children' => [],
            ];
        }
        $children = slowcloud_header_menu_tree($categoryRows);
        if ($children) $groups['categories'] = [['name' => _t('分类'), 'url' => '', 'children' => $children]];
    }
    if (in_array('friend-links', $sources, true)) {
        $children = slowcloud_friend_links($archive);
        if ($children) $groups['friend-links'] = [['name' => _t('友链'), 'url' => '', 'children' => $children]];
    }
    if (in_array('social-links', $sources, true)) {
        $children = array_map(static function (array $link): array {
            return [
                'name' => $link['name'],
                'url' => $link['url'],
                'iconType' => $link['iconType'] ?? '',
                'icon' => $link['icon'] ?? '',
            ];
        }, slowcloud_social_links($archive));
        if ($children) $groups['social-links'] = [['name' => _t('社交平台'), 'url' => '', 'children' => $children]];
    }
    if (in_array('latest-posts', $sources, true)) {
        $children = [];
        \Widget\Contents\Post\Recent::alloc(['pageSize' => max(1, min(20, $latestCount))])->to($posts);
        while ($posts->next()) $children[] = ['name' => (string) $posts->title, 'url' => (string) $posts->permalink];
        if ($children) $groups['latest-posts'] = [['name' => _t('最新文章'), 'url' => '', 'children' => $children]];
    }
    if (in_array('pages', $sources, true)) {
        $groups['pages'] = array_values(array_filter(slowcloud_default_header_menu_items($archive), static function (array $item): bool {
            return $item['name'] !== _t('首页');
        }));
    }
    return $groups;
}

function slowcloud_header_menu_tree(array $items, int $parent = 0): array
{
    $tree = [];
    foreach ($items as $item) {
        if ((int) ($item['parent'] ?? 0) !== $parent) {
            continue;
        }
        $item['children'] = slowcloud_header_menu_tree($items, (int) ($item['id'] ?? 0));
        unset($item['id'], $item['parent']);
        $tree[] = $item;
    }
    return $tree;
}

function slowcloud_render_header_submenu(array $items, string $charset): void
{
    if (empty($items)) {
        return;
    }

    echo '<div class="slowcloud-site-nav__submenu">';
    foreach ($items as $item) {
        $hasChildren = !empty($item['children']);
        echo '<div class="slowcloud-site-nav__submenu-item' . ($hasChildren ? ' slowcloud-site-nav__submenu-item--has-children' : '') . '">';
        echo '<a href="' . htmlspecialchars((string) ($item['url'] ?? ''), ENT_QUOTES, $charset) . '">';
        if (($item['iconType'] ?? '') === 'svg' && ($item['icon'] ?? '') !== '') {
            echo '<span class="slowcloud-site-nav__icon slowcloud-site-nav__icon--svg" aria-hidden="true">' . $item['icon'] . '</span>';
        } elseif (($item['iconType'] ?? '') === 'class' && ($item['icon'] ?? '') !== '') {
            echo '<span class="iconfont slowcloud-site-nav__icon ' . htmlspecialchars((string) $item['icon'], ENT_QUOTES, $charset) . '" aria-hidden="true"></span>';
        }
        echo htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, $charset);
        if ($hasChildren) {
            echo '<span class="slowcloud-site-nav__arrow" aria-hidden="true"></span>';
        }
        echo '</a>';
        if ($hasChildren) {
            slowcloud_render_header_submenu($item['children'], $charset);
        }
        echo '</div>';
    }
    echo '</div>';
}

function slowcloud_custom_header_menu_items(string $raw): array
{
    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) !== 2 || $parts[0] === '' || !slowcloud_is_safe_link_url($parts[1])) continue;
        $names = array_map('trim', explode('>', $parts[0], 2));
        if (count($names) === 1 || $names[1] === '') {
            $items[] = ['name' => $names[0], 'url' => $parts[1], 'children' => []];
            continue;
        }
        $parent = $names[0];
        $index = null;
        foreach ($items as $key => $item) if ($item['name'] === $parent) { $index = $key; break; }
        if ($index === null) {
            $items[] = ['name' => $parent, 'url' => '', 'children' => []];
            $index = count($items) - 1;
        }
        $items[$index]['children'][] = ['name' => $names[1], 'url' => $parts[1]];
    }
    return $items;
}

function slowcloud_timeline_link($archive): string
{
    $page = slowcloud_timeline_page();
    if ($page !== null) {
        return $page['permalink'];
    }

    return rtrim((string) (($archive->options ?? \Widget\Options::alloc())->siteUrl ?? ''), '/');
}

function slowcloud_timeline_summary_text($post): string
{
    $summary = slowcloud_capture_output(static function () use ($post): void {
        $post->excerpt(90, '');
    });

    $summary = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($summary, ENT_QUOTES, 'UTF-8'))));
    return $summary;
}

function slowcloud_timeline_category_text($post): string
{
    $category = slowcloud_capture_output(static function () use ($post): void {
        $post->category(' / ', false, _t('未分类'));
    });

    return trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($category, ENT_QUOTES, 'UTF-8'))));
}

function slowcloud_primary_category($archive): string
{
    $categories = $archive->categories ?? [];
    if (empty($categories)) {
        return '';
    }

    foreach ($categories as $category) {
        if ((int) ($category['parent'] ?? 0) > 0) {
            return trim((string) ($category['name'] ?? ''));
        }
    }

    $lastCategory = end($categories);
    return trim((string) (($lastCategory['name'] ?? '') ?: ($categories[0]['name'] ?? '')));
}

function slowcloud_timeline_data(): array
{
    $years = [];
    $stats = [
        'total' => 0,
        'months' => 0,
        'latest' => null,
        'earliest' => null,
    ];
    $monthSet = [];

    \Widget\Contents\Post\Recent::alloc(['pageSize' => 10000])->to($posts);

    while ($posts->next()) {
        $yearKey = $posts->date->format('Y');
        $monthKey = $posts->date->format('Y-m');

        if (!isset($years[$yearKey])) {
            $years[$yearKey] = [
                'year' => $yearKey,
                'months' => [],
            ];
        }

        if (!isset($years[$yearKey]['months'][$monthKey])) {
            $years[$yearKey]['months'][$monthKey] = [
                'key' => $monthKey,
                'label' => $posts->date->format('m月'),
                'items' => [],
            ];
        }

        $created = (int) $posts->created;
        $summary = slowcloud_timeline_summary_text($posts);
        $poster = slowcloud_poster($posts);
        $posterAlt = slowcloud_poster_alt($posts);
        $years[$yearKey]['months'][$monthKey]['items'][] = [
            'title' => (string) $posts->title,
            'permalink' => (string) $posts->permalink,
            'date' => $posts->date->format('m-d'),
            'datetime' => $posts->date->format('c'),
            'category' => slowcloud_timeline_category_text($posts),
            'views' => slowcloud_views($posts),
            'summary' => $summary,
            'poster' => $poster,
            'poster_alt' => $posterAlt,
        ];

        $stats['total']++;
        $monthSet[$monthKey] = true;
        $stats['latest'] = $stats['latest'] === null ? $created : max($stats['latest'], $created);
        $stats['earliest'] = $stats['earliest'] === null ? $created : min($stats['earliest'], $created);
    }

    $stats['months'] = count($monthSet);

    foreach ($years as &$year) {
        $year['months'] = array_values($year['months']);
    }
    unset($year);

    return [
        'years' => array_values($years),
        'stats' => $stats,
    ];
}

function slowcloud_tab_title($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $tabTitle = trim((string) ($options->tabTitle ?? ''));

    if ($tabTitle !== '') {
        return $tabTitle;
    }

    return 'slowcloud';
}

function slowcloud_header_background($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $background = trim((string) ($options->headerBackgroundUrl ?? ''));

    if ($background !== '') {
        return $background;
    }

    return slowcloud_theme_asset_url('usr/themes/slowcloud/assets/img/xiyang5.png', $archive);
}

function slowcloud_logo_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $logoUrl = trim((string) ($options->logoUrl ?? ''));

    if ($logoUrl !== '') {
        return $logoUrl;
    }

    return slowcloud_theme_asset_url('usr/themes/slowcloud/assets/img/head.png', $archive);
}

function slowcloud_header_height($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $height = trim((string) ($options->headerHeight ?? '120px'));

    if ($height === '') {
        return '120px';
    }

    if (preg_match('/^\d+(\.\d+)?(px|vh|vw|rem|em|%)$/', $height)) {
        return $height;
    }

    return '120px';
}

function slowcloud_site_width($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $width = trim((string) ($options->siteWidth ?? '1200px'));

    if ($width === '') {
        return '1200px';
    }

    if (preg_match('/^\d+(\.\d+)?(px|vw|vh|rem|em|%)$/', $width)) {
        return $width;
    }

    return '1200px';
}

function slowcloud_column_background($archive, string $column): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $defaults = [
        'left' => 'rgba(255, 255, 255, 0.82)',
        'center' => 'rgba(255, 255, 255, 0.62)',
        'right' => 'rgba(255, 255, 255, 0.74)',
    ];
    $fields = [
        'left' => 'leftColumnBg',
        'center' => 'centerColumnBg',
        'right' => 'rightColumnBg',
    ];

    $default = $defaults[$column] ?? 'transparent';
    $field = $fields[$column] ?? null;
    if ($field === null) {
        return $default;
    }

    $value = trim((string) ($options->{$field} ?? ''));
    return $value !== '' ? $value : $default;
}

function slowcloud_main_background($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $value = trim((string) ($options->mainBackground ?? ''));

    return $value !== '' ? $value : 'transparent';
}

function slowcloud_stats_table(string $suffix): string
{
    return \Typecho\Db::get()->getPrefix() . 'slowcloud_' . $suffix;
}

function slowcloud_stats_option_name(string $name): string
{
    $map = [
        'stats_schema_version' => 'sc_stats_ver',
    ];

    return $map[$name] ?? ('sc_' . substr(sha1($name), 0, 20));
}

function slowcloud_stats_get_option(string $name): ?string
{
    $db = \Typecho\Db::get();
    $row = $db->fetchRow($db->select('value')
        ->from('table.options')
        ->where('name = ? AND user = ?', slowcloud_stats_option_name($name), 0)
        ->limit(1));

    return $row !== null && isset($row['value']) ? (string) $row['value'] : null;
}

function slowcloud_stats_set_option(string $name, string $value): void
{
    $db = \Typecho\Db::get();
    $optionName = slowcloud_stats_option_name($name);
    $exists = $db->fetchRow($db->select('name')
        ->from('table.options')
        ->where('name = ? AND user = ?', $optionName, 0)
        ->limit(1));

    if ($exists) {
        $db->query($db->update('table.options')
            ->rows(['value' => $value])
            ->where('name = ? AND user = ?', $optionName, 0));
        return;
    }

    $db->query($db->insert('table.options')->rows([
        'name' => $optionName,
        'user' => 0,
        'value' => $value,
    ]));
}

function slowcloud_stats_storage_version(): string
{
    return '2';
}

function slowcloud_ensure_stats_storage(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $ensured = true;
    if (slowcloud_stats_get_option('stats_schema_version') === slowcloud_stats_storage_version()) {
        return;
    }

    slowcloud_create_stats_tables();
    slowcloud_migrate_stats_tables();
    slowcloud_stats_set_option('stats_schema_version', slowcloud_stats_storage_version());
}

function slowcloud_stats_storage_ready(): bool
{
    return slowcloud_stats_get_option('stats_schema_version') === slowcloud_stats_storage_version();
}

function slowcloud_stats_column_exists(string $table, string $column): bool
{
    $db = \Typecho\Db::get();
    $adapter = $db->getAdapterName();

    try {
        if (stripos($adapter, 'SQLite') !== false) {
            $rows = $db->fetchAll("PRAGMA table_info({$table})");
            foreach ((array) $rows as $row) {
                if (strcasecmp((string) ($row['name'] ?? ''), $column) === 0) {
                    return true;
                }
            }

            return false;
        }

        if (stripos($adapter, 'Pgsql') !== false) {
            $safeTable = str_replace("'", "''", $table);
            $safeColumn = str_replace("'", "''", $column);
            $row = $db->fetchRow("SELECT column_name FROM information_schema.columns WHERE table_name = '{$safeTable}' AND column_name = '{$safeColumn}' LIMIT 1");

            return !empty($row);
        }

        $safeTable = str_replace('`', '``', $table);
        $safeColumn = str_replace("'", "''", $column);
        $row = $db->fetchRow("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");

        return !empty($row);
    } catch (\Throwable $e) {
        return false;
    }
}

function slowcloud_migrate_stats_tables(): void
{
    $db = \Typecho\Db::get();
    $adapter = $db->getAdapterName();
    $visitsTable = slowcloud_stats_table('visits');

    if (slowcloud_stats_column_exists($visitsTable, 'is_counted')) {
        return;
    }

    if (stripos($adapter, 'SQLite') !== false || stripos($adapter, 'Pgsql') !== false) {
        $db->query("ALTER TABLE {$visitsTable} ADD COLUMN is_counted int NOT NULL DEFAULT 1");
        return;
    }

    $db->query("ALTER TABLE `{$visitsTable}` ADD COLUMN `is_counted` tinyint(1) unsigned NOT NULL DEFAULT 1");
}

function slowcloud_create_stats_tables(): void
{
    $db = \Typecho\Db::get();
    $adapter = $db->getAdapterName();
    $dailyTable = slowcloud_stats_table('stats_daily');
    $visitorsTable = slowcloud_stats_table('visitors');
    $visitsTable = slowcloud_stats_table('visits');

    if (stripos($adapter, 'SQLite') !== false) {
        $queries = [
            "CREATE TABLE IF NOT EXISTS {$dailyTable} (
                stat_date varchar(10) NOT NULL PRIMARY KEY,
                pv int(10) NOT NULL DEFAULT 0,
                uv int(10) NOT NULL DEFAULT 0,
                created int(10) NOT NULL DEFAULT 0,
                modified int(10) NOT NULL DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS {$visitorsTable} (
                id INTEGER NOT NULL PRIMARY KEY,
                visitor_id varchar(64) NOT NULL,
                stat_date varchar(10) NOT NULL,
                first_visit int(10) NOT NULL DEFAULT 0,
                last_visit int(10) NOT NULL DEFAULT 0,
                ip varchar(64) DEFAULT NULL,
                ip_hash varchar(64) DEFAULT NULL,
                user_agent varchar(511) DEFAULT NULL,
                referer varchar(255) DEFAULT NULL,
                path varchar(255) DEFAULT NULL,
                page_type varchar(32) DEFAULT NULL
            )",
            "CREATE UNIQUE INDEX IF NOT EXISTS {$visitorsTable}_visitor_date ON {$visitorsTable} (visitor_id, stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitorsTable}_stat_date ON {$visitorsTable} (stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitorsTable}_last_visit ON {$visitorsTable} (last_visit)",
            "CREATE TABLE IF NOT EXISTS {$visitsTable} (
                id INTEGER NOT NULL PRIMARY KEY,
                visitor_id varchar(64) NOT NULL,
                stat_date varchar(10) NOT NULL,
                visited_at int(10) NOT NULL DEFAULT 0,
                ip varchar(64) DEFAULT NULL,
                ip_hash varchar(64) DEFAULT NULL,
                user_agent varchar(511) DEFAULT NULL,
                referer varchar(255) DEFAULT NULL,
                path varchar(255) DEFAULT NULL,
                is_counted int(1) NOT NULL DEFAULT 1,
                page_type varchar(32) DEFAULT NULL
            )",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_stat_date ON {$visitsTable} (stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_visited_at ON {$visitsTable} (visited_at)",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_ip_hash ON {$visitsTable} (ip_hash)",
        ];
    } elseif (stripos($adapter, 'Pgsql') !== false) {
        $queries = [
            "CREATE TABLE IF NOT EXISTS {$dailyTable} (
                stat_date varchar(10) NOT NULL PRIMARY KEY,
                pv int NOT NULL DEFAULT 0,
                uv int NOT NULL DEFAULT 0,
                created int NOT NULL DEFAULT 0,
                modified int NOT NULL DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS {$visitorsTable} (
                id SERIAL PRIMARY KEY,
                visitor_id varchar(64) NOT NULL,
                stat_date varchar(10) NOT NULL,
                first_visit int NOT NULL DEFAULT 0,
                last_visit int NOT NULL DEFAULT 0,
                ip varchar(64) DEFAULT NULL,
                ip_hash varchar(64) DEFAULT NULL,
                user_agent varchar(511) DEFAULT NULL,
                referer varchar(255) DEFAULT NULL,
                path varchar(255) DEFAULT NULL,
                page_type varchar(32) DEFAULT NULL
            )",
            "CREATE UNIQUE INDEX IF NOT EXISTS {$visitorsTable}_visitor_date ON {$visitorsTable} (visitor_id, stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitorsTable}_stat_date ON {$visitorsTable} (stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitorsTable}_last_visit ON {$visitorsTable} (last_visit)",
            "CREATE TABLE IF NOT EXISTS {$visitsTable} (
                id SERIAL PRIMARY KEY,
                visitor_id varchar(64) NOT NULL,
                stat_date varchar(10) NOT NULL,
                visited_at int NOT NULL DEFAULT 0,
                ip varchar(64) DEFAULT NULL,
                ip_hash varchar(64) DEFAULT NULL,
                user_agent varchar(511) DEFAULT NULL,
                referer varchar(255) DEFAULT NULL,
                path varchar(255) DEFAULT NULL,
                is_counted int NOT NULL DEFAULT 1,
                page_type varchar(32) DEFAULT NULL
            )",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_stat_date ON {$visitsTable} (stat_date)",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_visited_at ON {$visitsTable} (visited_at)",
            "CREATE INDEX IF NOT EXISTS {$visitsTable}_ip_hash ON {$visitsTable} (ip_hash)",
        ];
    } else {
        $queries = [
            "CREATE TABLE IF NOT EXISTS `{$dailyTable}` (
                `stat_date` varchar(10) NOT NULL,
                `pv` int(10) unsigned NOT NULL DEFAULT 0,
                `uv` int(10) unsigned NOT NULL DEFAULT 0,
                `created` int(10) unsigned NOT NULL DEFAULT 0,
                `modified` int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`stat_date`)
            )",
            "CREATE TABLE IF NOT EXISTS `{$visitorsTable}` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `visitor_id` varchar(64) NOT NULL,
                `stat_date` varchar(10) NOT NULL,
                `first_visit` int(10) unsigned NOT NULL DEFAULT 0,
                `last_visit` int(10) unsigned NOT NULL DEFAULT 0,
                `ip` varchar(64) DEFAULT NULL,
                `ip_hash` varchar(64) DEFAULT NULL,
                `user_agent` varchar(511) DEFAULT NULL,
                `referer` varchar(255) DEFAULT NULL,
                `path` varchar(255) DEFAULT NULL,
                `page_type` varchar(32) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `visitor_date` (`visitor_id`, `stat_date`),
                KEY `stat_date` (`stat_date`),
                KEY `last_visit` (`last_visit`)
            )",
            "CREATE TABLE IF NOT EXISTS `{$visitsTable}` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `visitor_id` varchar(64) NOT NULL,
                `stat_date` varchar(10) NOT NULL,
                `visited_at` int(10) unsigned NOT NULL DEFAULT 0,
                `ip` varchar(64) DEFAULT NULL,
                `ip_hash` varchar(64) DEFAULT NULL,
                `user_agent` varchar(511) DEFAULT NULL,
                `referer` varchar(255) DEFAULT NULL,
                `path` varchar(255) DEFAULT NULL,
                `is_counted` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `page_type` varchar(32) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `stat_date` (`stat_date`),
                KEY `visited_at` (`visited_at`),
                KEY `ip_hash` (`ip_hash`)
            )",
        ];
    }

    foreach ($queries as $query) {
        $db->query($query);
    }
}

function slowcloud_generate_visitor_id(): string
{
    try {
        return bin2hex(random_bytes(16));
    } catch (\Throwable $e) {
        return sha1(uniqid('slowcloud_', true));
    }
}

function slowcloud_stats_visitor_id(): string
{
    $cookieKey = 'slowcloud_visitor_id';
    $visitorId = trim((string) \Typecho\Cookie::get($cookieKey, ''));

    if ($visitorId !== '') {
        return $visitorId;
    }

    $visitorId = slowcloud_generate_visitor_id();
    \Typecho\Cookie::set($cookieKey, $visitorId, time() + 315360000);

    return $visitorId;
}

function slowcloud_safe_theme_file($archive): string
{
    if (!is_object($archive) || !method_exists($archive, 'getThemeFile')) {
        return '';
    }

    try {
        return (string) ($archive->getThemeFile() ?? '');
    } catch (\Throwable $e) {
        return '';
    }
}

function slowcloud_detect_page_type_hint($archive): string
{
    if (!is_object($archive) || !method_exists($archive, 'is')) {
        return '';
    }

    foreach ([
        'feed',
        'front',
        'index',
        'post',
        'page',
        'category',
        'tag',
        'search',
        'author',
        'date',
        'archive',
    ] as $type) {
        if ($archive->is($type)) {
            return $type;
        }
    }

    return '';
}

function slowcloud_set_stats_context($archive): void
{
    $GLOBALS['slowcloud_stats_context'] = [
        'page_type' => slowcloud_detect_page_type_hint($archive),
        'theme_file' => slowcloud_safe_theme_file($archive),
    ];
}

function slowcloud_get_stats_context(): array
{
    return isset($GLOBALS['slowcloud_stats_context']) && is_array($GLOBALS['slowcloud_stats_context'])
        ? $GLOBALS['slowcloud_stats_context']
        : [];
}

function slowcloud_is_excluded_stats_path(string $path): bool
{
    $path = trim(strtolower($path));

    if ($path === '') {
        return false;
    }

    foreach ([
        '/action/',
        '/admin/',
        '/install/',
        '/var/',
        '/usr/',
    ] as $prefix) {
        if (strpos($path, $prefix) === 0) {
            return true;
        }
    }

    return false;
}

function slowcloud_stats_track_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $index = rtrim((string) $options->index, '/');

    return (substr($index, -4) === '.php' ? $index : $index . '/') . '?slowcloud_stats_track=1';
}

function slowcloud_stats_base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function slowcloud_stats_base64url_decode(string $value): ?string
{
    $value = strtr($value, '-_', '+/');
    $padding = strlen($value) % 4;
    if ($padding !== 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($value, true);
    return $decoded === false ? null : $decoded;
}

function slowcloud_stats_token_secret(): string
{
    static $secret = null;

    if (is_string($secret) && $secret !== '') {
        return $secret;
    }

    $secret = (string) (slowcloud_stats_get_option('track_token_secret') ?? '');
    if ($secret !== '') {
        return $secret;
    }

    try {
        $secret = bin2hex(random_bytes(32));
    } catch (\Throwable $e) {
        $secret = sha1(uniqid('slowcloud_stats_', true) . microtime(true));
    }

    slowcloud_stats_set_option('track_token_secret', $secret);
    return $secret;
}

function slowcloud_stats_track_token($archive): string
{
    $context = slowcloud_get_stats_context();
    $visitorId = (string) ($context['visitor_id'] ?? slowcloud_stats_visitor_id());
    $payload = slowcloud_stats_payload($archive);

    if ($visitorId === '' || !slowcloud_is_valid_front_page($payload)) {
        return '';
    }

    $pageType = (string) ($payload['page_type'] ?? '');
    $cid = $pageType === 'post' && isset($archive->cid) ? (int) $archive->cid : 0;
    $data = [
        'v' => hash('sha256', $visitorId),
        'p' => (string) ($payload['path'] ?? ''),
        't' => $pageType,
        'f' => (string) ($payload['theme_file'] ?? ''),
        'c' => $cid,
        'e' => time() + 300,
    ];
    $encoded = slowcloud_stats_base64url_encode((string) json_encode($data, JSON_UNESCAPED_SLASHES));
    $signature = hash_hmac('sha256', $encoded, slowcloud_stats_token_secret(), true);

    return $encoded . '.' . slowcloud_stats_base64url_encode($signature);
}

function slowcloud_stats_track_token_payload(string $token): ?array
{
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        return null;
    }

    $signature = slowcloud_stats_base64url_decode($parts[1]);
    $expected = hash_hmac('sha256', $parts[0], slowcloud_stats_token_secret(), true);
    $json = slowcloud_stats_base64url_decode($parts[0]);
    $payload = $json === null ? null : json_decode($json, true);

    if (!is_string($signature) || !hash_equals($expected, $signature) || !is_array($payload)) {
        return null;
    }

    $expiresAt = (int) ($payload['e'] ?? 0);
    if ($expiresAt < time() || $expiresAt > time() + 600) {
        return null;
    }

    foreach (['v', 'p', 't', 'f'] as $key) {
        if (!isset($payload[$key]) || !is_string($payload[$key])) {
            return null;
        }
    }

    return $payload;
}

function slowcloud_is_valid_front_page($archive): bool
{
    if (is_array($archive)) {
        $pageType = (string) ($archive['page_type'] ?? '');
        $themeFile = (string) ($archive['theme_file'] ?? '');

        if ($pageType === 'feed') {
            return false;
        }

        if ($themeFile === '404.php') {
            return false;
        }

        return $pageType !== '' && $pageType !== 'attachment';
    }

    if (!is_object($archive) || !method_exists($archive, 'is')) {
        return false;
    }

    if ($archive->is('feed')) {
        return false;
    }

    $archiveType = method_exists($archive, 'getArchiveType') ? (string) ($archive->getArchiveType() ?? '') : '';
    $archiveSlug = method_exists($archive, 'getArchiveSlug') ? (string) ($archive->getArchiveSlug() ?? '') : '';
    $archiveUrl = method_exists($archive, 'getArchiveUrl') ? trim((string) ($archive->getArchiveUrl() ?? '')) : '';
    $themeFile = method_exists($archive, 'getThemeFile') ? (string) ($archive->getThemeFile() ?? '') : '';

    if ($themeFile === '404.php' || $archiveSlug === '404') {
        return false;
    }

    if ($archiveType === '' || $archiveUrl === '' || $themeFile === '') {
        return false;
    }

    if ($archiveType === 'attachment') {
        return false;
    }

    if (isset($archive->hidden) && (bool) $archive->hidden) {
        return false;
    }

    if (in_array($archiveType, ['post', 'page'], true)) {
        $cid = isset($archive->cid) ? (int) $archive->cid : 0;
        $status = isset($archive->status) ? (string) $archive->status : '';

        if ($cid <= 0 || ($status !== '' && $status !== 'publish')) {
            return false;
        }
    }

    return true;
}

function slowcloud_should_record_visit($archive): bool
{
    $request = $archive->request ?? \Widget\Options::alloc()->request;
    $user = \Widget\User::alloc();

    if (php_sapi_name() === 'cli') {
        return false;
    }

    if ($user->hasLogin() && $user->pass('administrator', true)) {
        return false;
    }

    $requestMethod = strtoupper((string) $request->getServer('REQUEST_METHOD', 'GET'));
    if ($requestMethod !== 'GET') {
        return false;
    }

    if (method_exists($request, 'isAjax') && $request->isAjax()) {
        return false;
    }

    if (method_exists($request, 'isJson') && $request->isJson()) {
        return false;
    }

    $accept = strtolower((string) $request->getServer('HTTP_ACCEPT', ''));
    if (
        $accept !== ''
        && strpos($accept, 'text/html') === false
        && strpos($accept, 'application/xhtml+xml') === false
        && strpos($accept, '*/*') === false
    ) {
        return false;
    }

    $purpose = strtolower((string) $request->getServer('HTTP_PURPOSE', ''));
    $secPurpose = strtolower((string) $request->getServer('HTTP_SEC_PURPOSE', ''));
    if (
        strpos($purpose, 'prefetch') !== false
        || strpos($purpose, 'prerender') !== false
        || strpos($secPurpose, 'prefetch') !== false
        || strpos($secPurpose, 'prerender') !== false
    ) {
        return false;
    }

    $secFetchDest = strtolower((string) $request->getServer('HTTP_SEC_FETCH_DEST', ''));
    if ($secFetchDest !== '' && $secFetchDest !== 'document') {
        return false;
    }

    $path = (string) ($request->getPathInfo() ?? '');
    if (slowcloud_is_excluded_stats_path($path)) {
        return false;
    }

    return slowcloud_is_valid_front_page($archive);
}

function slowcloud_is_bot_user_agent(string $userAgent): bool
{
    $userAgent = strtolower($userAgent);
    $botKeywords = [
        'bot',
        'spider',
        'crawler',
        'crawl',
        'slurp',
        'curl',
        'wget',
        'python-requests',
        'httpclient',
        'http-client',
        'libwww-perl',
        'go-http-client',
        'java/',
        'okhttp',
        'scrapy',
        'googlebot',
        'bingbot',
        'baiduspider',
        'sogou',
        '360spider',
        'bytespider',
        'petalbot',
        'yandexbot',
        'duckduckbot',
        'applebot',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'telegrambot',
        'discordbot',
        'slackbot',
        'ahrefsbot',
        'semrushbot',
        'mj12bot',
        'dotbot',
        'siteauditbot',
        'serpstatbot',
        'seekportbot',
        'blexbot',
        'ccbot',
        'gptbot',
        'chatgpt-user',
        'claudebot',
        'perplexitybot',
        'anthropic-ai',
        'google-extended',
    ];

    foreach ($botKeywords as $needle) {
        if ($userAgent !== '' && strpos($userAgent, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function slowcloud_should_count_visit($archive): bool
{
    $request = $archive->request ?? \Widget\Options::alloc()->request;
    $requestMethod = strtoupper((string) $request->getServer('REQUEST_METHOD', 'GET'));
    if ($requestMethod !== 'GET') {
        return false;
    }

    $accept = strtolower((string) $request->getServer('HTTP_ACCEPT', ''));
    if (
        $accept !== ''
        && strpos($accept, 'text/html') === false
        && strpos($accept, 'application/xhtml+xml') === false
        && strpos($accept, '*/*') === false
    ) {
        return false;
    }

    $purpose = strtolower((string) $request->getServer('HTTP_PURPOSE', ''));
    $secPurpose = strtolower((string) $request->getServer('HTTP_SEC_PURPOSE', ''));
    if (
        strpos($purpose, 'prefetch') !== false
        || strpos($purpose, 'prerender') !== false
        || strpos($secPurpose, 'prefetch') !== false
        || strpos($secPurpose, 'prerender') !== false
    ) {
        return false;
    }

    $secFetchDest = strtolower((string) $request->getServer('HTTP_SEC_FETCH_DEST', ''));
    if ($secFetchDest !== '' && $secFetchDest !== 'document') {
        return false;
    }

    $userAgent = strtolower((string) $request->getServer('HTTP_USER_AGENT', ''));
    if ($userAgent === '' || slowcloud_is_bot_user_agent($userAgent)) {
        return false;
    }

    return true;
}

function slowcloud_should_count_visit_request($request): bool
{
    $purpose = strtolower((string) $request->getServer('HTTP_PURPOSE', ''));
    $secPurpose = strtolower((string) $request->getServer('HTTP_SEC_PURPOSE', ''));
    if (
        strpos($purpose, 'prefetch') !== false
        || strpos($purpose, 'prerender') !== false
        || strpos($secPurpose, 'prefetch') !== false
        || strpos($secPurpose, 'prerender') !== false
    ) {
        return false;
    }

    $userAgent = strtolower((string) $request->getServer('HTTP_USER_AGENT', ''));
    if ($userAgent === '' || slowcloud_is_bot_user_agent($userAgent)) {
        return false;
    }

    return true;
}

function slowcloud_should_track_request($archive): bool
{
    return slowcloud_should_record_visit($archive) && slowcloud_should_count_visit($archive);
}

function slowcloud_stats_page_type($archive): string
{
    if (is_array($archive)) {
        $pageType = trim((string) ($archive['page_type'] ?? ''));

        if ($pageType !== '') {
            return $pageType;
        }
    }

    if (is_object($archive) && method_exists($archive, 'getArchiveType')) {
        $archiveType = trim((string) ($archive->getArchiveType() ?? ''));

        if ($archiveType !== '') {
            return $archiveType;
        }
    }

    return 'other';
}

function slowcloud_stats_payload($archive): array
{
    $context = slowcloud_get_stats_context();
    $request = $archive->request ?? \Widget\Options::alloc()->request;

    return [
        'page_type' => slowcloud_stats_page_type($context !== [] ? $context : $archive),
        'theme_file' => (string) ($context['theme_file'] ?? slowcloud_safe_theme_file($archive)),
        'path' => substr((string) $request->getRequestUrl(), 0, 255),
    ];
}

function slowcloud_update_daily_stats(string $statDate, int $time, bool $increaseUv): void
{
    $db = \Typecho\Db::get();
    $table = slowcloud_stats_table('stats_daily');
    $row = $db->fetchRow($db->select()
        ->from($table)
        ->where('stat_date = ?', $statDate)
        ->limit(1));

    if ($row) {
        $db->query($db->update($table)->rows([
            'pv' => (int) ($row['pv'] ?? 0) + 1,
            'uv' => (int) ($row['uv'] ?? 0) + ($increaseUv ? 1 : 0),
            'modified' => $time,
        ])->where('stat_date = ?', $statDate));
        return;
    }

    $db->query($db->insert($table)->rows([
        'stat_date' => $statDate,
        'pv' => 1,
        'uv' => $increaseUv ? 1 : 0,
        'created' => $time,
        'modified' => $time,
    ]));
}

function slowcloud_record_site_visit($request, array $statsTarget, ?string $pathOverride = null, ?bool $isCountedOverride = null, ?int $postCid = null): bool
{
    if (!slowcloud_stats_storage_ready()) {
        return false;
    }

    $db = \Typecho\Db::get();
    $time = \Typecho\Date::time();
    $statDate = (new \Typecho\Date($time))->format('Y-m-d');
    $visitorId = slowcloud_stats_visitor_id();
    $path = substr((string) ($pathOverride !== null && $pathOverride !== '' ? $pathOverride : $request->getRequestUrl()), 0, 255);
    $pageType = slowcloud_stats_page_type($statsTarget);
    $ip = substr((string) $request->getIp(), 0, 64);
    $ipHash = sha1($ip);
    $userAgent = substr((string) $request->getServer('HTTP_USER_AGENT', ''), 0, 511);
    $referer = substr((string) ($request->getReferer() ?? ''), 0, 255);
    $isCounted = $isCountedOverride ?? slowcloud_should_count_visit($statsTarget);

    $db->query($db->insert(slowcloud_stats_table('visits'))->rows([
        'visitor_id' => $visitorId,
        'stat_date' => $statDate,
        'visited_at' => $time,
        'ip' => $ip,
        'ip_hash' => $ipHash,
        'user_agent' => $userAgent,
        'referer' => $referer,
        'path' => $path,
        'is_counted' => $isCounted ? 1 : 0,
        'page_type' => $pageType,
    ]));

    if (!$isCounted) {
        return true;
    }

    $visitorsTable = slowcloud_stats_table('visitors');
    $visitorRow = $db->fetchRow($db->select()
        ->from($visitorsTable)
        ->where('visitor_id = ? AND stat_date = ?', $visitorId, $statDate)
        ->limit(1));

    $increaseUv = !$visitorRow;
    if ($visitorRow) {
        $db->query($db->update($visitorsTable)->rows([
            'last_visit' => $time,
            'ip' => $ip,
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'path' => $path,
            'page_type' => $pageType,
        ])->where('id = ?', $visitorRow['id']));
    } else {
        $db->query($db->insert($visitorsTable)->rows([
            'visitor_id' => $visitorId,
            'stat_date' => $statDate,
            'first_visit' => $time,
            'last_visit' => $time,
            'ip' => $ip,
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'path' => $path,
            'page_type' => $pageType,
        ]));
    }

    slowcloud_update_daily_stats($statDate, $time, $increaseUv);

    if ($pageType === 'post' && $postCid !== null) {
        slowcloud_record_post_view_by_cid($postCid);
    }

    return true;
}

function slowcloud_stats_is_same_origin_request($request): bool
{
    $options = \Widget\Options::alloc();
    $site = parse_url((string) $options->index);
    $siteScheme = strtolower((string) ($site['scheme'] ?? ''));
    $siteHost = strtolower((string) ($site['host'] ?? ''));
    $sitePort = (int) ($site['port'] ?? ($siteScheme === 'https' ? 443 : 80));
    $origin = trim((string) $request->getServer('HTTP_ORIGIN', ''));
    $referer = trim((string) $request->getServer('HTTP_REFERER', ''));
    $fetchSite = strtolower(trim((string) $request->getServer('HTTP_SEC_FETCH_SITE', '')));

    if ($fetchSite !== '' && $fetchSite !== 'same-origin') {
        return false;
    }

    foreach ([$origin, $referer] as $url) {
        if ($url === '') {
            continue;
        }

        $source = parse_url($url);
        $scheme = strtolower((string) ($source['scheme'] ?? ''));
        $host = strtolower((string) ($source['host'] ?? ''));
        $port = (int) ($source['port'] ?? ($scheme === 'https' ? 443 : 80));
        if (
            $scheme === ''
            || $host === ''
            || $siteScheme === ''
            || $siteHost === ''
            || !hash_equals($siteScheme, $scheme)
            || !hash_equals($siteHost, $host)
            || $sitePort !== $port
        ) {
            return false;
        }
    }

    return true;
}

function slowcloud_stats_recent_duplicate(string $visitorId, string $path, int $time, int $windowSeconds = 10): bool
{
    $db = \Typecho\Db::get();
    $row = $db->fetchRow($db->select('id')
        ->from(slowcloud_stats_table('visits'))
        ->where('visitor_id = ? AND path = ? AND is_counted = ?', $visitorId, $path, 1)
        ->where('visited_at >= ?', $time - max(1, $windowSeconds))
        ->limit(1));

    return !empty($row);
}

function slowcloud_track_site_visit_from_request($request): bool
{
    try {
        if (!slowcloud_stats_is_same_origin_request($request) || !slowcloud_should_count_visit_request($request)) {
            return false;
        }

        $payload = slowcloud_stats_track_token_payload(trim((string) $request->get('token', '')));
        if ($payload === null) {
            return false;
        }

        $visitorId = trim((string) \Typecho\Cookie::get('slowcloud_visitor_id', ''));
        $path = substr(trim((string) ($payload['p'] ?? '')), 0, 255);
        $pageType = trim((string) ($payload['t'] ?? ''));
        $themeFile = trim((string) ($payload['f'] ?? ''));
        $postCid = $pageType === 'post' ? (int) ($payload['c'] ?? 0) : 0;

        if (
            $visitorId === ''
            || !hash_equals((string) ($payload['v'] ?? ''), hash('sha256', $visitorId))
            || $path === ''
            || slowcloud_is_excluded_stats_path((string) parse_url($path, PHP_URL_PATH))
            || !slowcloud_is_valid_front_page(['page_type' => $pageType, 'theme_file' => $themeFile])
            || ($pageType === 'post' && $postCid <= 0)
            || slowcloud_stats_recent_duplicate($visitorId, $path, \Typecho\Date::time())
        ) {
            return false;
        }

        return slowcloud_record_site_visit($request, [
            'page_type' => $pageType,
            'theme_file' => $themeFile,
        ], $path, true, $postCid > 0 ? $postCid : null);
    } catch (\Throwable $e) {
        return false;
    }
}

function slowcloud_record_excluded_bot_visit($archive): void
{
    static $recorded = false;

    $context = slowcloud_get_stats_context();
    $statsTarget = $context !== [] ? $context : $archive;

    if ($recorded || !slowcloud_should_record_visit($statsTarget)) {
        return;
    }

    $request = $archive->request ?? \Widget\Options::alloc()->request;
    if (!slowcloud_is_bot_user_agent((string) $request->getServer('HTTP_USER_AGENT', ''))) {
        return;
    }

    try {
        $payload = slowcloud_stats_payload($archive);
        $recorded = slowcloud_record_site_visit($request, [
            'page_type' => (string) ($payload['page_type'] ?? ''),
            'theme_file' => (string) ($payload['theme_file'] ?? ''),
        ], (string) ($payload['path'] ?? ''), false);
    } catch (\Throwable $e) {
    }
}

function slowcloud_track_site_visit($archive): void
{
    $context = slowcloud_get_stats_context();
    $statsTarget = $context !== [] ? $context : $archive;

    if (!slowcloud_should_track_request($statsTarget)) {
        return;
    }

    $trackUrl = slowcloud_stats_track_url($archive);
    $token = slowcloud_stats_track_token($archive);
    if ($token === '') {
        return;
    }
    ?>
<script>
(function () {
    var url = <?php echo json_encode($trackUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    var body = new URLSearchParams({ token: <?php echo json_encode($token, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?> }).toString();
    var sent = false;

    function send() {
        if (sent || document.visibilityState !== 'visible') {
            return;
        }

        sent = true;
        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, new Blob([body], { type: 'application/x-www-form-urlencoded;charset=UTF-8' }));
            return;
        }

        if (window.fetch) {
            fetch(url, {
                method: 'POST',
                body: body,
                credentials: 'same-origin',
                keepalive: true,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }
            }).catch(function () {});
        }
    }

    window.setTimeout(send, 2000);
})();
</script>
    <?php
}

function slowcloud_stats_overview(): array
{
    slowcloud_ensure_stats_storage();

    $db = \Typecho\Db::get();
    $dailyTable = slowcloud_stats_table('stats_daily');
    $visitorsTable = slowcloud_stats_table('visitors');
    $today = (new \Typecho\Date(\Typecho\Date::time()))->format('Y-m-d');

    $totalRow = $db->fetchRow($db->select([
        'SUM(pv)' => 'total_pv',
        'SUM(uv)' => 'total_uv',
    ])->from($dailyTable));

    $todayRow = $db->fetchRow($db->select()
        ->from($dailyTable)
        ->where('stat_date = ?', $today)
        ->limit(1));

    $todayIpRow = $db->fetchRow("SELECT COUNT(DISTINCT ip_hash) AS total FROM {$visitorsTable} WHERE stat_date = '{$today}'");

    return [
        'total_pv' => (int) ($totalRow['total_pv'] ?? 0),
        'total_uv' => (int) ($totalRow['total_uv'] ?? 0),
        'today_pv' => (int) ($todayRow['pv'] ?? 0),
        'today_uv' => (int) ($todayRow['uv'] ?? 0),
        'today_ips' => (int) ($todayIpRow['total'] ?? 0),
    ];
}

function slowcloud_stats_recent_visits(int $limit = 20): array
{
    slowcloud_ensure_stats_storage();

    $db = \Typecho\Db::get();
    $rows = $db->fetchAll($db->select()
        ->from(slowcloud_stats_table('visits'))
        ->order('visited_at', \Typecho\Db::SORT_DESC)
        ->limit($limit));

    return is_array($rows) ? $rows : [];
}

function slowcloud_stats_daily_series(int $days = 7): array
{
    slowcloud_ensure_stats_storage();

    $db = \Typecho\Db::get();
    $rows = $db->fetchAll($db->select()
        ->from(slowcloud_stats_table('stats_daily'))
        ->order('stat_date', \Typecho\Db::SORT_DESC)
        ->limit($days));

    $map = [];
    foreach ((array) $rows as $row) {
        $map[(string) $row['stat_date']] = [
            'pv' => (int) ($row['pv'] ?? 0),
            'uv' => (int) ($row['uv'] ?? 0),
        ];
    }

    $series = [];
    for ($offset = $days - 1; $offset >= 0; $offset--) {
        $date = (new \Typecho\Date(\Typecho\Date::time() - ($offset * 86400)))->format('Y-m-d');
        $series[] = [
            'date' => $date,
            'pv' => (int) ($map[$date]['pv'] ?? 0),
            'uv' => (int) ($map[$date]['uv'] ?? 0),
        ];
    }

    return $series;
}

function slowcloud_icp_beian($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return trim((string) ($options->icpBeian ?? ''));
}

function slowcloud_icp_beian_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $url = trim((string) ($options->icpBeianUrl ?? ''));

    if ($url !== '' && preg_match('#^https?://#i', $url)) {
        return $url;
    }

    return 'https://beian.miit.gov.cn/';
}

function slowcloud_public_security_beian($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    return trim((string) ($options->publicSecurityBeian ?? ''));
}

function slowcloud_public_security_beian_url($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $url = trim((string) ($options->publicSecurityBeianUrl ?? ''));

    if ($url !== '' && preg_match('#^https?://#i', $url)) {
        return $url;
    }

    return 'https://beian.mps.gov.cn/';
}
