<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $slowcloudHeaderBackground = slowcloud_header_background($this); ?>
<?php $slowcloudHeaderHeight = slowcloud_header_height($this); ?>
<?php $slowcloudSiteWidth = slowcloud_site_width($this); ?>
<?php $slowcloudTabTitle = slowcloud_tab_title($this); ?>
<?php $slowcloudThemeMode = slowcloud_theme_mode($this); ?>
<?php $slowcloudTimelinePage = slowcloud_timeline_page(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="color-scheme" content="light dark">
    <title><?php $this->archiveTitle([
        'category' => _t('分类 %s'),
        'search'   => _t('搜索 %s'),
        'tag'      => _t('标签 %s'),
        'author'   => _t('%s 的文章')
    ], '', ' - '); ?><?php echo htmlspecialchars($slowcloudTabTitle, ENT_QUOTES, $this->options->charset); ?></title>
    <?php if ($this->options->logoUrl): ?>
        <link rel="icon" href="<?php $this->options->logoUrl(); ?>">
        <link rel="shortcut icon" href="<?php $this->options->logoUrl(); ?>">
        <link rel="apple-touch-icon" href="<?php $this->options->logoUrl(); ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/main.css'); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/iconfont/iconfont.css'); ?>">
    <?php $this->header(); ?>
</head>
<body class="slowcloud-body" data-slowcloud-theme-mode="<?php echo htmlspecialchars($slowcloudThemeMode, ENT_QUOTES, $this->options->charset); ?>">
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
