<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$slowcloudIcpBeian = slowcloud_icp_beian($this);
$slowcloudIcpBeianUrl = slowcloud_icp_beian_url($this);
$slowcloudPublicSecurityBeian = slowcloud_public_security_beian($this);
$slowcloudPublicSecurityBeianUrl = slowcloud_public_security_beian_url($this);
?>
    <footer class="slowcloud-site-footer">
        <div class="slowcloud-footer-inner">
            <div class="slowcloud-footer-line">
                <p>&copy; <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a></p>
                <?php if ($slowcloudIcpBeian !== '' || $slowcloudPublicSecurityBeian !== ''): ?>
                    <p class="slowcloud-footer-beian">
                    <?php if ($slowcloudIcpBeian !== ''): ?>
                        <a href="<?php echo htmlspecialchars($slowcloudIcpBeianUrl, ENT_QUOTES, $this->options->charset); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo htmlspecialchars($slowcloudIcpBeian, ENT_QUOTES, $this->options->charset); ?></a>
                    <?php endif; ?>
                    <?php if ($slowcloudPublicSecurityBeian !== ''): ?>
                        <a href="<?php echo htmlspecialchars($slowcloudPublicSecurityBeianUrl, ENT_QUOTES, $this->options->charset); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo htmlspecialchars($slowcloudPublicSecurityBeian, ENT_QUOTES, $this->options->charset); ?></a>
                    <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</div>

<?php slowcloud_track_site_visit($this); ?>
<script src="<?php $this->options->themeUrl('assets/js/main.js'); ?>"></script>
<?php $this->footer(); ?>
</body>
</html>
