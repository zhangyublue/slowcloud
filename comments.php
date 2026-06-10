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

                <p class="slowcloud-comment-field">
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
                            <div class="slowcloud-owo" data-slowcloud-owo>
                                <div class="slowcloud-owo-tabs" role="tablist" aria-label="<?php _e('表情分类'); ?>">
                                    <button
                                        type="button"
                                        class="slowcloud-owo-tab is-active"
                                        data-slowcloud-emoji-tab
                                        data-target="emoji"
                                        role="tab"
                                        aria-selected="true"
                                    >
                                        emoji
                                    </button>
                                    <button
                                        type="button"
                                        class="slowcloud-owo-tab"
                                        data-slowcloud-emoji-tab
                                        data-target="kaomoji"
                                        role="tab"
                                        aria-selected="false"
                                    >
                                        颜文字
                                    </button>
                                </div>

                                <div class="slowcloud-owo-panels">
                                    <div class="slowcloud-owo-group is-active" data-slowcloud-emoji-group="emoji" role="tabpanel">
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="😀" aria-label="<?php _e('微笑'); ?>">😀</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="😆" aria-label="<?php _e('开心'); ?>">😆</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🥹" aria-label="<?php _e('感动'); ?>">🥹</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="😂" aria-label="<?php _e('大笑'); ?>">😂</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🥰" aria-label="<?php _e('喜欢'); ?>">🥰</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🤔" aria-label="<?php _e('思考'); ?>">🤔</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="😌" aria-label="<?php _e('放松'); ?>">😌</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="😴" aria-label="<?php _e('困了'); ?>">😴</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🙌" aria-label="<?php _e('庆祝'); ?>">🙌</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="✨" aria-label="<?php _e('闪亮'); ?>">✨</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="☁️" aria-label="<?php _e('云朵'); ?>">☁️</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🌙" aria-label="<?php _e('月亮'); ?>">🌙</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🍵" aria-label="<?php _e('喝茶'); ?>">🍵</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🌿" aria-label="<?php _e('植物'); ?>">🌿</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="🎈" aria-label="<?php _e('气球'); ?>">🎈</button>
                                        <button type="button" class="slowcloud-comment-emoji-item" data-slowcloud-emoji="💭" aria-label="<?php _e('想法'); ?>">💭</button>
                                    </div>

                                    <div class="slowcloud-owo-group" data-slowcloud-emoji-group="kaomoji" role="tabpanel" hidden>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="|´・ω・)ノ" aria-label="|´・ω・)ノ">|´・ω・)ノ</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="ヾ(≧▽≦*)o" aria-label="ヾ(≧▽≦*)o">ヾ(≧▽≦*)o</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(*/ω＼*)" aria-label="(*/ω＼*)">(*/ω＼*)</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(๑•̀ㅂ•́)و✧" aria-label="(๑•̀ㅂ•́)و✧">(๑•̀ㅂ•́)و✧</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(╯°□°）╯︵ ┻━┻" aria-label="(╯°□°）╯︵ ┻━┻">(╯°□°）╯︵ ┻━┻</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="φ(゜▽゜*)♪" aria-label="φ(゜▽゜*)♪">φ(゜▽゜*)♪</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(～￣▽￣)～" aria-label="(～￣▽￣)～">(～￣▽￣)～</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="Σ(っ °Д °;)っ" aria-label="Σ(っ °Д °;)っ">Σ(っ °Д °;)っ</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="QAQ" aria-label="QAQ">QAQ</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="( •̀ ω •́ )✧" aria-label="( •̀ ω •́ )✧">( •̀ ω •́ )✧</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(❁´◡`❁)" aria-label="(❁´◡`❁)">(❁´◡`❁)</button>
                                        <button type="button" class="slowcloud-comment-emoji-item slowcloud-comment-emoji-item-text" data-slowcloud-emoji="(っ °Д °;)っ" aria-label="(っ °Д °;)っ">(っ °Д °;)っ</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <textarea rows="5" name="text" id="textarea" placeholder="<?php _e('写点什么吧…'); ?>" required><?php $this->remember('text'); ?></textarea>
                </p>

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
