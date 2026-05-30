<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
    <footer class="slowcloud-site-footer">
        <div class="slowcloud-footer-inner">
            <p>&copy; <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a></p>
            <p><?php _e('由 Typecho 驱动，慢慢记录生活。'); ?></p>
        </div>
    </footer>
</div>

<script src="<?php $this->options->themeUrl('assets/js/main.js'); ?>"></script>
<?php $this->footer(); ?>
</body>
</html>
