<?php
/**
 * 时光轴页面
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>
<?php $this->need('header.php'); ?>
<?php $timeline = slowcloud_timeline_data(); ?>
<?php $timelineYears = $timeline['years']; ?>
<?php $timelineStats = $timeline['stats']; ?>

<main
    class="slowcloud-layout-shell slowcloud-layout-shell-timeline"
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

    <section class="slowcloud-content-column slowcloud-content-column-timeline">
        <div class="slowcloud-page-lead">
            <p class="slowcloud-page-eyebrow"><?php _e('时间归档'); ?></p>
            <h1 class="slowcloud-page-title"><?php $this->title(); ?></h1>
            <p class="slowcloud-page-summary"><?php _e('沿着发布时间回看每一篇文章，把分散的记录重新串成一条清晰的轨迹。'); ?></p>
        </div>

        <?php if (!empty($timelineYears)): ?>
            <section class="slowcloud-timeline-summary" aria-label="<?php _e('时光轴概览'); ?>">
                <div class="slowcloud-timeline-summary-card">
                    <span class="slowcloud-timeline-summary-label"><?php _e('文章'); ?></span>
                    <strong class="slowcloud-timeline-summary-value"><?php echo (int) $timelineStats['total']; ?></strong>
                </div>
                <div class="slowcloud-timeline-summary-card">
                    <span class="slowcloud-timeline-summary-label"><?php _e('月份'); ?></span>
                    <strong class="slowcloud-timeline-summary-value"><?php echo (int) $timelineStats['months']; ?></strong>
                </div>
                <div class="slowcloud-timeline-summary-card">
                    <span class="slowcloud-timeline-summary-label"><?php _e('最近更新'); ?></span>
                    <strong class="slowcloud-timeline-summary-value"><?php echo date('Y.m.d', (int) $timelineStats['latest']); ?></strong>
                </div>
                <div class="slowcloud-timeline-summary-card">
                    <span class="slowcloud-timeline-summary-label"><?php _e('最早记录'); ?></span>
                    <strong class="slowcloud-timeline-summary-value"><?php echo date('Y.m.d', (int) $timelineStats['earliest']); ?></strong>
                </div>
            </section>

            <nav class="slowcloud-timeline-year-nav" aria-label="<?php _e('年份导航'); ?>">
                <?php foreach ($timelineYears as $timelineYear): ?>
                    <a href="#timeline-year-<?php echo htmlspecialchars($timelineYear['year'], ENT_QUOTES, $this->options->charset); ?>"><?php echo htmlspecialchars($timelineYear['year'], ENT_QUOTES, $this->options->charset); ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="slowcloud-timeline">
                <?php foreach ($timelineYears as $timelineYear): ?>
                    <section id="timeline-year-<?php echo htmlspecialchars($timelineYear['year'], ENT_QUOTES, $this->options->charset); ?>" class="slowcloud-timeline-year">
                        <header class="slowcloud-timeline-year-header">
                            <h2 class="slowcloud-timeline-year-title"><?php echo htmlspecialchars($timelineYear['year'], ENT_QUOTES, $this->options->charset); ?></h2>
                        </header>

                        <?php foreach ($timelineYear['months'] as $timelineMonth): ?>
                            <div class="slowcloud-timeline-month">
                                <div class="slowcloud-timeline-month-label"><?php echo htmlspecialchars($timelineMonth['label'], ENT_QUOTES, $this->options->charset); ?></div>
                                <div class="slowcloud-timeline-month-list">
                                    <?php foreach ($timelineMonth['items'] as $timelineItem): ?>
                                        <article class="slowcloud-timeline-item">
                                            <time class="slowcloud-timeline-item-date" datetime="<?php echo htmlspecialchars($timelineItem['datetime'], ENT_QUOTES, $this->options->charset); ?>"><?php echo htmlspecialchars($timelineItem['date'], ENT_QUOTES, $this->options->charset); ?></time>
                                            <div class="slowcloud-timeline-item-main">
                                                <div class="slowcloud-timeline-item-body">
                                                    <?php if ($timelineItem['poster'] !== ''): ?>
                                                        <a class="slowcloud-timeline-item-poster-link" href="<?php echo htmlspecialchars($timelineItem['permalink'], ENT_QUOTES, $this->options->charset); ?>" aria-label="<?php echo htmlspecialchars($timelineItem['title'], ENT_QUOTES, $this->options->charset); ?>">
                                                            <img class="slowcloud-timeline-item-poster" src="<?php echo htmlspecialchars($timelineItem['poster'], ENT_QUOTES, $this->options->charset); ?>" alt="<?php echo htmlspecialchars($timelineItem['title'], ENT_QUOTES, $this->options->charset); ?>" loading="lazy" decoding="async">
                                                        </a>
                                                    <?php endif; ?>

                                                    <div class="slowcloud-timeline-item-copy">
                                                        <h3 class="slowcloud-timeline-item-title">
                                                            <a href="<?php echo htmlspecialchars($timelineItem['permalink'], ENT_QUOTES, $this->options->charset); ?>"><?php echo htmlspecialchars($timelineItem['title'], ENT_QUOTES, $this->options->charset); ?></a>
                                                        </h3>
                                                        <p class="slowcloud-timeline-item-meta">
                                                            <span><?php echo htmlspecialchars($timelineItem['category'], ENT_QUOTES, $this->options->charset); ?></span>
                                                            <span><?php echo htmlspecialchars(sprintf(_t('%d 次浏览'), (int) $timelineItem['views']), ENT_QUOTES, $this->options->charset); ?></span>
                                                        </p>
                                                        <?php if ($timelineItem['summary'] !== ''): ?>
                                                            <p class="slowcloud-timeline-item-summary"><?php echo htmlspecialchars($timelineItem['summary'], ENT_QUOTES, $this->options->charset); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <article class="slowcloud-empty-card">
                <p><?php _e('还没有文章，等第一篇内容出现后，这里会自动生成时光轴。'); ?></p>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php $this->need('footer.php'); ?>
