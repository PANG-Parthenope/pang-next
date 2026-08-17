<?php
/**
 * Plugin Name: PANG Footer Minimal
 * Description: Minimal PANG footer inspired by the approved mockup: institutional identity, group email, LinkedIn and copyright.
 * Version: 0.1.1
 * Author: PArthenope Navigation Group
 */

if (!defined('ABSPATH')) exit;

define('PANG_FOOTER_MIN_VERSION', '0.1.1');

function pang_footer_min_defaults() {
    return array(
        'email'        => '',
        'linkedin_url' => '',
        'institution'  => 'University of Naples Parthenope',
    );
}

function pang_footer_min_options() {
    return wp_parse_args(get_option('pang_footer_min_options', array()), pang_footer_min_defaults());
}

add_action('admin_init', function () {
    register_setting('pang_footer_min_settings', 'pang_footer_min_options', array(
        'sanitize_callback' => function ($input) {
            $defaults = pang_footer_min_defaults();
            return array(
                'email'        => isset($input['email']) ? sanitize_email($input['email']) : '',
                'linkedin_url' => isset($input['linkedin_url']) ? esc_url_raw($input['linkedin_url']) : '',
                'institution'  => isset($input['institution']) ? sanitize_text_field($input['institution']) : $defaults['institution'],
            );
        }
    ));
});

add_action('admin_menu', function () {
    add_theme_page('PANG Footer', 'PANG Footer', 'manage_options', 'pang-footer-minimal', 'pang_footer_min_settings_page');
});

function pang_footer_min_settings_page() {
    if (!current_user_can('manage_options')) return;
    $o = pang_footer_min_options();
    ?>
    <div class="wrap">
        <h1>PANG Footer</h1>
        <p>Configure the contact details shown in the footer.</p>
        <form method="post" action="options.php">
            <?php settings_fields('pang_footer_min_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="pang_footer_email">Group email</label></th>
                    <td>
                        <input id="pang_footer_email" class="regular-text" type="email"
                               name="pang_footer_min_options[email]"
                               value="<?php echo esc_attr($o['email']); ?>"
                               placeholder="group@uniparthenope.it">
                        <p class="description">Leave empty to hide the email row.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pang_footer_linkedin">LinkedIn URL</label></th>
                    <td>
                        <input id="pang_footer_linkedin" class="regular-text" type="url"
                               name="pang_footer_min_options[linkedin_url]"
                               value="<?php echo esc_attr($o['linkedin_url']); ?>"
                               placeholder="https://www.linkedin.com/...">
                        <p class="description">Leave empty to hide LinkedIn.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pang_footer_institution">Institution</label></th>
                    <td>
                        <input id="pang_footer_institution" class="regular-text" type="text"
                               name="pang_footer_min_options[institution]"
                               value="<?php echo esc_attr($o['institution']); ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('wp_head', function () {
    if (is_admin()) return;
    echo '<style id="pang-footer-min-hide-blocksy">footer.ct-footer,.ct-footer{display:none!important}</style>';
}, 99);

