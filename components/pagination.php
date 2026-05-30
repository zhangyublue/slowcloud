<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<nav class="slowcloud-pagination-wrap" aria-label="<?php _e('分页'); ?>">
    <?php $this->pageNav(_t('前一页'), _t('后一页'), 2, '...', [
        'wrapTag' => 'ul',
        'itemTag' => 'li',
        'textTag' => 'span',
        'currentClass' => 'slowcloud-is-current',
    ]); ?>
</nav>
