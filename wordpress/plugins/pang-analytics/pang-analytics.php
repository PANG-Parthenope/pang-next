<?php
/**
 * Plugin Name: PANG Analytics
 * Description: Cloudflare Web Analytics integration for panglab.eu. Excludes logged-in users from tracking.
 * Version: 0.1.1
 * Author: PArthenope Navigation Group
 */

if (!defined('ABSPATH')) exit;

define('PANG_ANALYTICS_VERSION', '0.1.1');
define('PANG_ANALYTICS_CF_TOKEN', '289780d708e646cd8b091353badbdd66');

add_action('wp_footer', function () {
    if (is_admin() || is_user_logged_in()) {
        return;
    }

    $host = isset($_SERVER['HTTP_HOST'])
        ? strtolower(preg_replace('/:\\d+$/', '', sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']))))
        : '';

    $allowed_hosts = array(
        'panglab.eu',
        'www.panglab.eu',
    );

    if (!in_array($host, $allowed_hosts, true)) {
        return;
    }
    ?>
    <!-- Cloudflare Web Analytics -->
    <script type='module'
            src='https://static.cloudflareinsights.com/beacon.min.js'
            data-cf-beacon='{"token": "<?php echo esc_attr(PANG_ANALYTICS_CF_TOKEN); ?>"}'></script>
    <!-- End Cloudflare Web Analytics -->
    <?php
}, 100);
