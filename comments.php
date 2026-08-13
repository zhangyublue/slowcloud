<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<section class="slowcloud-comment-card" id="comments">
    <?php $this->comments()->to($comments); ?>

    <?php if ($comments->have()): ?>
        <div class="slowcloud-comment-head">
            <h2><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></h2>
        </div>

        <?php $comments->listComments([
            'before' => '<ol class="comment-list slowcloud-comment-list">',
            'after' => '</ol>',
            'dateFormat' => 'Y-n-j H:i',
            'defaultAvatar' => slowcloud_comment_default_avatar($this),
            'avatarHighRes' => true,
        ]); ?>
        <div class="slowcloud-comment-pagination">
            <?php $comments->pageNav(_t('上一页'), _t('下一页')); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="slowcloud-comment-respond">
            <div class="slowcloud-cancel-reply" data-slowcloud-cancel-reply-home><?php $comments->cancelReply(_t('取消回复')); ?></div>
            <div class="slowcloud-comment-respond-head">
                <h2><?php _e('写下你的想法'); ?></h2>
                <?php if ($this->user->hasLogin()): ?>
                    <p class="slowcloud-login-hint">
                        <?php _e('当前登录'); ?>:
                        <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                        <span aria-hidden="true">·</span>
                        <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出'); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <form method="post" action="<?php $this->commentUrl(); ?>" id="slowcloud-comment-form" class="slowcloud-comment-form" role="form">
                <?php if (!$this->user->hasLogin()): ?>
                    <div class="slowcloud-form-grid slowcloud-form-grid-meta">
                        <p>
                            <label for="author"><?php _e('称呼'); ?> <span class="slowcloud-field-required">*</span></label>
                            <input type="text" name="author" id="author" value="<?php $this->remember('author'); ?>" required>
                        </p>
                        <p>
                            <label for="mail"><?php _e('邮箱'); ?> <span class="slowcloud-field-required">*</span></label>
                            <input type="email" name="mail" id="mail" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                        </p>
                        <p>
                            <label for="url"><?php _e('网站'); ?></label>
                            <input type="url" name="url" id="url" placeholder="https://example.com" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="slowcloud-comment-field">
                    <?php if (slowcloud_seo_is($this, 'post')): ?>
                    <div class="slowcloud-comment-tools">
                        <span
                            class="slowcloud-comment-emoji-toggle iconfont icon-slowcloudemoji"
                            data-slowcloud-emoji-toggle
                            aria-expanded="false"
                            aria-controls="slowcloud-comment-emoji-panel"
                            aria-label="<?php _e('打开表情面板'); ?>"
                            role="button"
                            tabindex="0"
                        ></span>
                        <div
                            id="slowcloud-comment-emoji-panel"
                            class="slowcloud-comment-emoji-panel"
                            data-slowcloud-emoji-panel
                            hidden
                        >
                            <?php $emojiGroups = slowcloud_comment_emoji_groups($this); ?>
                            <div class="slowcloud-owo" data-slowcloud-owo>
                                <div class="slowcloud-owo-tabs" role="tablist" aria-label="<?php _e('表情分类'); ?>">
                                    <?php $isFirstEmojiTab = true; ?>
                                    <?php foreach ($emojiGroups as $groupKey => $group): ?>
                                        <button
                                            type="button"
                                            class="slowcloud-owo-tab<?php if ($isFirstEmojiTab): ?> is-active<?php endif; ?>"
                                            data-slowcloud-emoji-tab
                                            data-target="<?php echo htmlspecialchars($groupKey, ENT_QUOTES, $this->options->charset); ?>"
                                            role="tab"
                                            aria-selected="<?php echo $isFirstEmojiTab ? 'true' : 'false'; ?>"
                                        >
                                            <?php echo htmlspecialchars((string) $group['label'], ENT_QUOTES, $this->options->charset); ?>
                                        </button>
                                        <?php $isFirstEmojiTab = false; ?>
                                    <?php endforeach; ?>
                                </div>

                                <div class="slowcloud-owo-panels">
                                    <?php $isFirstEmojiGroup = true; ?>
                                    <?php foreach ($emojiGroups as $groupKey => $group): ?>
                                        <div
                                            class="slowcloud-owo-group<?php if ($isFirstEmojiGroup): ?> is-active<?php endif; ?>"
                                            data-slowcloud-emoji-group="<?php echo htmlspecialchars($groupKey, ENT_QUOTES, $this->options->charset); ?>"
                                            role="tabpanel"
                                            <?php if (!$isFirstEmojiGroup): ?>hidden<?php endif; ?>
                                        >
                                            <?php foreach ($group['items'] as $item): ?>
                                                <?php
                                                $isImage = !empty($item['image']) && !empty($item['url']);
                                                $itemClass = 'slowcloud-comment-emoji-item';
                                                if ($isImage) {
                                                    $itemClass .= ' slowcloud-comment-emoji-item-image';
                                                } elseif ($groupKey === 'kaomoji') {
                                                    $itemClass .= ' slowcloud-comment-emoji-item-text';
                                                }
                                                ?>
                                                <button
                                                    type="button"
                                                    class="<?php echo htmlspecialchars($itemClass, ENT_QUOTES, $this->options->charset); ?>"
                                                    data-slowcloud-emoji="<?php echo htmlspecialchars((string) $item['value'], ENT_QUOTES, $this->options->charset); ?>"
                                                    aria-label="<?php echo htmlspecialchars((string) $item['name'], ENT_QUOTES, $this->options->charset); ?>"
                                                ><?php if ($isImage): ?><img src="<?php echo htmlspecialchars((string) $item['url'], ENT_QUOTES, $this->options->charset); ?>" alt="<?php echo htmlspecialchars((string) $item['name'], ENT_QUOTES, $this->options->charset); ?>" loading="lazy"><?php else: ?><?php echo htmlspecialchars((string) $item['value'], ENT_QUOTES, $this->options->charset); ?><?php endif; ?></button>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php $isFirstEmojiGroup = false; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <textarea rows="5" name="text" id="textarea" placeholder="<?php _e('写点什么吧…'); ?>" required><?php $this->remember('text'); ?></textarea>
                </div>

                <p class="slowcloud-comment-submit">
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
