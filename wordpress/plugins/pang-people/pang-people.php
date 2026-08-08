<?php
/**
 * Plugin Name: PANG People
 * Description: Structured People profiles for PANG Next.
 * Version: 0.6.4
 * Author: PArthenope Navigation Group
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function pang_people_register_types() {
    register_post_type( 'pang_person', array(
        'labels' => array(
            'name' => 'People',
            'singular_name' => 'Person',
            'add_new_item' => 'Add Person',
            'edit_item' => 'Edit Person',
        ),
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
        'rewrite' => array( 'slug' => 'people' ),
        'has_archive' => false,
    ) );

    register_taxonomy( 'pang_person_category', array( 'pang_person' ), array(
        'labels' => array(
            'name' => 'People Categories',
            'singular_name' => 'People Category',
        ),
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite' => false,
    ) );
}
add_action( 'init', 'pang_people_register_types' );

function pang_people_migrate_associated_members_term() {
    $old = get_term_by( 'name', 'Collaborators', 'pang_person_category' );
    $new = get_term_by( 'name', 'Associated Members', 'pang_person_category' );

    if ( ! $new ) {
        $inserted = wp_insert_term( 'Associated Members', 'pang_person_category' );
        if ( ! is_wp_error( $inserted ) ) {
            $new = get_term( $inserted['term_id'], 'pang_person_category' );
        }
    }

    if ( $old && $new && ! is_wp_error( $new ) ) {
        $person_ids = get_objects_in_term( $old->term_id, 'pang_person_category' );

        if ( ! is_wp_error( $person_ids ) ) {
            foreach ( $person_ids as $person_id ) {
                wp_set_object_terms(
                    (int) $person_id,
                    array( (int) $new->term_id ),
                    'pang_person_category',
                    false
                );
            }
        }

        wp_delete_term( $old->term_id, 'pang_person_category' );
    }
}


function pang_people_migrate_students_term() {
    $new = get_term_by( 'name', 'Students', 'pang_person_category' );

    if ( ! $new ) {
        $inserted = wp_insert_term( 'Students', 'pang_person_category' );
        if ( ! is_wp_error( $inserted ) ) {
            $new = get_term( $inserted['term_id'], 'pang_person_category' );
        }
    }

    if ( ! $new || is_wp_error( $new ) ) return;

    foreach ( array( 'PhD Students', 'PhD & Visiting Students', 'Visiting Students', 'MSc Students', 'BSc Students' ) as $old_name ) {
        $old = get_term_by( 'name', $old_name, 'pang_person_category' );
        if ( ! $old ) continue;

        $person_ids = get_objects_in_term( $old->term_id, 'pang_person_category' );
        if ( ! is_wp_error( $person_ids ) ) {
            foreach ( $person_ids as $person_id ) {
                wp_set_object_terms(
                    (int) $person_id,
                    array( (int) $new->term_id ),
                    'pang_person_category',
                    false
                );
            }
        }
        wp_delete_term( $old->term_id, 'pang_person_category' );
    }
}

function pang_people_activate() {
    pang_people_register_types();

    foreach ( array( 'Faculty', 'Researchers', 'Associated Members', 'Students', 'Past Members' ) as $term ) {
        if ( ! term_exists( $term, 'pang_person_category' ) ) {
            wp_insert_term( $term, 'pang_person_category' );
        }
    }

    pang_people_migrate_associated_members_term();
    pang_people_migrate_students_term();
    update_option( 'pang_people_version', '0.6.4' );
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pang_people_activate' );

function pang_people_upgrade_if_needed() {
    $current = get_option( 'pang_people_version' );

    if ( '0.6.4' !== $current ) {
        pang_people_migrate_associated_members_term();
        pang_people_migrate_students_term();
        update_option( 'pang_people_version', '0.6.4' );
    }
}
add_action( 'init', 'pang_people_upgrade_if_needed', 30 );

register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
} );

function pang_people_add_meta_boxes() {
    add_meta_box(
        'pang_person_details',
        'PANG Profile Details',
        'pang_people_render_meta_box',
        'pang_person',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'pang_people_add_meta_boxes' );

function pang_people_render_meta_box( $post ) {
    wp_nonce_field( 'pang_people_save_profile', 'pang_people_nonce' );

    $fields = array(
        'academic_position'  => array( 'Academic Position', 'text' ),
        'affiliation'        => array( 'Affiliation', 'text' ),
        'photo_position'     => array( 'Photo vertical position (%)', 'number' ),
        'biography'          => array( 'Biography', 'textarea' ),
        'research_interests' => array( 'Research Interests (one per line)', 'textarea' ),
        'orcid'              => array( 'ORCID URL', 'url' ),
        'google_scholar'     => array( 'Google Scholar URL', 'url' ),
        'scopus'             => array( 'Scopus URL', 'url' ),
    );

    foreach ( $fields as $key => $config ) {
        $value = get_post_meta( $post->ID, '_pang_' . $key, true );
        echo '<p><label for="pang_' . esc_attr( $key ) . '"><strong>' . esc_html( $config[0] ) . '</strong></label></p>';

        if ( 'textarea' === $config[1] ) {
            $height = ( 'biography' === $key ) ? '220px' : '110px';
            echo '<textarea style="width:100%;min-height:' . esc_attr( $height ) . '" id="pang_' . esc_attr( $key ) . '" name="pang_' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
        } else {
            if ( 'photo_position' === $key ) {
                if ( '' === $value ) $value = '50';
                echo '<input style="width:100%" type="number" min="0" max="100" step="1" id="pang_' . esc_attr( $key ) . '" name="pang_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
                echo '<small>0 = top, 50 = center, 100 = bottom. Lower values show more of the upper part of the portrait.</small>';
            } else {
                echo '<input style="width:100%" type="' . esc_attr( $config[1] ) . '" id="pang_' . esc_attr( $key ) . '" name="pang_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
            }
        }
    }
}

function pang_people_save_meta( $post_id ) {
    if ( ! isset( $_POST['pang_people_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pang_people_nonce'] ) ), 'pang_people_save_profile' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    foreach ( array( 'academic_position','affiliation','photo_position','biography','research_interests','orcid','google_scholar','scopus' ) as $key ) {
        $name = 'pang_' . $key;
        if ( ! isset( $_POST[ $name ] ) ) continue;

        $raw = wp_unslash( $_POST[ $name ] );

        if ( 'photo_position' === $key ) {
            $value = (string) max( 0, min( 100, absint( $raw ) ) );
        } elseif ( in_array( $key, array( 'orcid','google_scholar','scopus' ), true ) ) {
            $value = esc_url_raw( $raw );
        } elseif ( in_array( $key, array( 'biography','research_interests' ), true ) ) {
            $value = sanitize_textarea_field( $raw );
        } else {
            $value = sanitize_text_field( $raw );
        }

        update_post_meta( $post_id, '_pang_' . $key, $value );
    }
}
add_action( 'save_post_pang_person', 'pang_people_save_meta' );

function pang_people_badge_class( $category ) {
    return 'pang-badge pang-badge--' . sanitize_html_class( sanitize_title( $category ) );
}

function pang_people_grid_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'category' => '' ), $atts, 'pang_people' );

    $args = array(
        'post_type'      => 'pang_person',
        'posts_per_page' => -1,
        'orderby'        => array(
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ),
    );

    if ( $atts['category'] ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'pang_person_category',
                'field'    => 'slug',
                'terms'    => sanitize_title( $atts['category'] ),
            ),
        );
    }

    $q = new WP_Query( $args );
    if ( ! $q->have_posts() ) return '';

    ob_start();
    echo '<div class="pang-people-grid">';

    while ( $q->have_posts() ) {
        $q->the_post();

        $id          = get_the_ID();
        $position    = get_post_meta( $id, '_pang_academic_position', true );
        $affiliation = get_post_meta( $id, '_pang_affiliation', true );
        $terms       = wp_get_post_terms( $id, 'pang_person_category', array( 'fields' => 'names' ) );
        $category    = $terms ? $terms[0] : '';

        echo '<article class="pang-person-card"><a class="pang-person-card__link" href="' . esc_url( get_permalink() ) . '">';

        if ( has_post_thumbnail() ) {
            $photo_position = get_post_meta( $id, '_pang_photo_position', true );
            $photo_position = is_numeric( $photo_position ) ? max( 0, min( 100, (int) $photo_position ) ) : 50;
            echo get_the_post_thumbnail( $id, 'medium_large', array(
                'class' => 'pang-person-card__image',
                'style' => 'object-position:center ' . esc_attr( $photo_position ) . '%;',
            ) );
        }

        echo '<div class="pang-person-card__body">';

        echo '<h3>' . esc_html( get_the_title() ) . '</h3>';

        if ( $position ) {
            echo '<p class="pang-person-card__position">' . esc_html( $position ) . '</p>';
        }

        if ( $affiliation ) {
            echo '<p class="pang-person-card__affiliation">' . esc_html( $affiliation ) . '</p>';
        }

        echo '</div></a></article>';
    }

    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'pang_people', 'pang_people_grid_shortcode' );

function pang_people_profile_content( $content ) {
    if ( ! is_singular( 'pang_person' ) || ! in_the_loop() || ! is_main_query() ) return $content;

    $id          = get_the_ID();
    $position    = get_post_meta( $id, '_pang_academic_position', true );
    $affiliation = get_post_meta( $id, '_pang_affiliation', true );
    $biography   = get_post_meta( $id, '_pang_biography', true );
    $interests   = get_post_meta( $id, '_pang_research_interests', true );
    $orcid       = get_post_meta( $id, '_pang_orcid', true );
    $scholar     = get_post_meta( $id, '_pang_google_scholar', true );
    $scopus      = get_post_meta( $id, '_pang_scopus', true );
    $terms       = wp_get_post_terms( $id, 'pang_person_category', array( 'fields' => 'names' ) );
    $category    = $terms ? $terms[0] : '';

    ob_start(); ?>
    <div class="pang-profile-head">
        <div class="pang-profile-photo">
            <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium_large' ); ?>
        </div>

        <div class="pang-profile-summary">
            <?php if ( $category ) : ?>
                <span class="<?php echo esc_attr( pang_people_badge_class( $category ) ); ?>">
                    <?php echo esc_html( $category ); ?>
                </span>
            <?php endif; ?>

            <?php if ( $position ) : ?>
                <p class="pang-profile-position"><?php echo esc_html( $position ); ?></p>
            <?php endif; ?>

            <?php if ( $affiliation ) : ?>
                <p class="pang-profile-affiliation"><?php echo esc_html( $affiliation ); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $biography ) : ?>
        <section class="pang-profile-section">
            <h2>Biography</h2>
            <p><?php echo nl2br( esc_html( $biography ) ); ?></p>
        </section>
    <?php endif; ?>

    <?php if ( $interests ) : ?>
        <section class="pang-profile-section">
            <h2>Research Interests</h2>
            <ul>
                <?php
                foreach ( preg_split( '/\R+/', $interests ) as $interest ) {
                    $interest = trim( $interest );
                    if ( $interest ) {
                        echo '<li>' . esc_html( $interest ) . '</li>';
                    }
                }
                ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ( $orcid || $scholar || $scopus ) : ?>
        <nav class="pang-profile-links" aria-label="External academic profiles">
            <?php if ( $orcid ) : ?>
                <a href="<?php echo esc_url( $orcid ); ?>" target="_blank" rel="noopener">ORCID</a>
            <?php endif; ?>

            <?php if ( $scholar ) : ?>
                <a href="<?php echo esc_url( $scholar ); ?>" target="_blank" rel="noopener">Google Scholar</a>
            <?php endif; ?>

            <?php if ( $scopus ) : ?>
                <a href="<?php echo esc_url( $scopus ); ?>" target="_blank" rel="noopener">Scopus</a>
            <?php endif; ?>
        </nav>
    <?php endif;

    return ob_get_clean();
}
add_filter( 'the_content', 'pang_people_profile_content', 20 );



/**
 * Locate an existing person by legacy nid first, then by exact title.
 */
