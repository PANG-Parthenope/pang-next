<?php
/**
 * Plugin Name: PANG People Manager
 * Description: Simplified editorial interface for adding PANG People profiles.
 * Version: 0.1.0
 * Author: PArthenope Navigation Group
 */

if (!defined('ABSPATH')) exit;

define('PANG_PEOPLE_MANAGER_VERSION', '0.1.0');
define('PANG_PEOPLE_MANAGER_CAP', 'edit_pages');

/* -------------------------------------------------------------------------
 * Menu: reuse the existing PANG People top-level CPT menu.
 * Replace the standard Add New submenu with the simplified PANG form.
 * ---------------------------------------------------------------------- */
add_action('admin_menu', function () {
    if (!post_type_exists('pang_person') || !current_user_can(PANG_PEOPLE_MANAGER_CAP)) {
        return;
    }

    $parent = 'edit.php?post_type=pang_person';

    remove_submenu_page($parent, 'post-new.php?post_type=pang_person');

    add_submenu_page(
        $parent,
        'Add Person',
        'Add Person',
        PANG_PEOPLE_MANAGER_CAP,
        'pang-add-person',
        'pang_people_manager_add_page'
    );
}, 999);

/* -------------------------------------------------------------------------
 * Category helpers
 * ---------------------------------------------------------------------- */
function pang_people_manager_categories() {
    if (!taxonomy_exists('pang_person_category')) return array();

    $terms = get_terms(array(
        'taxonomy'   => 'pang_person_category',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms)) return array();

    $priority = array(
        'Faculty'            => 10,
        'Researchers'        => 20,
        'Associated Members' => 30,
        'Students'           => 40,
        'Former Members'     => 50,
        'Alumni'             => 50,
        'Past Members'       => 50,
    );

    usort($terms, function($a, $b) use ($priority) {
        $pa = $priority[$a->name] ?? 100;
        $pb = $priority[$b->name] ?? 100;
        if ($pa !== $pb) return $pa <=> $pb;
        return strcasecmp($a->name, $b->name);
    });

    return $terms;
}

/* -------------------------------------------------------------------------
 * Add Person page
 * ---------------------------------------------------------------------- */
