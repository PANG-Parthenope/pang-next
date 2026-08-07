<?php
/**
 * Plugin Name: PANG People
 * Description: Structured People profiles for PANG Next.
 * Version: 0.4.1
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

function pang_people_activate() {
    pang_people_register_types();

    foreach ( array( 'Faculty', 'Researchers', 'Associated Members', 'PhD Students', 'Past Members' ) as $term ) {
        if ( ! term_exists( $term, 'pang_person_category' ) ) {
            wp_insert_term( $term, 'pang_person_category' );
        }
    }

    pang_people_migrate_associated_members_term();
    update_option( 'pang_people_version', '0.4.1' );
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pang_people_activate' );

function pang_people_upgrade_if_needed() {
    $current = get_option( 'pang_people_version' );

    if ( '0.4.1' !== $current ) {
        pang_people_migrate_associated_members_term();
        update_option( 'pang_people_version', '0.4.1' );
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
            echo '<input style="width:100%" type="' . esc_attr( $config[1] ) . '" id="pang_' . esc_attr( $key ) . '" name="pang_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
        }
    }
}

function pang_people_save_meta( $post_id ) {
    if ( ! isset( $_POST['pang_people_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pang_people_nonce'] ) ), 'pang_people_save_profile' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    foreach ( array( 'academic_position','affiliation','biography','research_interests','orcid','google_scholar','scopus' ) as $key ) {
        $name = 'pang_' . $key;
        if ( ! isset( $_POST[ $name ] ) ) continue;

        $raw = wp_unslash( $_POST[ $name ] );

        if ( in_array( $key, array( 'orcid','google_scholar','scopus' ), true ) ) {
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
            echo get_the_post_thumbnail( $id, 'medium_large', array( 'class' => 'pang-person-card__image' ) );
        }

        echo '<div class="pang-person-card__body">';

        if ( $category ) {
            echo '<span class="' . esc_attr( pang_people_badge_class( $category ) ) . '">' . esc_html( $category ) . '</span>';
        }

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
        'PhD Students',
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
.pang-people-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:28px;margin:28px 0 56px}
.pang-person-card{border:1px solid #e3e8ec;border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 8px 24px rgba(11,45,72,.06)}
.pang-person-card__link{display:block;color:inherit;text-decoration:none;height:100%}
.pang-person-card__image{display:block;width:100%;aspect-ratio:4/5;object-fit:cover}
.pang-person-card__body{padding:20px}.pang-person-card h3{margin:10px 0 6px;font-size:1.25rem}
.pang-person-card p{margin:4px 0}.pang-person-card__affiliation{color:#5f6d78}
.pang-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.75rem;font-weight:700}
.pang-badge--faculty{background:#e4eef9;color:#174f86}
.pang-badge--researchers{background:#e5f5eb;color:#247043}
.pang-badge--associated-members{background:#f0e7f7;color:#68428a}
.pang-badge--phd-students{background:#fff4cc;color:#775d00}
.pang-badge--past-members{background:#eceff1;color:#5f6870}
.pang-profile-head{display:grid;grid-template-columns:minmax(180px,300px) 1fr;gap:40px;align-items:center;margin:8px 0 32px}
.pang-profile-photo img{width:100%;border-radius:14px}.pang-profile-position{font-size:1.15rem;font-weight:600;margin:16px 0 6px}
.pang-profile-affiliation{margin:0;color:#44566a}.pang-profile-section{margin:34px 0}
.pang-profile-links{display:flex;gap:12px;flex-wrap:wrap;margin:36px 0}
.pang-profile-links a{padding:9px 14px;border:1px solid #006DAA;border-radius:8px;text-decoration:none}
@media(max-width:900px){.pang-people-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.pang-people-grid,.pang-profile-head{grid-template-columns:1fr}}
</style>
<?php }
add_action( 'wp_head', 'pang_people_styles' );
