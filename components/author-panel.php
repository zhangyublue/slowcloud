<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $authorAvatar = slowcloud_author_avatar($this); ?>
<?php if ($authorAvatar !== ''): ?>
    <img class="slowcloud-home-author-avatar" src="<?php echo htmlspecialchars($authorAvatar, ENT_QUOTES, $this->options->charset); ?>" alt="<?php echo htmlspecialchars(slowcloud_author_name($this), ENT_QUOTES, $this->options->charset); ?>">
<?php endif; ?>
<div class="slowcloud-home-author-copy">
    <h2 class="slowcloud-home-author-name"><?php echo htmlspecialchars(slowcloud_author_name($this), ENT_QUOTES, $this->options->charset); ?></h2>
    <p class="slowcloud-home-author-bio"><?php echo htmlspecialchars(slowcloud_author_bio($this), ENT_QUOTES, $this->options->charset); ?></p>
</div>

<?php $socialLinks = slowcloud_social_links($this); ?>
<?php if (!empty($socialLinks)): ?>
    <section class="slowcloud-home-author-links" aria-label="<?php _e('社交平台'); ?>">
        <h3 class="slowcloud-home-author-links-title"><?php _e('其他平台'); ?></h3>
        <ul class="slowcloud-home-author-links-list slowcloud-home-author-links-list-social">
            <?php foreach ($socialLinks as $socialLink): ?>
                <li>
                    <a
                        class="slowcloud-home-author-social"
                        href="<?php echo htmlspecialchars($socialLink['url'], ENT_QUOTES, $this->options->charset); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span class="iconfont <?php echo htmlspecialchars($socialLink['icon'], ENT_QUOTES, $this->options->charset); ?>" aria-hidden="true"></span>
                        <span><?php echo htmlspecialchars($socialLink['name'], ENT_QUOTES, $this->options->charset); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php $siteStats = slowcloud_site_stats($this); ?>
<?php if (!empty($siteStats)): ?>
    <section class="slowcloud-home-author-links" aria-label="<?php _e('站点信息'); ?>">
        <h3 class="slowcloud-home-author-links-title"><?php _e('站点信息'); ?></h3>
        <ul class="slowcloud-home-author-links-list slowcloud-home-author-stats-list">
            <?php foreach ($siteStats as $siteStat): ?>
                <li class="slowcloud-home-author-stat">
                    <span class="slowcloud-home-author-stat-label"><?php echo htmlspecialchars($siteStat['label'], ENT_QUOTES, $this->options->charset); ?></span>
                    <strong class="slowcloud-home-author-stat-value"><?php echo htmlspecialchars($siteStat['value'], ENT_QUOTES, $this->options->charset); ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php $friendLinks = slowcloud_friend_links($this); ?>
<?php if (!empty($friendLinks)): ?>
    <section class="slowcloud-home-author-links">
        <h3 class="slowcloud-home-author-links-title"><?php _e('友链'); ?></h3>
        <ul class="slowcloud-home-author-links-list">
            <?php foreach ($friendLinks as $friendLink): ?>
                <li>
                    <a
                        href="<?php echo htmlspecialchars($friendLink['url'], ENT_QUOTES, $this->options->charset); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php echo htmlspecialchars($friendLink['name'], ENT_QUOTES, $this->options->charset); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
