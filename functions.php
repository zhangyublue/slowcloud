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
</style>
<script>
(function () {
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

function slowcloud_render_admin_editor_enhance($content): void
{
    $options = \Widget\Options::alloc();
    $cssUrl = $options->themeUrl('assets/typecho/editor-enhance.css', 'slowcloud');
    $cssFile = $options->themeFile('slowcloud', 'assets/typecho/editor-enhance.css');
    $contentCssUrl = $options->themeUrl('assets/css/content-render.css', 'slowcloud');
    $contentCssFile = $options->themeFile('slowcloud', 'assets/css/content-render.css');
    $codeCssUrl = $options->themeUrl('assets/css/code-highlight.css', 'slowcloud');
    $codeCssFile = $options->themeFile('slowcloud', 'assets/css/code-highlight.css');
    $scriptFile = $options->themeFile('slowcloud', 'assets/typecho/editor-enhance.js');
    $prismBaseUrl = rtrim((string) $options->themeUrl('assets/typecho/prism', 'slowcloud'), '/') . '/';
    $themeMode = (string) ($options->themeMode ?? 'system');
    ?>
	    (function () {
		        [
		            <?php echo json_encode($contentCssUrl . '?v=' . (is_file($contentCssFile) ? filemtime($contentCssFile) : time()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
		            <?php echo json_encode($codeCssUrl . '?v=' . (is_file($codeCssFile) ? filemtime($codeCssFile) : time()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
		            <?php echo json_encode($cssUrl . '?v=' . (is_file($cssFile) ? filemtime($cssFile) : time()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
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
            placeholder: <?php echo json_encode(_t('标题文字'), JSON_UNESCAPED_UNICODE); ?>
        },
        themeMode: <?php echo json_encode($themeMode, JSON_UNESCAPED_UNICODE); ?>,
        prism: {
            core: <?php echo json_encode($prismBaseUrl . 'prism.js', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            autoloader: <?php echo json_encode($prismBaseUrl . 'plugins/autoloader/prism-autoloader.js', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            components: <?php echo json_encode($prismBaseUrl . 'components/', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            lineNumbersScript: <?php echo json_encode($prismBaseUrl . 'plugins/line-numbers/prism-line-numbers.js', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            lineNumbersStyle: <?php echo json_encode($prismBaseUrl . 'plugins/line-numbers/prism-line-numbers.css', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            coyStyle: <?php echo json_encode($prismBaseUrl . 'themes/prism-coy.css', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
            okaidiaStyle: <?php echo json_encode($prismBaseUrl . 'themes/prism-okaidia.css', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
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
    $poster = trim((string) ($archive->fields->poster ?? ''));
    if ($poster !== '') {
        return slowcloud_rewrite_upload_url($archive, $poster);
    }

    $requestFields = \Typecho\Request::getInstance()->getArray('fields');
    if (is_array($requestFields) && isset($requestFields['poster'])) {
        return slowcloud_rewrite_upload_url($archive, trim((string) $requestFields['poster']));
    }

    return '';
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

function slowcloud_render_content($archive): void
{
    ob_start();
    $archive->content();
    $html = slowcloud_rewrite_upload_html($archive, (string) ob_get_clean());
    echo slowcloud_replace_owo_shortcodes($archive, $html);
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

function slowcloud_record_view($archive): int
{
    if (!isset($archive->cid) || (string) ($archive->type ?? '') !== 'post') {
        return 0;
    }

    $cid = (int) $archive->cid;
    $views = slowcloud_views($archive) + 1;
    $db = \Typecho\Db::get();
    $exists = $db->fetchRow($db->select('cid')
        ->from('table.fields')
        ->where('cid = ? AND name = ?', $cid, 'views'));

    $rows = [
        'type' => 'int',
        'str_value' => null,
        'int_value' => $views,
        'float_value' => 0,
    ];

    if ($exists) {
        $db->query($db->update('table.fields')
            ->rows($rows)
            ->where('cid = ? AND name = ?', $cid, 'views'));
    } else {
        $rows['cid'] = $cid;
        $rows['name'] = 'views';
        $db->query($db->insert('table.fields')->rows($rows));
    }

    $archive->fields->views = $views;

    return $views;
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
    return slowcloud_theme_asset_url('usr/themes/slowcloud/assets/img/avatar.jpg', $archive);
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
            'icon' => $platform['icon'],
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

    $viewStats = $db->fetchRow($db->select([
        'SUM(table.fields.int_value)' => 'views',
    ])->from('table.fields')
        ->where('table.fields.name = ?', 'views'));
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
        [
            'label' => _t('文章浏览'),
            'value' => (string) (int) ($viewStats['views'] ?? 0),
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
        $years[$yearKey]['months'][$monthKey]['items'][] = [
            'title' => (string) $posts->title,
            'permalink' => (string) $posts->permalink,
            'date' => $posts->date->format('m-d'),
            'datetime' => $posts->date->format('c'),
            'category' => slowcloud_timeline_category_text($posts),
            'views' => slowcloud_views($posts),
            'summary' => $summary,
            'poster' => $poster,
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
    return '1';
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
    slowcloud_stats_set_option('stats_schema_version', slowcloud_stats_storage_version());
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

function slowcloud_should_track_request($archive): bool
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
    if ($requestMethod !== 'GET' && $requestMethod !== 'HEAD') {
        return false;
    }

    if (method_exists($request, 'isAjax') && $request->isAjax()) {
        return false;
    }

    if (method_exists($request, 'isJson') && $request->isJson()) {
        return false;
    }

    $path = (string) ($request->getPathInfo() ?? '');
    if (slowcloud_is_excluded_stats_path($path)) {
        return false;
    }

    $userAgent = strtolower((string) $request->getServer('HTTP_USER_AGENT', ''));
    foreach (['bot', 'spider', 'crawler', 'curl', 'wget', 'python-requests'] as $needle) {
        if ($userAgent !== '' && strpos($userAgent, $needle) !== false) {
            return false;
        }
    }

    return slowcloud_is_valid_front_page($archive);
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

function slowcloud_track_site_visit($archive): void
{
    try {
        $context = slowcloud_get_stats_context();
        $statsTarget = $context !== [] ? $context : $archive;

        if (!slowcloud_should_track_request($statsTarget)) {
            return;
        }

        slowcloud_ensure_stats_storage();

        $db = \Typecho\Db::get();
        $request = $archive->request ?? \Widget\Options::alloc()->request;
        $time = \Typecho\Date::time();
        $statDate = (new \Typecho\Date($time))->format('Y-m-d');
        $visitorId = slowcloud_stats_visitor_id();
        $path = substr((string) $request->getRequestUrl(), 0, 255);
        $pageType = slowcloud_stats_page_type($statsTarget);
        $ip = substr((string) $request->getIp(), 0, 64);
        $ipHash = sha1($ip);
        $userAgent = substr((string) $request->getServer('HTTP_USER_AGENT', ''), 0, 511);
        $referer = substr((string) ($request->getReferer() ?? ''), 0, 255);

        $db->query($db->insert(slowcloud_stats_table('visits'))->rows([
            'visitor_id' => $visitorId,
            'stat_date' => $statDate,
            'visited_at' => $time,
            'ip' => $ip,
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'path' => $path,
            'page_type' => $pageType,
        ]));

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
    } catch (\Throwable $e) {
    }
}

function slowcloud_stats_overview(): array
{
    slowcloud_ensure_stats_storage();

    $db = \Typecho\Db::get();
    $dailyTable = slowcloud_stats_table('stats_daily');
    $visitsTable = slowcloud_stats_table('visits');
    $today = (new \Typecho\Date(\Typecho\Date::time()))->format('Y-m-d');

    $totalRow = $db->fetchRow($db->select([
        'SUM(pv)' => 'total_pv',
        'SUM(uv)' => 'total_uv',
    ])->from($dailyTable));

    $todayRow = $db->fetchRow($db->select()
        ->from($dailyTable)
        ->where('stat_date = ?', $today)
        ->limit(1));

    $todayIpRow = $db->fetchRow("SELECT COUNT(DISTINCT ip_hash) AS total FROM {$visitsTable} WHERE stat_date = '{$today}'");

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
