<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $slowcloudHeaderBackground = slowcloud_header_background($this); ?>
<?php $slowcloudHeaderHeight = slowcloud_header_height($this); ?>
<?php $slowcloudSiteWidth = slowcloud_site_width($this); ?>
<?php $slowcloudTabTitle = slowcloud_tab_title($this); ?>
<?php $slowcloudThemeMode = slowcloud_theme_mode($this); ?>
<?php $slowcloudTimelinePage = slowcloud_timeline_page(); ?>
<?php $slowcloudLogoUrl = slowcloud_logo_url($this); ?>
<?php $slowcloudSeoContext = slowcloud_seo_context($this); ?>
<?php $slowcloudPrismBaseUrl = rtrim((string) $this->options->themeUrl('assets/typecho/prism', 'slowcloud'), '/') . '/'; ?>
<?php $slowcloudContentRenderVersion = @filemtime(__DIR__ . '/assets/css/content-render.css') ?: time(); ?>
<?php $slowcloudCodeHighlightVersion = @filemtime(__DIR__ . '/assets/css/code-highlight.css') ?: time(); ?>
<?php slowcloud_set_stats_context($this); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="color-scheme" content="light dark">
    <title><?php echo slowcloud_seo_escape((string) $slowcloudSeoContext['title'], $this->options->charset); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($slowcloudLogoUrl, ENT_QUOTES, $this->options->charset); ?>">
    <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($slowcloudLogoUrl, ENT_QUOTES, $this->options->charset); ?>">
    <?php if (slowcloud_sitemap_enabled($this)): ?>
        <link rel="sitemap" type="application/xml" href="<?php echo htmlspecialchars(slowcloud_seo_normalize_site_url($this, \Typecho\Common::url('sitemap.xml', (string) $this->options->index)), ENT_QUOTES, $this->options->charset); ?>">
    <?php endif; ?>
    <?php slowcloud_render_seo_meta($this, $slowcloudSeoContext); ?>

    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($this->options->themeUrl('assets/css/content-render.css', 'slowcloud') . '?v=' . $slowcloudContentRenderVersion, ENT_QUOTES, $this->options->charset); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($slowcloudPrismBaseUrl . 'plugins/line-numbers/prism-line-numbers.css', ENT_QUOTES, $this->options->charset); ?>">
    <link id="slowcloud-prism-theme-coy" rel="stylesheet" href="<?php echo htmlspecialchars($slowcloudPrismBaseUrl . 'themes/prism-coy.css', ENT_QUOTES, $this->options->charset); ?>">
    <link id="slowcloud-prism-theme-okaidia" rel="stylesheet" href="<?php echo htmlspecialchars($slowcloudPrismBaseUrl . 'themes/prism-okaidia.css', ENT_QUOTES, $this->options->charset); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($this->options->themeUrl('assets/css/code-highlight.css', 'slowcloud') . '?v=' . $slowcloudCodeHighlightVersion, ENT_QUOTES, $this->options->charset); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/iconfont/iconfont.css'); ?>">
    <?php slowcloud_render_typecho_header($this, 'description=&keywords=&social='); ?>
</head>
<body class="slowcloud-body" data-slowcloud-theme-mode="<?php echo htmlspecialchars($slowcloudThemeMode, ENT_QUOTES, $this->options->charset); ?>">
<div class="slowcloud-top-loader" data-slowcloud-top-loader aria-hidden="true">
    <span class="slowcloud-top-loader__beam"></span>
</div>
<div class="slowcloud-site-wrap" style="--slowcloud-container: <?php echo htmlspecialchars($slowcloudSiteWidth, ENT_QUOTES, $this->options->charset); ?>;">
    <header class="slowcloud-site-header<?php if ($slowcloudHeaderBackground !== ''): ?> slowcloud-site-header--has-cover<?php endif; ?>"<?php if ($slowcloudHeaderBackground !== ''): ?> data-slowcloud-cover="<?php echo htmlspecialchars($slowcloudHeaderBackground, ENT_QUOTES, $this->options->charset); ?>"<?php endif; ?> style="--slowcloud-header-height: <?php echo htmlspecialchars($slowcloudHeaderHeight, ENT_QUOTES, $this->options->charset); ?>;<?php if ($slowcloudHeaderBackground !== ''): ?> --slowcloud-header-cover-image: url('<?php echo htmlspecialchars($slowcloudHeaderBackground, ENT_QUOTES, $this->options->charset); ?>');<?php endif; ?>">
        <div class="slowcloud-header-overlay"></div>
        <div class="slowcloud-header-inner">
            <div class="slowcloud-header-topbar">
                <a class="slowcloud-brand-title" href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>

                <div class="slowcloud-header-actions">
                    <nav class="slowcloud-site-nav" aria-label="<?php _e('主导航'); ?>">
                        <a href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a>
                        <?php if ($slowcloudTimelinePage !== null): ?>
                            <a href="<?php echo htmlspecialchars($slowcloudTimelinePage['permalink'], ENT_QUOTES, $this->options->charset); ?>"><?php _e('时光轴'); ?></a>
                        <?php endif; ?>
                        <?php \Widget\Contents\Page\Rows::alloc()->to($pages); ?>
                        <?php while ($pages->next()): ?>
                            <?php if ((string) $pages->template === 'timeline.php') continue; ?>
                            <a href="<?php $pages->permalink(); ?>"><?php $pages->title(); ?></a>
                        <?php endwhile; ?>
                    </nav>

                    <button
                        type="button"
                        class="slowcloud-theme-toggle"
                        data-slowcloud-theme-toggle
                        aria-pressed="false"
                        aria-label="<?php _e('切换黑夜白天模式'); ?>"
                    >
                        <span class="slowcloud-theme-toggle__icon" aria-hidden="true">◐</span>
                    </button>
                </div>
            </div>

            <div class="slowcloud-header-content">
                <p class="slowcloud-header-description"><?php echo slowcloud_intro($this); ?></p>
            </div>
        </div>
    </header>
