<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $slowcloudPostHasCodeBlocks = slowcloud_post_has_code_blocks($this); ?>
<?php if ($slowcloudPostHasCodeBlocks): ?>
    <?php $slowcloudPrismComponentsUrl = rtrim((string) $this->options->themeUrl('assets/typecho/prism/components', 'slowcloud'), '/') . '/'; ?>
<?php endif; ?>
<?php if (slowcloud_basic_layout($this) !== 'classic'): ?>
    <?php $this->need('components/site-footer.php'); ?>
<?php endif; ?>
</div>

<script src="<?php echo htmlspecialchars(slowcloud_theme_versioned_theme_url('assets/js/main.js', $this), ENT_QUOTES, $this->options->charset); ?>"></script>
<?php if ($slowcloudPostHasCodeBlocks): ?>
    <script>
    window.Prism = window.Prism || {};
    window.Prism.manual = true;
    window.SlowcloudCodeHighlight = {
        themeMode: <?php echo json_encode(slowcloud_theme_mode($this), JSON_UNESCAPED_UNICODE); ?>,
        prism: {
            components: <?php echo json_encode($slowcloudPrismComponentsUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
        }
    };
    </script>
    <script src="<?php echo htmlspecialchars(slowcloud_theme_versioned_theme_url('assets/typecho/prism/prism.js', $this), ENT_QUOTES, $this->options->charset); ?>"></script>
    <script src="<?php echo htmlspecialchars(slowcloud_theme_versioned_theme_url('assets/typecho/prism/plugins/autoloader/prism-autoloader.js', $this), ENT_QUOTES, $this->options->charset); ?>" data-autoloader-path="<?php echo htmlspecialchars($slowcloudPrismComponentsUrl, ENT_QUOTES, $this->options->charset); ?>"></script>
    <script src="<?php echo htmlspecialchars(slowcloud_theme_versioned_theme_url('assets/typecho/prism/plugins/line-numbers/prism-line-numbers.js', $this), ENT_QUOTES, $this->options->charset); ?>"></script>
    <script src="<?php echo htmlspecialchars(slowcloud_theme_versioned_theme_url('assets/js/code-highlight.js', $this), ENT_QUOTES, $this->options->charset); ?>"></script>
<?php endif; ?>
<?php slowcloud_record_excluded_bot_visit($this); ?>
<?php slowcloud_track_site_visit($this); ?>
<?php $this->footer(); ?>
</body>
</html>
