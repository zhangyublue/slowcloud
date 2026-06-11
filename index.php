<?php
/**
 * A calm, airy personal theme for Typecho
 *
 * @package Slowcloud
 * @author 章鱼
 * @version 1.0.0
 * @link https://slowcloud.cn
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<?php if ($this->is('index')): ?>
    <main
        class="slowcloud-home-grid"
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

        <section class="slowcloud-home-band slowcloud-home-band-stream">
            <?php if ($this->have()): ?>
                <div class="slowcloud-post-list">
                    <?php while ($this->next()): ?>
                        <?php $this->need('components/post-card.php'); ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <?php $this->need('components/empty.php'); ?>
            <?php endif; ?>

            <?php $this->need('components/pagination.php'); ?>
        </section>

        <section class="slowcloud-home-band slowcloud-home-band-side">
            <?php if (slowcloud_show_sidebar($this)): ?>
                <?php $this->need('components/sidebar.php'); ?>
            <?php else: ?>
                <div class="slowcloud-home-note">
                    <p class="slowcloud-home-note-label"><?php _e('Slowcloud'); ?></p>
                    <p class="slowcloud-home-note-text"><?php _e('在这里慢慢记录、慢慢整理，也慢慢留下属于自己的时间纹理。'); ?></p>
                </div>
            <?php endif; ?>
        </section>
    </main>
<?php else: ?>
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
            <?php if (!($this->is('post'))): ?>
                <div class="slowcloud-page-lead">
                    <p class="slowcloud-page-eyebrow"><?php _e('归档浏览'); ?></p>
                    <h1 class="slowcloud-page-title">
                        <?php $this->archiveTitle([
                            'category' => _t('分类 %s'),
                            'search'   => _t('搜索 %s'),
                            'tag'      => _t('标签 %s'),
                            'author'   => _t('%s 的文章')
                        ], '', ''); ?>
                    </h1>
                </div>
            <?php endif; ?>

            <?php if ($this->have()): ?>
                <div class="slowcloud-post-list">
                    <?php while ($this->next()): ?>
                        <?php $this->need('components/post-card.php'); ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <?php $this->need('components/empty.php'); ?>
            <?php endif; ?>

            <?php $this->need('components/pagination.php'); ?>
        </section>

        <?php if (slowcloud_show_sidebar($this)): ?>
            <?php $this->need('components/sidebar.php'); ?>
        <?php endif; ?>
    </main>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
