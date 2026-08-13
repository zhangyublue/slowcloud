<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<main
    class="slowcloud-layout-shell"
    style="
        --slowcloud-main-bg: <?php echo htmlspecialchars(slowcloud_main_background($this), ENT_QUOTES, $this->options->charset); ?>;
        --slowcloud-home-left-bg-light: <?php echo htmlspecialchars(slowcloud_column_background($this, 'left'), ENT_QUOTES, $this->options->charset); ?>;
        --slowcloud-home-center-bg-light: <?php echo htmlspecialchars(slowcloud_column_background($this, 'center'), ENT_QUOTES, $this->options->charset); ?>;
        --slowcloud-home-right-bg-light: <?php echo htmlspecialchars(slowcloud_column_background($this, 'right'), ENT_QUOTES, $this->options->charset); ?>;
    "
>
    <section class="slowcloud-home-band slowcloud-home-band-author">
        <?php $this->need('components/author-panel.php'); ?>
    </section>

    <section class="slowcloud-content-column">
        <article class="slowcloud-article-card slowcloud-article-detail" itemscope itemtype="http://schema.org/BlogPosting">
            <?php postMeta($this, 'post'); ?>

            <?php $poster = slowcloud_poster($this); ?>
            <?php if ($poster !== ''): ?>
                <img class="slowcloud-article-poster slowcloud-article-poster-detail" src="<?php echo htmlspecialchars($poster, ENT_QUOTES, $this->options->charset); ?>" alt="<?php echo htmlspecialchars(slowcloud_poster_alt($this), ENT_QUOTES, $this->options->charset); ?>" itemprop="image" loading="eager" decoding="async" fetchpriority="high"<?php echo slowcloud_image_dimension_attrs($this, $poster, $this->options->charset); ?><?php echo slowcloud_image_srcset_attrs($this, $poster, $this->options->charset, '(max-width: 768px) 100vw, 760px'); ?>>
            <?php endif; ?>

            <div class="slowcloud-entry-content" itemprop="articleBody">
                <?php slowcloud_render_content($this); ?>
            </div>

            <footer class="slowcloud-article-footer">
                <p class="slowcloud-tag-row"><?php _e('标签'); ?>: <?php slowcloud_post_tags($this, ', ', _t('暂无标签')); ?></p>
            </footer>
        </article>

        <nav class="slowcloud-post-nav" aria-label="<?php _e('文章导航'); ?>">
            <div class="slowcloud-nav-card">
                <div class="slowcloud-nav-link slowcloud-nav-link-prev">
                    <span class="slowcloud-nav-text"><?php _e('上一篇'); ?>:</span>
                    <span class="slowcloud-nav-title"><?php $this->thePrev('%s', _t('没有了')); ?></span>
                </div>
            </div>
            <div class="slowcloud-nav-card">
                <div class="slowcloud-nav-link slowcloud-nav-link-next">
                    <span class="slowcloud-nav-text"><?php _e('下一篇'); ?>:</span>
                    <span class="slowcloud-nav-title"><?php $this->theNext('%s', _t('没有了')); ?></span>
                </div>
            </div>
        </nav>

        <?php $this->need('comments.php'); ?>
        <?php if (slowcloud_basic_layout($this) === 'classic'): ?><?php $this->need('components/site-footer.php'); ?><?php endif; ?>
    </section>

    <?php if (slowcloud_show_sidebar($this)): ?>
        <?php $this->need('components/sidebar.php'); ?>
    <?php endif; ?>
</main>

<?php $this->need('footer.php'); ?>
