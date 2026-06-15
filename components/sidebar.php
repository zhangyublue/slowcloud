<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<aside class="slowcloud-sidebar-column">
    <section class="slowcloud-sidebar-card slowcloud-sidebar-card-recent">
        <h2><?php _e('最新文章'); ?></h2>
        <ul>
            <?php \Widget\Contents\Post\Recent::alloc()
                ->parse('<li><a href="{permalink}">{title}</a></li>'); ?>
        </ul>
    </section>
    
    <section class="slowcloud-sidebar-card slowcloud-sidebar-card-category">
        <h2><?php _e('分类'); ?></h2>
        <ul>
            <?php \Widget\Metas\Category\Rows::alloc()->listCategories('wrapClass=category-list&showCount=1&countTemplate=<span class="slowcloud-category-count">%d</span>'); ?>
        </ul>
    </section>

    <section class="slowcloud-sidebar-card slowcloud-sidebar-card-tags">
        <h2><?php _e('标签云'); ?></h2>
        <div class="slowcloud-tag-cloud">
            <?php \Widget\Metas\Tag\Cloud::alloc('sort=count&ignoreZeroCount=1&desc=1&limit=30')->to($tags); ?>
            <?php if ($tags->have()): ?>
                <?php while ($tags->next()): ?>
                    <?php $tagCount = (int) $tags->count; ?>
                    <a
                        href="<?php $tags->permalink(); ?>"
                        class="<?php echo $tagCount >= 20 ? 'is-lg' : ($tagCount >= 8 ? 'is-md' : 'is-sm'); ?>"
                    >
                        <span class="slowcloud-tag-prefix" aria-hidden="true">#</span>
                        <span class="slowcloud-tag-name"><?php $tags->name(); ?></span>
                        <span class="slowcloud-tag-count"><?php echo $tagCount; ?></span>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <span class="slowcloud-tag-cloud-empty"><?php _e('暂无标签'); ?></span>
            <?php endif; ?>
        </div>
    </section>
</aside>
