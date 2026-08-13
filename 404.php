<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<main
    class="slowcloud-layout-shell slowcloud-layout-center"
    style="--slowcloud-main-bg: <?php echo htmlspecialchars(slowcloud_main_background($this), ENT_QUOTES, $this->options->charset); ?>;"
>
    <section class="slowcloud-content-column">
        <div class="slowcloud-empty-card">
            <p class="slowcloud-page-eyebrow">404</p>
            <h1 class="slowcloud-page-title"><?php _e('页面飘走了'); ?></h1>
            <p class="slowcloud-page-summary"><?php _e('你要找的内容可能已移动、删除，或者暂时躲进云里了。'); ?></p>

            <form class="slowcloud-search-form slowcloud-search-form-large" method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
                <label for="slowcloud-search-404" class="slowcloud-sr-only"><?php _e('搜索关键字'); ?></label>
                <input id="slowcloud-search-404" type="text" name="s" value="<?php $this->archiveTitle('', '', ''); ?>" placeholder="<?php _e('试试搜索文章标题'); ?>">
                <button type="submit"><?php _e('搜索'); ?></button>
            </form>
        </div>
        <?php if (slowcloud_basic_layout($this) === 'classic'): ?><?php $this->need('components/site-footer.php'); ?><?php endif; ?>
    </section>
</main>

<?php $this->need('footer.php'); ?>
