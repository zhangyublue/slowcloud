<article class="slowcloud-article-card" itemscope itemtype="http://schema.org/BlogPosting">
    <?php $poster = slowcloud_poster($this); ?>
    <?php if ($poster !== ''): ?>
        <a class="slowcloud-article-poster-link" href="<?php $this->permalink(); ?>" aria-label="<?php $this->title(); ?>">
            <img class="slowcloud-article-poster" src="<?php echo htmlspecialchars($poster, ENT_QUOTES, $this->options->charset); ?>" alt="<?php $this->title(); ?>">
        </a>
    <?php endif; ?>

    <header class="slowcloud-article-header">
        <h2 class="slowcloud-article-title" itemprop="name headline">
            <?php $primaryCategory = slowcloud_primary_category($this); ?>
            <?php if ($primaryCategory !== ''): ?>
                <span class="slowcloud-article-category-tag"><?php echo htmlspecialchars($primaryCategory, ENT_QUOTES, $this->options->charset); ?></span>
            <?php endif; ?>
            <a href="<?php $this->permalink(); ?>" itemprop="url"><?php $this->title(); ?></a>
        </h2>
    </header>

    <?php if ($poster === ''): ?>
        <div class="slowcloud-entry-content slowcloud-article-excerpt" itemprop="articleBody">
            <?php slowcloud_render_excerpt($this, 180, '...'); ?>
        </div>
    <?php endif; ?>

    <footer class="slowcloud-article-meta slowcloud-article-meta-card">
        <span class="slowcloud-meta-item">
            <span class="slowcloud-meta-icon iconfont icon-slowcloudadmin" aria-hidden="true"></span>
            <span><?php $this->author(); ?></span>
        </span>
        <span class="slowcloud-meta-item">
            <span class="slowcloud-meta-icon iconfont icon-slowcloudriqi2" aria-hidden="true"></span>
            <time datetime="<?php $this->date('c'); ?>" itemprop="datePublished"><?php $this->date('Y-m-d'); ?></time>
        </span>
        <span class="slowcloud-meta-item">
            <span class="slowcloud-meta-icon iconfont icon-slowcloudview" aria-hidden="true"></span>
            <span><?php echo htmlspecialchars(slowcloud_views_text($this), ENT_QUOTES, $this->options->charset); ?></span>
        </span>
        <span class="slowcloud-meta-item">
            <span class="slowcloud-meta-icon iconfont icon-slowcloudcommet" aria-hidden="true"></span>
            <span><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></span>
        </span>
    </footer>
</article>
