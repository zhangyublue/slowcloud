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
            <?php postMeta($this, 'page'); ?>

            <div class="slowcloud-entry-content" itemprop="articleBody">
                <?php slowcloud_render_content($this); ?>
            </div>
        </article>

        <?php $this->need('comments.php'); ?>
        <?php if (slowcloud_basic_layout($this) === 'classic'): ?><?php $this->need('components/site-footer.php'); ?><?php endif; ?>
    </section>

    <?php if (slowcloud_show_sidebar($this)): ?>
        <?php $this->need('components/sidebar.php'); ?>
    <?php endif; ?>
</main>

<?php $this->need('footer.php'); ?>
