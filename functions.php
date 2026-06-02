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

function themeConfig($form)
{
    $tabTitle = new \Typecho\Widget\Helper\Form\Element\Text(
        'tabTitle',
        null,
        null,
        _t('浏览器 Tab 文字'),
        _t('用于浏览器标签页显示的文字，不填写时默认使用站点标题')
    );
    $form->addInput(slowcloud_assign_settings_group($tabTitle, 'browser-tab', '浏览器 Tab 设置'));

    $logoUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        null,
        _t('网站 Logo'),
        _t('填写图片 URL 后，主题头部将显示 Logo')
    );
    $logoUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($logoUrl, 'browser-tab', '浏览器 Tab 设置'));

    $headerBackgroundUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerBackgroundUrl',
        null,
        null,
        _t('Header 背景图'),
        _t('填写图片 URL 后，站点头部将使用这张图片作为横幅背景')
    );
    $headerBackgroundUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($headerBackgroundUrl, 'header-display', 'Header 展示设置'));

    $headerHeight = new \Typecho\Widget\Helper\Form\Element\Text(
        'headerHeight',
        null,
        '100vh',
        _t('Header 高度'),
        _t('支持 CSS 高度值，例如 100vh、720px、80vh')
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
        null,
        _t('博主头像'),
        _t('填写图片 URL，用于内容区左侧信息栏头像')
    );
    $authorAvatar->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($authorAvatar, 'author-card', '博主信息栏'));

    $authorName = new \Typecho\Widget\Helper\Form\Element\Text(
        'authorName',
        null,
        null,
        _t('博主名称'),
        _t('用于内容区左侧信息栏名称，不填写时默认使用站点标题')
    );
    $form->addInput(slowcloud_assign_settings_group($authorName, 'author-card', '博主信息栏'));

    $authorBio = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'authorBio',
        null,
        null,
        _t('博主描述'),
        _t('用于内容区左侧信息栏描述，不填写时默认使用首页简介')
    );
    $form->addInput(slowcloud_assign_settings_group($authorBio, 'author-card', '博主信息栏'));

    $githubUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'githubUrl',
        null,
        null,
        _t('GitHub 地址'),
        _t('填写后会显示在左侧作者区域，例如 https://github.com/your-name')
    );
    $githubUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($githubUrl, 'author-card', '博主信息栏'));

    $bilibiliUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'bilibiliUrl',
        null,
        null,
        _t('Bilibili 地址'),
        _t('填写后会显示在左侧作者区域，例如 https://space.bilibili.com/123456')
    );
    $bilibiliUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($bilibiliUrl, 'author-card', '博主信息栏'));

    $friendLinks = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'friendLinks',
        null,
        null,
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
        _t('仅用于改写站内上传目录 usr/uploads 下的图片地址。填写 CDN 域名根地址，例如 https://cdn.example.com；保存后会作用于文章正文、摘要和海报图中的本地上传图片，请确保 CDN 已正确回源到站点上传目录。')
    );
    $uploadCdnUrl->addRule('url', _t('请填写正确的 URL 地址'));
    $form->addInput(slowcloud_assign_settings_group($uploadCdnUrl, 'content-delivery', '内容分发设置'));

    $form->addInput(slowcloud_theme_settings_enhancer());
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

function slowcloud_rewrite_upload_url($archive, string $url): string
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
        if (strpos($path, '/usr/uploads/') === 0 || strpos($path, 'usr/uploads/') === 0) {
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
    } elseif (strpos($urlPath, '/usr/uploads/') === 0) {
        $relativePath = ltrim($urlPath, '/');
    }

    if (strpos($relativePath, 'usr/uploads/') !== 0) {
        return $url;
    }

    return slowcloud_join_url($cdnUrl, $relativePath)
        . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '')
        . (isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '');
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
    echo slowcloud_rewrite_upload_html($archive, (string) ob_get_clean());
}

function slowcloud_render_excerpt($archive, int $length = 180, string $suffix = '...'): void
{
    ob_start();
    $archive->excerpt($length, $suffix);
    echo slowcloud_rewrite_upload_html($archive, (string) ob_get_clean());
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

    return trim((string) ($options->authorAvatar ?? ''));
}

function slowcloud_author_name($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $name = trim((string) ($options->authorName ?? ''));

    if ($name !== '') {
        return $name;
    }

    return (string) ($options->title ?? '');
}

function slowcloud_author_bio($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $bio = trim((string) ($options->authorBio ?? ''));

    if ($bio !== '') {
        return $bio;
    }

    return slowcloud_intro($archive);
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
            'label' => _t('访客数量'),
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

    return (string) ($options->title ?? '');
}

function slowcloud_header_background($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();

    return trim((string) ($options->headerBackgroundUrl ?? ''));
}

function slowcloud_header_height($archive): string
{
    $options = $archive->options ?? \Widget\Options::alloc();
    $height = trim((string) ($options->headerHeight ?? '100vh'));

    if ($height === '') {
        return '100vh';
    }

    if (preg_match('/^\d+(\.\d+)?(px|vh|vw|rem|em|%)$/', $height)) {
        return $height;
    }

    return '100vh';
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