function pang_people_manager_add_page() {
    if (!current_user_can(PANG_PEOPLE_MANAGER_CAP)) {
        wp_die('You do not have permission to add People.');
    }

    wp_enqueue_media();

    $created = isset($_GET['pang_person_created']) ? absint($_GET['pang_person_created']) : 0;
    if ($created) {
        echo '<div class="notice notice-success is-dismissible"><p>Person created successfully. ';
        echo '<a href="'.esc_url(get_edit_post_link($created)).'">Edit profile</a>';
        echo ' · <a href="'.esc_url(get_permalink($created)).'" target="_blank" rel="noopener">View profile</a>';
        echo '</p></div>';
    }

    $categories = pang_people_manager_categories();
    ?>
    <div class="wrap pang-people-editor">
        <h1>Add Person</h1>
        <p class="pang-people-editor__intro">
            Add a PANG member using only the fields required by the current People layout.
            The profile is stored directly in the same <code>pang_person</code> data structure used by PANG People.
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="pang-person-form">
            <input type="hidden" name="action" value="pang_people_manager_create">
            <?php wp_nonce_field('pang_people_manager_create', 'pang_people_manager_nonce'); ?>

            <div class="pang-person-grid pang-person-grid--top">
                <section class="pang-person-panel">
                    <label class="pang-person-label" for="pang_full_name">Full name <span>*</span></label>
                    <input class="large-text pang-person-name" type="text" id="pang_full_name" name="pang_full_name" required placeholder="e.g. Mario Rossi">

                    <label class="pang-person-label" for="pang_category">Category <span>*</span></label>
                    <select id="pang_category" name="pang_category" required>
                        <option value="">Select a category…</option>
                        <?php foreach ($categories as $term) : ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="pang-person-label" for="pang_academic_position">Academic position</label>
                    <input class="large-text" type="text" id="pang_academic_position" name="pang_academic_position" placeholder="e.g. Associate Professor">

                    <label class="pang-person-label" for="pang_affiliation">Institutional affiliation</label>
                    <input class="large-text" type="text" id="pang_affiliation" name="pang_affiliation" placeholder="e.g. University of Naples Parthenope">
                </section>

                <section class="pang-person-panel">
                    <label class="pang-person-label">Profile photo <span>*</span></label>
                    <p class="description">
                        Recommended: portrait image, at least <strong>800 × 1000 px</strong>, with the face centred and some space above the head.
                        Images smaller than 800 × 800 px are not accepted.
                    </p>

                    <input type="hidden" id="pang_photo_id" name="pang_photo_id" value="">
                    <div id="pang_photo_preview" class="pang-photo-preview">
                        <span class="dashicons dashicons-admin-users"></span>
                        <span>No photo selected</span>
                    </div>

                    <div id="pang_photo_info" class="pang-photo-info" aria-live="polite"></div>

                    <p>
                        <button type="button" class="button button-secondary" id="pang_select_photo">Select profile photo</button>
                        <button type="button" class="button button-link-delete" id="pang_remove_photo" style="display:none">Remove</button>
                    </p>

                    <label class="pang-person-label" for="pang_photo_position">
                        Photo vertical position
                        <span class="pang-value"><output id="pang_photo_position_value">50</output>%</span>
                    </label>
                    <input type="range" id="pang_photo_position" name="pang_photo_position" min="0" max="100" step="1" value="50">
                    <p class="description">0 = show more of the upper part; 50 = centre; 100 = show more of the lower part.</p>
                </section>
            </div>

            <section class="pang-person-panel">
                <label class="pang-person-label" for="pang_biography">Biography</label>
                <textarea class="large-text" id="pang_biography" name="pang_biography" rows="8" placeholder="Short professional biography"></textarea>
            </section>

            <section class="pang-person-panel">
                <label class="pang-person-label" for="pang_research_interests">Research interests</label>
                <textarea class="large-text" id="pang_research_interests" name="pang_research_interests" rows="6" placeholder="One research interest per line"></textarea>
                <p class="description">Enter one research interest per line.</p>
            </section>

            <div class="pang-person-grid pang-person-grid--links">
                <section class="pang-person-panel">
                    <label class="pang-person-label" for="pang_orcid">ORCID URL</label>
                    <input class="large-text" type="url" id="pang_orcid" name="pang_orcid" placeholder="https://orcid.org/...">
                </section>

                <section class="pang-person-panel">
                    <label class="pang-person-label" for="pang_google_scholar">Google Scholar URL</label>
                    <input class="large-text" type="url" id="pang_google_scholar" name="pang_google_scholar" placeholder="https://scholar.google.com/...">
                </section>

                <section class="pang-person-panel">
                    <label class="pang-person-label" for="pang_scopus">Scopus URL</label>
                    <input class="large-text" type="url" id="pang_scopus" name="pang_scopus" placeholder="https://www.scopus.com/...">
                </section>
            </div>

            <section class="pang-person-panel pang-person-publish">
                <div>
                    <label class="pang-person-label" for="pang_person_status">Status</label>
                    <select id="pang_person_status" name="pang_person_status">
                        <?php if (current_user_can('publish_pages')) : ?>
                            <option value="publish">Publish</option>
                        <?php endif; ?>
                        <option value="draft">Save as draft</option>
                    </select>
                </div>
                <p class="description">
                    New profiles are added at the end of their category. Their position can later be changed using <strong>People → Order People</strong>.
                </p>
            </section>

            <?php submit_button('Save Person', 'primary large'); ?>
        </form>
    </div>
    <?php
}

/* -------------------------------------------------------------------------
 * Create Person
 * ---------------------------------------------------------------------- */