function pang_people_find_person_for_import( $nid, $name ) {
    if ( $nid ) {
        $by_nid = get_posts( array(
            'post_type'      => 'pang_person',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_pang_legacy_nid',
            'meta_value'     => (string) $nid,
        ) );
        if ( $by_nid ) return (int) $by_nid[0];
    }

    $page = get_page_by_title( $name, OBJECT, 'pang_person' );
    return $page ? (int) $page->ID : 0;
}

/**
 * Import/update the bundled reviewed CSV. Existing profiles are updated in place;
 * unmatched rows create new People posts. Images and manual ordering are preserved.
 */
function pang_people_normalize_category( $category ) {
    $category = trim( (string) $category );
    $student_categories = array(
        'PhD Students',
        'PhD & Visiting Students',
        'Visiting Students',
        'Visiting',
        'MSc Students',
        'M.Sc. Students',
        'Master Students',
        'BSc Students',
        'Bachelor Students',
        'Student',
        'Students',
    );

    if ( in_array( $category, $student_categories, true ) ) {
        return 'Students';
    }

    return $category;
}

function pang_people_import_review_csv() {
    $path = '';
    if ( ! is_readable( $path ) ) {
        return new WP_Error( 'pang_people_csv_missing', 'Bundled CSV not found or not readable.' );
    }

    $handle = fopen( $path, 'r' );
    if ( ! $handle ) {
        return new WP_Error( 'pang_people_csv_open', 'Unable to open bundled CSV.' );
    }

    $headers = fgetcsv( $handle, 0, ';' );
    if ( ! $headers ) {
        fclose( $handle );
        return new WP_Error( 'pang_people_csv_headers', 'CSV header row is missing.' );
    }
    $headers[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $headers[0] );

    $created = 0;
    $updated = 0;
    $skipped = 0;

    while ( ( $values = fgetcsv( $handle, 0, ';' ) ) !== false ) {
        if ( count( $values ) !== count( $headers ) ) {
            $skipped++;
            continue;
        }
        $row  = array_combine( $headers, $values );
        $name = isset( $row['name'] ) ? trim( $row['name'] ) : '';
        if ( '' === $name ) {
            $skipped++;
            continue;
        }

        $nid     = isset( $row['nid'] ) ? trim( $row['nid'] ) : '';
        $post_id = pang_people_find_person_for_import( $nid, $name );
        $is_new  = ! $post_id;

        $postarr = array(
            'post_type'   => 'pang_person',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $name ),
        );
        if ( $post_id ) $postarr['ID'] = $post_id;

        $post_id = wp_insert_post( $postarr, true );
        if ( is_wp_error( $post_id ) ) {
            $skipped++;
            continue;
        }

        if ( $nid ) update_post_meta( $post_id, '_pang_legacy_nid', sanitize_text_field( $nid ) );

        $meta_map = array(
            'legacy_role'    => 'academic_position',
            'affiliation'     => 'affiliation',
            'biography'       => 'biography',
            'orcid'           => 'orcid',
            'google_scholar'  => 'google_scholar',
            'scopus'          => 'scopus',
        );
        foreach ( $meta_map as $csv_key => $meta_key ) {
            if ( ! array_key_exists( $csv_key, $row ) ) continue;
            $raw = trim( $row[ $csv_key ] );
            if ( in_array( $meta_key, array( 'orcid', 'google_scholar', 'scopus' ), true ) ) {
                $value = esc_url_raw( $raw );
            } elseif ( 'biography' === $meta_key ) {
                $value = sanitize_textarea_field( $raw );
            } else {
                $value = sanitize_text_field( $raw );
            }
            update_post_meta( $post_id, '_pang_' . $meta_key, $value );
        }

        $category = isset( $row['final_category'] ) ? pang_people_normalize_category( $row['final_category'] ) : '';
        if ( $category ) {
            if ( ! term_exists( $category, 'pang_person_category' ) ) {
                wp_insert_term( $category, 'pang_person_category' );
            }
            wp_set_object_terms( $post_id, array( $category ), 'pang_person_category', false );
        }

        if ( $is_new ) $created++; else $updated++;
    }
    fclose( $handle );

    return array( 'created' => $created, 'updated' => $updated, 'skipped' => $skipped );
}


