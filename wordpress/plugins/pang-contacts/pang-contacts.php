<?php
/**
 * Plugin Name: PANG Contacts
 * Description: Creates and renders the PANG Contacts page with location, email, phone, collaboration and news links.
 * Version: 0.1.5
 * Author: PArthenope Navigation Group
 */

if (!defined('ABSPATH')) exit;

define('PANG_CONTACTS_VERSION', '0.1.5');

function pang_contacts_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : home_url('/' . trim($slug, '/') . '/');
}

function pang_contacts_create_page() {
    $page = get_page_by_path('contacts');

    if (!$page) {
        wp_insert_post(array(
            'post_title'   => 'Contacts',
            'post_name'    => 'contacts',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '[pang_contacts]',
        ));
        return;
    }

    if (strpos((string)$page->post_content, '[pang_contacts]') === false) {
        wp_update_post(array(
            'ID'           => $page->ID,
            'post_content' => '[pang_contacts]',
        ));
    }
}
register_activation_hook(__FILE__, 'pang_contacts_create_page');

add_shortcode('pang_contacts', function () {
    $email = 'pang@uniparthenope.it';
    $phone_display = '+390815476610';
    $phone_href = '+390815476610';

    $news_url = pang_contacts_page_url('news');

    ob_start();
    ?>
    <section class="pang-contacts" aria-labelledby="pang-contacts-title">
        <div class="pang-contacts__inner">

            <header class="pang-contacts__header">
                <h1 id="pang-contacts-title">Contacts</h1>
                <div class="pang-contacts__title-line" aria-hidden="true"></div>
                <p>
                    We collaborate with academic institutions, research organizations,
                    public authorities and industry partners worldwide. Get in touch with us!
                </p>
            </header>

            <div class="pang-contacts__grid">

                <div class="pang-contacts__details">

                    <div class="pang-contact-item">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 21s7-6 7-12a7 7 0 1 0-14 0c0 6 7 12 7 12Zm0-9.2A2.8 2.8 0 1 1 12 6a2.8 2.8 0 0 1 0 5.8Z"/></svg>
                        </span>
                        <div>
                            <h2>Our Location</h2>
                            <p>
                                University of Naples Parthenope<br>
                                Centro Direzionale Isola C4<br>
                                80143 Naples, Italy
                            </p>
                        </div>
                    </div>

                    <div class="pang-contact-item pang-contact-item--laboratory">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M9 2h6v2h-1v5.2l5.5 8.8A2.6 2.6 0 0 1 17.3 22H6.7a2.6 2.6 0 0 1-2.2-4L10 9.2V4H9V2Zm3 8-5.8 9.2c-.2.3 0 .8.5.8h10.6c.5 0 .7-.5.5-.8L12 10Zm-3 6h6l1.3 2H7.7L9 16Z"/></svg>
                        </span>
                        <div>
                            <h2>PANG Laboratory</h2>
                            <p>
                                <strong>“G. Simeon” Navigation Laboratory</strong><br>
                                4th floor, South Wing<br>
                                Centro Direzionale di Napoli – Isola C4<br>
                                Scientific Coordinator: <strong>Prof. Salvatore Gaglione</strong>
                            </p>
                        </div>
                    </div>

                    <div class="pang-contact-item">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm2 2v.4l7 5.2 7-5.2V7l-7 5.2L5 7Z"/></svg>
                        </span>
                        <div>
                            <h2>Email</h2>
                            <p><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                        </div>
                    </div>

                    <div class="pang-contact-item">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M6.6 2.8 4.2 4.4c-.7.5-1 1.4-.7 2.2 1.9 6.1 6.8 11 12.9 12.9.8.3 1.7 0 2.2-.7l1.6-2.4c.4-.6.3-1.4-.3-1.8l-3.4-2.5c-.5-.4-1.3-.3-1.7.2l-1.4 1.8a14.2 14.2 0 0 1-3.5-2.6 14.2 14.2 0 0 1-2.6-3.5L9 6.6c.5-.4.6-1.2.2-1.7L6.7 1.5c-.4-.6-1.2-.7-1.8-.3Z"/></svg>
                        </span>
                        <div>
                            <h2>Phone</h2>
                            <p><a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone_display); ?></a></p>
                        </div>
                    </div>

                    <div class="pang-contact-item">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-3.1a15.9 15.9 0 0 0-1.4-3.5A8.1 8.1 0 0 1 18.9 8ZM12 4c.8 1 1.5 2.4 1.9 4h-3.8C10.5 6.4 11.2 5 12 4ZM4.6 14A8 8 0 0 1 4 12c0-.7.1-1.4.3-2h3.4a16.5 16.5 0 0 0 0 4H4.6Zm.5 2h3.1a15.9 15.9 0 0 0 1.4 3.5A8.1 8.1 0 0 1 5.1 16Zm3.1-8H5.1a8.1 8.1 0 0 1 4.5-3.5A15.9 15.9 0 0 0 8.2 8Zm3.8 12c-.8-1-1.5-2.4-1.9-4h3.8c-.4 1.6-1.1 3-1.9 4Zm2.3-6H9.7a14.5 14.5 0 0 1 0-4h4.6a14.5 14.5 0 0 1 0 4Zm.1 5.5a15.9 15.9 0 0 0 1.4-3.5h3.1a8.1 8.1 0 0 1-4.5 3.5ZM16.3 14a16.5 16.5 0 0 0 0-4h3.4c.2.6.3 1.3.3 2s-.1 1.4-.3 2h-3.4Z"/></svg>
                        </span>
                        <div>
                            <h2>University</h2>
                            <p><a href="https://www.uniparthenope.it" target="_blank" rel="noopener noreferrer">www.uniparthenope.it</a></p>
                        </div>
                    </div>

                    <div class="pang-contact-item">
                        <span class="pang-contact-item__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 21V8l9-5 9 5v13h-6v-6H9v6H3Zm2-2h2v-6h10v6h2V9.2l-7-3.9-7 3.9V19Zm3-8h2V9H8v2Zm6 0h2V9h-2v2Z"/></svg>
                        </span>
                        <div>
                            <h2>Department of Science and Technology</h2>
                            <p><a href="https://www.scienzeetecnologie.uniparthenope.it/" target="_blank" rel="noopener noreferrer">www.scienzeetecnologie.uniparthenope.it</a></p>
                        </div>
                    </div>

                </div>

                <div class="pang-contacts__right">

                    <div class="pang-contacts__map-card" aria-label="Map showing the University of Naples Parthenope at Centro Direzionale, Naples">
                        <iframe
                            class="pang-contacts__map-frame"
                            title="University of Naples Parthenope - Centro Direzionale Isola C4"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            src="https://www.google.com/maps?q=Universit%C3%A0%20degli%20Studi%20di%20Napoli%20Parthenope%20Centro%20Direzionale%20Isola%20C4%20Napoli&amp;output=embed">
                        </iframe>

                        <div class="pang-contacts__map-note">
                            <strong>PANG at the University of Naples Parthenope</strong>
                            <span>“G. Simeon” Navigation Laboratory · 4th floor, South Wing</span>
                        </div>
                    </div>

                    <div class="pang-connect">
                        <h2>Connect with PANG</h2>

                        <div class="pang-connect__cards">
                            <article class="pang-connect-card">
                                <span class="pang-connect-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M8.5 11.5 6 14a3.5 3.5 0 0 0 5 5l2.5-2.5m2-4 2.5-2.5a3.5 3.5 0 0 0-5-5L10.5 7.5m-2 8 7-7"/></svg>
                                </span>
                                <h3>Collaborate with Us</h3>
                                <p>We welcome research collaborations and partnerships.</p>
                                <a href="mailto:<?php echo esc_attr($email); ?>">Contact us →</a>
                            </article>

                            <article class="pang-connect-card">
                                <span class="pang-connect-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M3 12 21 3l-7.5 18-2.6-6.3L3 12Zm7.9 2.7 2.4 2.4 3.7-9-6.1 6.6Z"/></svg>
                                </span>
                                <h3>Stay Tuned</h3>
                                <p>Follow our latest news, activities and research updates.</p>
                                <a href="<?php echo esc_url($news_url); ?>">Latest news →</a>
                            </article>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    wp_register_style('pang-contacts', false, array(), PANG_CONTACTS_VERSION);
    wp_enqueue_style('pang-contacts');

    $css = <<<'CSS'
.pang-contacts{
    padding:72px 0 84px;
}
.pang-contacts__inner{
    width:min(1200px,calc(100% - 48px));
    margin:0 auto;
}
.pang-contacts__header{
    text-align:center;
    max-width:820px;
    margin:0 auto 48px;
}
.pang-contacts__header h1{
    margin:0;
    color:#182b40;
    font-size:clamp(2.2rem,4vw,3.4rem);
    line-height:1.1;
}
.pang-contacts__title-line{
    width:56px;
    height:4px;
    border-radius:999px;
    background:#1677ff;
    margin:18px auto 20px;
}
.pang-contacts__header p{
    margin:0;
    color:#415977;
    font-size:1.08rem;
    line-height:1.65;
}
.pang-contacts__grid{
    display:grid;
    grid-template-columns:minmax(290px,.8fr) minmax(0,1.45fr);
    gap:38px;
    align-items:start;
}
.pang-contacts__details{
    padding-top:18px;
}
.pang-contact-item{
    display:grid;
    grid-template-columns:64px minmax(0,1fr);
    gap:22px;
    padding:24px 0;
    border-bottom:1px solid #d9e3ee;
}
.pang-contact-item:last-child{
    border-bottom:0;
}
.pang-contact-item__icon,
.pang-connect-card__icon{
    display:flex;
    align-items:center;
    justify-content:center;
    color:#1677ff;
}
.pang-contact-item__icon{
    width:64px;
    height:64px;
    border-radius:50%;
    background:#edf5ff;
}
.pang-contact-item__icon svg,
.pang-connect-card__icon svg{
    width:28px;
    height:28px;
    fill:currentColor;
}
.pang-contact-item h2{
    margin:3px 0 8px;
    color:#112b50;
    font-size:1.12rem;
}
.pang-contact-item p{
    margin:0;
    color:#314a68;
    line-height:1.6;
}
.pang-contact-item a{
    color:#1068df;
    text-decoration:none;
}
.pang-contact-item a:hover{
    text-decoration:underline;
}
.pang-contacts__right{
    display:grid;
    gap:24px;
}
.pang-contacts__map-card{
    position:relative;
    overflow:hidden;
    border:1px solid #d9e6f3;
    border-radius:16px;
    background:#f5f9fd;
    min-height:340px;
}
.pang-contacts__map-frame{
    width:100%;
    height:340px;
    border:0;
    display:block;
}
.pang-contacts__map-note{
    position:absolute;
    right:18px;
    bottom:18px;
    max-width:320px;
    padding:18px 20px;
    border:1px solid #d7e2ee;
    border-radius:12px;
    background:rgba(255,255,255,.94);
    box-shadow:0 10px 28px rgba(28,56,91,.08);
}
.pang-contacts__map-note strong{
    display:block;
    margin-bottom:6px;
    color:#172d4d;
}
.pang-contacts__map-note span{
    color:#465d79;
    font-size:.94rem;
    line-height:1.5;
}
.pang-connect{
    border:1px solid #dde6ef;
    border-radius:16px;
    background:#fff;
    padding:24px 28px 26px;
}
.pang-connect > h2{
    margin:0 0 18px;
    color:#112b50;
    font-size:1.2rem;
}
.pang-connect__cards{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.pang-connect-card{
    padding:10px 30px 6px;
    text-align:center;
}
.pang-connect-card + .pang-connect-card{
    border-left:1px solid #dfe7ef;
}
.pang-connect-card__icon{
    margin:0 auto 10px;
}
.pang-connect-card h3{
    margin:0 0 8px;
    color:#17315b;
    font-size:1rem;
}
.pang-connect-card p{
    margin:0 auto 13px;
    max-width:270px;
    color:#536880;
    font-size:.92rem;
    line-height:1.55;
}
.pang-connect-card a{
    color:#1068df;
    font-weight:600;
    text-decoration:none;
    font-size:.92rem;
}
.pang-connect-card a:hover{
    text-decoration:underline;
}
@media(max-width:900px){
    .pang-contacts__grid{
        grid-template-columns:1fr;
    }
    .pang-contacts__details{
        padding-top:0;
    }
}
@media(max-width:620px){
    .pang-contacts{
        padding:52px 0 60px;
    }
    .pang-contacts__inner{
        width:min(100% - 34px,1200px);
    }
    .pang-connect__cards{
        grid-template-columns:1fr;
    }
    .pang-connect-card{
        padding:18px 8px;
    }
    .pang-connect-card + .pang-connect-card{
        border-left:0;
        border-top:1px solid #dfe7ef;
    }
    .pang-contacts__map-note{
        position:static;
        margin:16px;
        max-width:none;
    }
}
CSS;

    wp_add_inline_style('pang-contacts', $css);
});