add_action('admin_post_pang_people_manager_create', function () {
    if (!current_user_can(PANG_PEOPLE_MANAGER_CAP)) {
        wp_die('You do not have permission to add People.');
    }

    check_admin_referer('pang_people_manager_create', 'pang_people_manager_nonce');

    $name = isset($_POST['pang_full_name'])
        ? sanitize_text_field(wp_unslash($_POST['pang_full_name']))
        : '';

    $category_id = isset($_POST['pang_category']) ? absint($_POST['pang_category']) : 0;
    $photo_id = isset($_POST['pang_photo_id']) ? absint($_POST['pang_photo_id']) : 0;

    if ($name === '' || !$category_id || !$photo_id) {
        wp_die('Full name, category and profile photo are required.');
    }

    $term = get_term($category_id, 'pang_person_category');
    if (!$term || is_wp_error($term)) {
        wp_die('Invalid People category.');
    }

    if (!wp_attachment_is_image($photo_id)) {
        wp_die('The selected profile photo is not a valid image.');
    }

    $image_meta = wp_get_attachment_metadata($photo_id);
    $width  = isset($image_meta['width']) ? (int)$image_meta['width'] : 0;
    $height = isset($image_meta['height']) ? (int)$image_meta['height'] : 0;

    if ($width < 800 || $height < 800) {
        wp_die(
            'The selected profile photo is too small. '.
            'Please use an image of at least 800 × 800 px; 800 × 1000 px or larger in portrait orientation is recommended.'
        );
    }

    $status = isset($_POST['pang_person_status'])
        ? sanitize_key(wp_unslash($_POST['pang_person_status']))
        : 'draft';

    if ($status === 'publish' && !current_user_can('publish_pages')) {
        $status = 'draft';
    }

    $post_id = wp_insert_post(array(
        'post_type'   => 'pang_person',
        'post_title'  => $name,
        'post_status' => $status,
        'post_author' => get_current_user_id(),
        'menu_order'  => 999,
    ), true);

    if (is_wp_error($post_id)) {
        wp_die('Unable to create Person: '.esc_html($post_id->get_error_message()));
    }

    wp_set_object_terms($post_id, array($category_id), 'pang_person_category', false);
    set_post_thumbnail($post_id, $photo_id);

    $fields = array(
        'academic_position'  => 'text',
        'affiliation'        => 'text',
        'biography'          => 'textarea',
        'research_interests' => 'textarea',
        'orcid'              => 'url',
        'google_scholar'     => 'url',
        'scopus'             => 'url',
    );

    foreach ($fields as $key => $type) {
        $field_name = 'pang_'.$key;
        if (!isset($_POST[$field_name])) continue;

        $raw = wp_unslash($_POST[$field_name]);

        if ($type === 'url') {
            $value = esc_url_raw($raw);
        } elseif ($type === 'textarea') {
            $value = sanitize_textarea_field($raw);
        } else {
            $value = sanitize_text_field($raw);
        }

        update_post_meta($post_id, '_pang_'.$key, $value);
    }

    $photo_position = isset($_POST['pang_photo_position'])
        ? max(0, min(100, absint($_POST['pang_photo_position'])))
        : 50;

    update_post_meta($post_id, '_pang_photo_position', (string)$photo_position);

    wp_safe_redirect(
        add_query_arg(
            'pang_person_created',
            $post_id,
            admin_url('edit.php?post_type=pang_person&page=pang-add-person')
        )
    );
    exit;
});

/* -------------------------------------------------------------------------
 * Admin assets
 * ---------------------------------------------------------------------- */
