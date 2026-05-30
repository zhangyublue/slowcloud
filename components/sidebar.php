<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<aside class="slowcloud-sidebar-column">
    <section class="slowcloud-sidebar-card">
        <h2><?php _e('最新文章'); ?></h2>
        <ul>
            <?php \Widget\Contents\Post\Recent::alloc()
                ->parse('<li><a href="{permalink}">{title}</a></li>'); ?>
        </ul>
    </section>
    
    <section class="slowcloud-sidebar-card">
        <h2><?php _e('分类'); ?></h2>
        <ul>
            <?php \Widget\Metas\Category\Rows::alloc()->listCategories('wrapClass=category-list&showCount=1&countTemplate=<span class="slowcloud-category-count">%d</span>'); ?>
        </ul>
    </section>
</aside>