/**
 * Return a normalized media key for matching a People profile to an image.
 */
function pang_people_media_match_key( $value ) {
    $value = (string) $value;
    $value = preg_replace( '/\.[A-Za-z0-9]+$/', '', $value );
    $value = preg_replace( '/-\d+x\d+$/', '', $value );
    return sanitize_title( $value );
}

/**
 * Associate unassigned People profiles with images already present in the
 * WordPress Media Library. Existing featured images are never overwritten.
 * Matching uses person slug/title against attachment slug/title/file basename.
 */
function pang_people_associate_photos() {
    $attachments = get_posts( array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );

    $media_index = array();
    foreach ( $attachments as $attachment ) {
        $candidates = array(
            $attachment->post_name,
            $attachment->post_title,
        );

        $file = get_attached_file( $attachment->ID );
        if ( $file ) {
            $candidates[] = pathinfo( $file, PATHINFO_FILENAME );
        }

        foreach ( $candidates as $candidate ) {
            $key = pang_people_media_match_key( $candidate );
            if ( $key && ! isset( $media_index[ $key ] ) ) {
                $media_index[ $key ] = (int) $attachment->ID;
            }
        }
    }

    $people = get_posts( array(
        'post_type'      => 'pang_person',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $associated = 0;
    $existing   = 0;
    $not_found  = array();

    foreach ( $people as $person ) {
        if ( has_post_thumbnail( $person->ID ) ) {
            $existing++;
            continue;
        }

        $keys = array_unique( array_filter( array(
            pang_people_media_match_key( $person->post_name ),
            pang_people_media_match_key( $person->post_title ),
        ) ) );

        $attachment_id = 0;
        foreach ( $keys as $key ) {
            if ( isset( $media_index[ $key ] ) ) {
                $attachment_id = (int) $media_index[ $key ];
                break;
            }
        }

        if ( $attachment_id ) {
            set_post_thumbnail( $person->ID, $attachment_id );
            $associated++;
        } else {
            $not_found[] = $person->post_title;
        }
    }

    return array(
        'associated' => $associated,
        'existing'   => $existing,
        'not_found'  => $not_found,
    );
}

function pang_people_import_menu() {
    add_submenu_page(
        'edit.php?post_type=pang_person',
        'Sync People CSV',
        'Sync CSV',
        'manage_options',
        'pang-people-import',
        'pang_people_import_page'
    );
}
add_action( 'admin_menu', 'pang_people_import_menu' );

function pang_people_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    echo '<div class="wrap"><h1>Sync People CSV</h1>';
    echo '<p>Updates existing People from <code>people-review_04.csv</code> and creates missing profiles. Featured images, research interests and manual ordering are preserved.</p>';

    if ( isset( $_POST['pang_people_run_import'] ) ) {
        check_admin_referer( 'pang_people_import_csv' );
        $result = pang_people_import_review_csv();
        if ( is_wp_error( $result ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>Sync complete: ' . intval( $result['updated'] ) . ' updated, ' . intval( $result['created'] ) . ' created, ' . intval( $result['skipped'] ) . ' skipped.</p></div>';
        }
    }

    if ( isset( $_POST['pang_people_associate_photos'] ) ) {
        check_admin_referer( 'pang_people_associate_photos' );
        $result = pang_people_associate_photos();
        echo '<div class="notice notice-success"><p>Photo association complete: ' . intval( $result['associated'] ) . ' associated, ' . intval( $result['existing'] ) . ' already assigned, ' . count( $result['not_found'] ) . ' not found.</p>';
        if ( $result['not_found'] ) {
            echo '<p><strong>Not found:</strong> ' . esc_html( implode( ', ', $result['not_found'] ) ) . '</p>';
        }
        echo '</div>';
    }

    echo '<form method="post" style="margin-bottom:24px">';
    wp_nonce_field( 'pang_people_import_csv' );
    submit_button( 'Sync bundled CSV', 'primary', 'pang_people_run_import', false );
    echo '</form>';

    echo '<hr><h2>Associate People Photos</h2>';
    echo '<p>Matches People without a featured image to images already in the Media Library using the person slug/name and the media filename/title. Existing featured images are preserved.</p>';
    echo '<form method="post">';
    wp_nonce_field( 'pang_people_associate_photos' );
    submit_button( 'Associate People Photos', 'secondary', 'pang_people_associate_photos', false );
    echo '</form></div>';
}

function pang_people_order_menu() {
    add_submenu_page(
        'edit.php?post_type=pang_person',
        'Order People',
        'Order People',
        'edit_pages',
        'pang-people-order',
        'pang_people_order_page'
    );
}
add_action( 'admin_menu', 'pang_people_order_menu' );

function pang_people_order_assets( $hook ) {
    if ( 'pang_person_page_pang-people-order' !== $hook ) return;

    wp_enqueue_script( 'jquery-ui-sortable' );

    wp_add_inline_script(
        'jquery-ui-sortable',
        "jQuery(function($){
            $('.pang-sortable').sortable({placeholder:'pang-sort-placeholder'});
            $('#pang-order-form').on('submit',function(){
                var data={};
                $('.pang-sortable').each(function(){
                    data[$(this).data('category')]=$(this).sortable('toArray',{attribute:'data-id'});
                });
                $('#pang-order-json').val(JSON.stringify(data));
            });
        });"
    );

    wp_add_inline_style(
        'wp-admin',
        '.pang-sortable{max-width:760px;margin:12px 0 30px}
        .pang-sort-item{background:#fff;border:1px solid #ccd0d4;padding:12px 16px;margin:6px 0;cursor:move;border-radius:5px}
        .pang-sort-item:before{content:"☰";margin-right:12px;color:#777}
        .pang-sort-placeholder{height:44px;border:2px dashed #8c8f94;margin:6px 0}
        .pang-order-cat{margin-top:28px}'
    );
}
add_action( 'admin_enqueue_scripts', 'pang_people_order_assets' );

function pang_people_order_page() {
    if ( ! current_user_can( 'edit_pages' ) ) return;

    $categories = array(
        'Faculty',
        'Researchers',
        'Associated Members',
        'Students',
        'Past Members',
    );

    if ( isset( $_POST['pang_save_order'] ) ) {
        check_admin_referer( 'pang_people_order' );

        $json = isset( $_POST['pang_order_json'] )
            ? json_decode( wp_unslash( $_POST['pang_order_json'] ), true )
            : array();

        if ( is_array( $json ) ) {
            foreach ( $json as $ids ) {
                if ( ! is_array( $ids ) ) continue;

                foreach ( $ids as $index => $id ) {
                    wp_update_post(
                        array(
                            'ID'         => (int) $id,
                            'menu_order' => (int) $index + 1,
                        )
                    );
                }
            }

            echo '<div class="notice notice-success is-dismissible"><p>People order saved.</p></div>';
        }
    }

    echo '<div class="wrap"><h1>Order People</h1><p>Drag members within each category, then click <strong>Save Order</strong>.</p><form method="post" id="pang-order-form">';

    wp_nonce_field( 'pang_people_order' );

    foreach ( $categories as $cat ) {
        echo '<h2 class="pang-order-cat">' . esc_html( $cat ) . '</h2>';

        $q = new WP_Query(
            array(
                'post_type'      => 'pang_person',
                'posts_per_page' => -1,
                'orderby'        => array(
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'pang_person_category',
                        'field'    => 'name',
                        'terms'    => $cat,
                    ),
                ),
            )
        );

        echo '<div class="pang-sortable" data-category="' . esc_attr( sanitize_title( $cat ) ) . '">';

        while ( $q->have_posts() ) {
            $q->the_post();
            echo '<div class="pang-sort-item" data-id="' . intval( get_the_ID() ) . '">' . esc_html( get_the_title() ) . '</div>';
        }

        echo '</div>';
        wp_reset_postdata();
    }

    echo '<input type="hidden" id="pang-order-json" name="pang_order_json" value="">';
    submit_button( 'Save Order', 'primary', 'pang_save_order' );
    echo '</form></div>';
}

function pang_people_styles() { ?>
<style>
.single-pang_person .entry-meta,.single-pang_person .ct-entry-meta,.single-pang_person .entry-meta-items{display:none!important}
.pang-people-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:34px 26px;margin:28px 0 56px}
.pang-person-card{border:0;border-radius:0;overflow:visible;background:transparent;box-shadow:none;text-align:center;min-width:0}
.pang-person-card__link{display:flex;flex-direction:column;align-items:center;color:inherit;text-decoration:none;height:100%}
.pang-person-card__image{display:block;width:180px;height:180px;aspect-ratio:1/1;object-fit:cover;border-radius:50%;margin:0 auto 14px;transition:transform .18s ease,box-shadow .18s ease;box-shadow:0 2px 10px rgba(11,45,72,.10)}
.pang-person-card__body{padding:0 6px}
.pang-person-card h3{margin:0 0 5px;font-size:1.08rem;line-height:1.25}
.pang-person-card p{margin:2px 0;line-height:1.35}
.pang-person-card__position{font-size:.92rem;font-weight:600}
.pang-person-card__affiliation{font-size:.84rem;color:#687786}
.pang-person-card__link:hover .pang-person-card__image{transform:translateY(-2px);box-shadow:0 7px 18px rgba(11,45,72,.15)}
.pang-person-card__link:hover h3{text-decoration:underline;text-underline-offset:3px}
.pang-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.75rem;font-weight:700}
.pang-badge--faculty{background:#e4eef9;color:#174f86}
.pang-badge--researchers{background:#e5f5eb;color:#247043}
.pang-badge--associated-members{background:#f0e7f7;color:#68428a}
.pang-badge--students{background:#fff4cc;color:#775d00}
.pang-badge--past-members{background:#eceff1;color:#5f6870}
.pang-profile-head{display:grid;grid-template-columns:minmax(180px,300px) 1fr;gap:40px;align-items:center;margin:8px 0 32px}
.pang-profile-photo img{width:100%;border-radius:14px}.pang-profile-position{font-size:1.15rem;font-weight:600;margin:16px 0 6px}
.pang-profile-affiliation{margin:0;color:#44566a}.pang-profile-section{margin:34px 0}
.pang-profile-links{display:flex;gap:12px;flex-wrap:wrap;margin:36px 0}
.pang-profile-links a{padding:9px 14px;border:1px solid #006DAA;border-radius:8px;text-decoration:none}
@media(max-width:1000px){.pang-people-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:760px){.pang-people-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:30px 18px}.pang-person-card__image{width:150px;height:150px}}
@media(max-width:480px){.pang-people-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:26px 12px}.pang-person-card__image{width:125px;height:125px}.pang-person-card h3{font-size:1rem}.pang-person-card__position{font-size:.86rem}.pang-person-card__affiliation{font-size:.78rem}.pang-profile-head{grid-template-columns:1fr}}
</style>
<?php }
add_action( 'wp_head', 'pang_people_styles' );