add_action('wp_footer', function () {
    if (is_admin()) return;
    $o = pang_footer_min_options();
    ?>
    <footer class="pang-footer-min" role="contentinfo">
        <div class="pang-footer-min__inner">
            <div class="pang-footer-min__top">
                <div class="pang-footer-min__identity">
                    <div class="pang-footer-min__name">PANG</div>
                    <div class="pang-footer-min__full">PArthenope Navigation Group</div>
                    
                </div>

                <div class="pang-footer-min__contact">
                    <div class="pang-footer-min__heading">CONTACT</div>

                    <?php if (!empty($o['email'])) : ?>
                        <a class="pang-footer-min__contact-row" href="mailto:<?php echo esc_attr($o['email']); ?>">
                            <span class="pang-footer-min__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M3 5h18v14H3V5zm1.8 1.5L12 12l7.2-5.5H4.8zM4.5 8v9.5h15V8L12 13.8 4.5 8z"/>
                                </svg>
                            </span>
                            <span><?php echo esc_html($o['email']); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($o['linkedin_url'])) : ?>
                        <a class="pang-footer-min__contact-row"
                           href="<?php echo esc_url($o['linkedin_url']); ?>"
                           target="_blank" rel="noopener noreferrer">
                            <span class="pang-footer-min__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M5.3 3.8A2.3 2.3 0 1 1 5.3 8.4 2.3 2.3 0 0 1 5.3 3.8zM3.3 9.7h4v10.9h-4V9.7zm6.5 0h3.8v1.5h.1c.5-1 1.9-2 3.9-2 4.2 0 5 2.8 5 6.4v5h-4v-4.4c0-1.1 0-2.5-1.5-2.5s-1.8 1.2-1.8 2.4v4.5h-4V9.7z"/>
                                </svg>
                            </span>
                            <span>LinkedIn</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pang-footer-min__bottom">
                &copy; <?php echo esc_html(wp_date('Y')); ?> PANG · <?php echo esc_html($o['institution']); ?>
            </div>
        </div>
    </footer>
    <?php
}, 100);

add_action('wp_enqueue_scripts', function () {
    wp_register_style('pang-footer-min', false, array(), PANG_FOOTER_MIN_VERSION);
    wp_enqueue_style('pang-footer-min');

    $css = <<<'CSS'
.pang-footer-min{
    background:#071c33;
    color:#d9e3ee;
    margin-top:50px;
}
.pang-footer-min__inner{
    width:min(1180px,calc(100% - 48px));
    margin:0 auto;
    padding:40px 0 0;
}
.pang-footer-min__top{
    display:grid;
    grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr);
    gap:56px;
    align-items:start;
    padding-bottom:32px;
}
.pang-footer-min__name{
    color:#fff;
    font-size:1.55rem;
    font-weight:750;
    letter-spacing:.08em;
    line-height:1.15;
    margin-bottom:12px;
}
.pang-footer-min__full{
    color:#c7d5e3;
    font-size:1rem;
    line-height:1.5;
    margin-bottom:16px;
}
.pang-footer-min__institution{
    color:#fff;
    font-size:1rem;
    line-height:1.5;
}
.pang-footer-min__contact{
    justify-self:end;
    min-width:280px;
}
.pang-footer-min__heading{
    color:#fff;
    font-size:.92rem;
    font-weight:700;
    letter-spacing:.13em;
    margin-bottom:15px;
}
.pang-footer-min__contact-row{
    display:flex;
    align-items:center;
    gap:14px;
    color:#e6edf5;
    text-decoration:none;
    margin:0 0 12px;
    font-size:1rem;
    line-height:1.4;
}
.pang-footer-min__contact-row:hover,
.pang-footer-min__contact-row:focus{
    color:#fff;
    text-decoration:underline;
    text-underline-offset:3px;
}
.pang-footer-min__contact-row:focus-visible{
    outline:2px solid currentColor;
    outline-offset:4px;
}
.pang-footer-min__icon{
    width:24px;
    height:24px;
    flex:0 0 24px;
}
.pang-footer-min__icon svg{
    display:block;
    width:100%;
    height:100%;
    fill:currentColor;
}
.pang-footer-min__bottom{
    border-top:1px solid rgba(215,228,240,.22);
    padding:15px 0 17px;
    color:#9fb0c2;
    text-align:center;
    font-size:.82rem;
    line-height:1.5;
}
@media(max-width:760px){
    .pang-footer-min{
        margin-top:40px;
    }
    .pang-footer-min__inner{
        width:min(100% - 36px,1180px);
        padding-top:30px;
    }
    .pang-footer-min__top{
        grid-template-columns:1fr;
        gap:26px;
        padding-bottom:27px;
    }
    .pang-footer-min__contact{
        justify-self:start;
        min-width:0;
    }
}
CSS;

    wp_add_inline_style('pang-footer-min', $css);
}, 20);
