<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $authorAvatar = slowcloud_author_avatar($this); ?>
<?php if ($authorAvatar !== ''): ?>
    <img class="slowcloud-home-author-avatar" src="<?php echo htmlspecialchars($authorAvatar, ENT_QUOTES, $this->options->charset); ?>" alt="<?php echo htmlspecialchars(slowcloud_author_name($this), ENT_QUOTES, $this->options->charset); ?>">
<?php endif; ?>
<div class="slowcloud-home-author-copy">
    <h2 class="slowcloud-home-author-name"><?php echo htmlspecialchars(slowcloud_author_name($this), ENT_QUOTES, $this->options->charset); ?></h2>
    <p class="slowcloud-home-author-bio"><?php echo htmlspecialchars(slowcloud_author_bio($this), ENT_QUOTES, $this->options->charset); ?></p>
</div>
