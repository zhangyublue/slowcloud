<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<section class="slowcloud-comment-card" id="comments">
    <?php $this->comments()->to($comments); ?>

    <?php if ($comments->have()): ?>
        <div class="slowcloud-comment-head">
            <h2><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></h2>
        </div>

        <?php $comments->listComments(); ?>
        <div class="slowcloud-comment-pagination">
            <?php $comments->pageNav(_t('上一页'), _t('下一页')); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="slowcloud-comment-respond">
            <div class="slowcloud-cancel-reply"><?php $comments->cancelReply(); ?></div>
            <h2><?php _e('写下你的想法'); ?></h2>

            <form method="post" action="<?php $this->commentUrl(); ?>" id="slowcloud-comment-form" class="slowcloud-comment-form" role="form">
                <?php if ($this->user->hasLogin()): ?>
                    <p class="slowcloud-login-hint">
                        <?php _e('当前登录'); ?>:
                        <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                        ·
                        <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出'); ?></a>
                    </p>
                <?php else: ?>
                    <div class="slowcloud-form-grid">
                        <p>
                            <label for="author"><?php _e('称呼'); ?></label>
                            <input type="text" name="author" id="author" value="<?php $this->remember('author'); ?>" required>
                        </p>
                        <p>
                            <label for="mail"><?php _e('邮箱'); ?></label>
                            <input type="email" name="mail" id="mail" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                        </p>
                        <p>
                            <label for="url"><?php _e('网站'); ?></label>
                            <input type="url" name="url" id="url" placeholder="https://example.com" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>>
                        </p>
                    </div>
                <?php endif; ?>

                <p>
                    <label for="textarea"><?php _e('内容'); ?></label>
                    <textarea rows="6" name="text" id="textarea" required><?php $this->remember('text'); ?></textarea>
                </p>

                <p>
                    <button type="submit"><?php _e('提交评论'); ?></button>
                </p>
            </form>
        </div>
    <?php else: ?>
        <div class="slowcloud-comment-head">
            <h2><?php _e('评论已关闭'); ?></h2>
        </div>
    <?php endif; ?>
</section>