add_action('admin_enqueue_scripts', function () {
    if (empty($_GET['page']) || $_GET['page'] !== 'pang-add-person') return;

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    wp_register_style('pang-people-manager-admin', false, array(), PANG_PEOPLE_MANAGER_VERSION);
    wp_enqueue_style('pang-people-manager-admin');

    $css = <<<'CSS'
.pang-people-editor{max-width:1180px}
.pang-people-editor__intro{max-width:900px;color:#50575e;margin-bottom:24px}
.pang-person-grid{display:grid;gap:20px}
.pang-person-grid--top{grid-template-columns:minmax(0,1.2fr) minmax(340px,.8fr)}
.pang-person-grid--links{grid-template-columns:repeat(3,minmax(0,1fr))}
.pang-person-panel{box-sizing:border-box;background:#fff;border:1px solid #dcdcde;border-radius:7px;padding:22px;margin:0 0 20px}
.pang-person-label{display:block;font-weight:600;font-size:14px;margin:0 0 9px}
.pang-person-label:not(:first-child){margin-top:20px}
.pang-person-label>span:not(.pang-value){color:#d63638}
.pang-person-name{font-size:20px!important;padding:8px 10px!important}
.pang-person-panel select{min-width:260px;max-width:100%}
.pang-photo-preview{width:260px;height:260px;border:2px dashed #c3c4c7;border-radius:50%;background:#f6f7f7;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;overflow:hidden;margin:18px auto;color:#646970}
.pang-photo-preview img{width:100%;height:100%;object-fit:cover;object-position:center 50%}
.pang-photo-preview .dashicons{font-size:48px;width:48px;height:48px}
.pang-photo-info{text-align:center;min-height:24px;font-weight:600}
.pang-photo-info.is-good{color:#008a20}
.pang-photo-info.is-warning{color:#b26200}
.pang-photo-info.is-error{color:#b32d2e}
#pang_photo_position{width:100%}
.pang-value{float:right;color:#50575e;font-weight:400}
.pang-person-publish{display:flex;align-items:end;gap:32px;flex-wrap:wrap}
.pang-person-publish .description{max-width:620px;margin:0}
@media(max-width:900px){
  .pang-person-grid--top,.pang-person-grid--links{grid-template-columns:1fr}
}
CSS;
    wp_add_inline_style('pang-people-manager-admin', $css);

    $js = <<<'JS'
jQuery(function($){
    var frame;

    function updateCropPosition(){
        var value = $('#pang_photo_position').val();
        $('#pang_photo_position_value').text(value);
        $('#pang_photo_preview img').css('object-position','center '+value+'%');
    }

    $('#pang_photo_position').on('input change', updateCropPosition);

    $('#pang_select_photo').on('click', function(e){
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select PANG profile photo',
            button: { text: 'Use this photo' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function(){
            var a = frame.state().get('selection').first().toJSON();
            var width = parseInt(a.width || 0, 10);
            var height = parseInt(a.height || 0, 10);
            var url = a.sizes && a.sizes.medium_large ? a.sizes.medium_large.url :
                      (a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url);

            $('#pang_photo_id').val(a.id);
            $('#pang_photo_preview').html('<img src="'+url+'" alt="">');
            $('#pang_remove_photo').show();
            updateCropPosition();

            var info = $('#pang_photo_info').removeClass('is-good is-warning is-error');

            if (width < 800 || height < 800) {
                info.addClass('is-error').text(width+' × '+height+' px — too small; choose at least 800 × 800 px.');
                $('#pang_photo_id').val('');
            } else if (height < width) {
                info.addClass('is-warning').text(width+' × '+height+' px — usable, but a portrait photo (about 4:5) is recommended.');
            } else if (height < 1000) {
                info.addClass('is-warning').text(width+' × '+height+' px — acceptable; 800 × 1000 px or larger is recommended.');
            } else {
                info.addClass('is-good').text(width+' × '+height+' px — suitable profile image.');
            }
        });

        frame.open();
    });

    $('#pang_remove_photo').on('click', function(e){
        e.preventDefault();
        $('#pang_photo_id').val('');
        $('#pang_photo_preview').html('<span class="dashicons dashicons-admin-users"></span><span>No photo selected</span>');
        $('#pang_photo_info').removeClass('is-good is-warning is-error').text('');
        $(this).hide();
    });

    $('#pang-person-form').on('submit', function(e){
        if (!$('#pang_photo_id').val()) {
            e.preventDefault();
            alert('Please select a valid profile photo before saving the Person.');
        }
    });
});
JS;
    wp_add_inline_script('jquery', $js);
});
