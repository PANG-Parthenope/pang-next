<?php
/**
 * Plugin Name: PANG Data Export
 * Description: Export PANG People, Projects, News and Publications to CSV. Includes an Export All ZIP. Available to Editors and Administrators.
 * Version: 0.1.2
 * Author: PArthenope Navigation Group
 */

if (!defined('ABSPATH')) exit;

define('PANG_DATA_EXPORT_VERSION', '0.1.2');
define('PANG_DATA_EXPORT_CAP', 'edit_others_posts');

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_menu_page(
        'PANG Data Export',
        'PANG Data Export',
        PANG_DATA_EXPORT_CAP,
        'pang-data-export',
        'pang_data_export_admin_page',
        'dashicons-download',
        28
    );
});


/**
 * Editor UX: expose News as a dedicated menu with All News / Add News,
 * and hide the generic Posts and Comments menus. Administrators are unchanged.
 */
add_action('admin_menu', function () {
    if (!current_user_can('edit_others_posts') || current_user_can('manage_options')) {
        return;
    }

    add_menu_page(
        'News',
        'News',
        'edit_others_posts',
        'edit.php?category_name=news',
        '',
        'dashicons-megaphone',
        5
    );

    add_submenu_page(
        'edit.php?category_name=news',
        'All News',
        'All News',
        'edit_others_posts',
        'edit.php?category_name=news'
    );

    add_submenu_page(
        'edit.php?category_name=news',
        'Add News',
        'Add News',
        'edit_posts',
        'post-new.php'
    );

    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
}, 999);

/**
 * When an Editor uses Add News, preselect the News category when available.
 */
add_action('admin_head-post-new.php', function () {
    if (!current_user_can('edit_others_posts') || current_user_can('manage_options')) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'post') return;

    $news = get_term_by('slug', 'news', 'category');
    if (!$news) $news = get_term_by('name', 'News', 'category');
    if (!$news || is_wp_error($news)) return;

    $term_id = (int) $news->term_id;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var checkbox = document.getElementById('in-category-<?php echo $term_id; ?>');
        if (checkbox && !checkbox.checked) checkbox.checked = true;
    });
    </script>
    <?php
});

function pang_data_export_admin_page() {
    if (!current_user_can(PANG_DATA_EXPORT_CAP)) {
        wp_die('You do not have permission to export PANG data.');
    }

    $types = pang_data_export_resolve_post_types();
    ?>
    <div class="wrap">
        <h1>PANG Data Export</h1>
        <p>
            Export the current WordPress content as UTF-8 CSV files.
            This tool is intended for periodic repository snapshots and structured backups.
        </p>

        <table class="widefat striped" style="max-width:900px;margin:24px 0">
            <thead>
                <tr><th>Dataset</th><th>Detected WordPress source</th><th>Export</th></tr>
            </thead>
            <tbody>
                <?php foreach (array(
                    'people' => 'People',
                    'projects' => 'Projects',
                    'news' => 'News',
                    'publications' => 'Publications'
                ) as $key => $label) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($label); ?></strong></td>
                        <td>
                            <?php
                            if ($key === 'publications' && pang_data_export_publications_table_exists()) {
                                echo '<code>'.esc_html($GLOBALS['wpdb']->prefix . 'pang_publications').'</code>';
                            } elseif (!empty($types[$key])) {
                                echo '<code>'.esc_html($types[$key]).'</code>';
                            } else {
                                echo '<span style="color:#b32d2e">Not detected</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (($key === 'publications' && pang_data_export_publications_table_exists()) || !empty($types[$key])) : ?>
                                <a class="button button-secondary"
                                   href="<?php echo esc_url(pang_data_export_action_url($key)); ?>">
                                   Export <?php echo esc_html($label); ?> CSV
                                </a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p>
            <a class="button button-primary button-hero"
               href="<?php echo esc_url(pang_data_export_action_url('all')); ?>">
               Export All
            </a>
        </p>

        <p class="description" style="max-width:900px">
            <strong>Export All</strong> creates a ZIP containing all datasets that can be detected.
            Each CSV includes WordPress IDs, slugs, dates and all custom metadata currently stored
            for the records, so new plugin fields are preserved automatically.
        </p>
    </div>
    <?php
}

function pang_data_export_action_url($dataset) {
    return wp_nonce_url(
        admin_url('admin-post.php?action=pang_data_export&dataset='.rawurlencode($dataset)),
        'pang_data_export_'.$dataset
    );
}

/* -------------------------------------------------------------------------
 * Post-type discovery
 * ---------------------------------------------------------------------- */
function pang_data_export_resolve_post_types() {
    $registered = get_post_types(array(), 'objects');

    $find = function($preferred, $needles) use ($registered) {
        foreach ($preferred as $pt) {
            if (post_type_exists($pt)) return $pt;
        }

        foreach ($registered as $name => $obj) {
            $hay = strtolower($name.' '.$obj->label.' '.$obj->labels->singular_name);
            foreach ($needles as $needle) {
                if (strpos($hay, strtolower($needle)) !== false) return $name;
            }
        }
        return '';
    };

    return array(
        'people' => $find(
            array('pang_person', 'pang_people', 'person', 'people'),
            array('pang people', 'people', 'person')
        ),
        'projects' => $find(
            array('pang_project', 'pang_projects', 'research_project'),
            array('research project', 'pang project')
        ),
        'news' => 'post',
        'publications' => $find(
            array('pang_publication', 'pang_publications', 'publication', 'publications'),
            array('pang publication', 'publication')
        ),
    );
}

/* -------------------------------------------------------------------------
 * Export endpoint
 * ---------------------------------------------------------------------- */
add_action('admin_post_pang_data_export', function () {
    if (!current_user_can(PANG_DATA_EXPORT_CAP)) {
        wp_die('You do not have permission to export PANG data.');
    }

    $dataset = isset($_GET['dataset']) ? sanitize_key(wp_unslash($_GET['dataset'])) : '';
    check_admin_referer('pang_data_export_'.$dataset);

    if ($dataset === 'all') {
        pang_data_export_all();
        exit;
    }

    $allowed = array('people', 'projects', 'news', 'publications');
    if (!in_array($dataset, $allowed, true)) {
        wp_die('Unknown dataset.');
    }

    $csv = pang_data_export_build_csv($dataset);
    if ($csv === null) {
        wp_die('The requested PANG content type could not be detected.');
    }

    $filename = 'pang-'.$dataset.'-'.wp_date('Y-m-d_His').'.csv';
    pang_data_export_send_csv($filename, $csv);
    exit;
});

function pang_data_export_publications_table_exists() {
    global $wpdb;
    $table = $wpdb->prefix . 'pang_publications';
    return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
}

function pang_data_export_build_publications_csv() {
    global $wpdb;
    $table = $wpdb->prefix . 'pang_publications';
    if (!pang_data_export_publications_table_exists()) return null;

    $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);
    if (!$columns) return '';

    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY year DESC, title ASC, id ASC", ARRAY_A);
    $headers = array_merge($columns, array('exported_at'));

    $fp = fopen('php://temp', 'w+');
    if (!$fp) return '';
    fwrite($fp, "\\xEF\\xBB\\xBF");
    fputcsv($fp, $headers, ';');
    $exported_at = wp_date('c');

    foreach ($rows as $row) {
        $out = array();
        foreach ($columns as $column) $out[] = isset($row[$column]) ? (string)$row[$column] : '';
        $out[] = $exported_at;
        fputcsv($fp, $out, ';');
    }
    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);
    return $csv;
}

/* -------------------------------------------------------------------------
 * CSV generation
 * ---------------------------------------------------------------------- */
function pang_data_export_build_csv($dataset) {
    if ($dataset === 'publications') {
        return pang_data_export_build_publications_csv();
    }

    $types = pang_data_export_resolve_post_types();
    $post_type = isset($types[$dataset]) ? $types[$dataset] : '';
    if (!$post_type || !post_type_exists($post_type)) return null;

    $args = array(
        'post_type' => $post_type,
        'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC', 'title' => 'ASC'),
        'order' => 'ASC',
        'suppress_filters' => false,
    );

    /* Standard posts: export only News when a News category exists. */
    if ($dataset === 'news' && $post_type === 'post') {
        $news_term = get_term_by('slug', 'news', 'category');
        if (!$news_term) $news_term = get_term_by('name', 'News', 'category');
        if ($news_term && !is_wp_error($news_term)) {
            $args['cat'] = (int)$news_term->term_id;
        }
    }

    $posts = get_posts($args);

    /* Collect every custom meta key actually used in this dataset. */
    $meta_keys = array();
    foreach ($posts as $post) {
        foreach (array_keys(get_post_meta($post->ID)) as $key) {
            if (pang_data_export_include_meta_key($key)) {
                $meta_keys[$key] = true;
            }
        }
    }
    $meta_keys = array_keys($meta_keys);
    natcasesort($meta_keys);
    $meta_keys = array_values($meta_keys);

    $base_headers = array(
        'wp_id',
        'post_type',
        'status',
        'slug',
        'title',
        'content',
        'excerpt',
        'published_at',
        'modified_at',
        'menu_order',
        'featured_image_url',
        'categories',
        'tags',
        'exported_at',
    );

    $headers = array_merge($base_headers, $meta_keys);

    $fp = fopen('php://temp', 'w+');
    if (!$fp) return '';

    /* Excel-friendly UTF-8 BOM. */
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers, ';');

    $exported_at = wp_date('c');

    foreach ($posts as $post) {
        $row = array(
            $post->ID,
            $post->post_type,
            $post->post_status,
            $post->post_name,
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            $post->post_date,
            $post->post_modified,
            $post->menu_order,
            get_the_post_thumbnail_url($post->ID, 'full') ?: '',
            pang_data_export_terms($post->ID, 'category'),
            pang_data_export_terms($post->ID, 'post_tag'),
            $exported_at,
        );

        foreach ($meta_keys as $key) {
            $row[] = pang_data_export_meta_value($post->ID, $key);
        }

        fputcsv($fp, $row, ';');
    }

    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);
    return $csv;
}

function pang_data_export_include_meta_key($key) {
    /* Exclude WordPress/editor caches and lock metadata, but keep PANG/plugin data. */
    $excluded_exact = array(
        '_edit_lock',
        '_edit_last',
        '_wp_old_slug',
        '_wp_desired_post_slug',
    );
    if (in_array($key, $excluded_exact, true)) return false;

    $excluded_prefixes = array(
        '_oembed_',
        '_transient_',
    );
    foreach ($excluded_prefixes as $prefix) {
        if (strpos($key, $prefix) === 0) return false;
    }

    return true;
}

function pang_data_export_meta_value($post_id, $key) {
    $values = get_post_meta($post_id, $key, false);
    if (!$values) return '';

    $normalised = array();
    foreach ($values as $value) {
        if (is_array($value) || is_object($value)) {
            $normalised[] = wp_json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $normalised[] = (string)$value;
        }
    }

    return implode(' | ', $normalised);
}

function pang_data_export_terms($post_id, $taxonomy) {
    if (!taxonomy_exists($taxonomy)) return '';
    $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
    if (is_wp_error($terms) || !$terms) return '';
    return implode(' | ', $terms);
}

/* -------------------------------------------------------------------------
 * ZIP export
 * ---------------------------------------------------------------------- */
function pang_data_export_all() {
    $datasets = array('people', 'projects', 'news', 'publications');
    $stamp = wp_date('Y-m-d_His');

    if (!class_exists('ZipArchive')) {
        wp_die(
            'ZIP export is not available because the PHP ZipArchive extension is missing. '.
            'The four individual CSV exports remain available.'
        );
    }

    $tmp = wp_tempnam('pang-data-export-'.$stamp.'.zip');
    if (!$tmp) wp_die('Unable to create a temporary export file.');

    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        @unlink($tmp);
        wp_die('Unable to create ZIP export.');
    }

    $count = 0;
    foreach ($datasets as $dataset) {
        $csv = pang_data_export_build_csv($dataset);
        if ($csv === null) continue;

        $zip->addFromString($dataset.'.csv', $csv);
        $count++;
    }

    $manifest = array(
        'generated_at' => wp_date('c'),
        'site_url' => home_url('/'),
        'wordpress_version' => get_bloginfo('version'),
        'plugin_version' => PANG_DATA_EXPORT_VERSION,
        'datasets' => $count,
    );
    $zip->addFromString(
        'manifest.json',
        wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    $zip->close();

    if ($count === 0) {
        @unlink($tmp);
        wp_die('No PANG datasets could be detected.');
    }

    nocache_headers();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="pang-data-export-'.$stamp.'.zip"');
    header('Content-Length: '.filesize($tmp));
    readfile($tmp);
    @unlink($tmp);
}

/* -------------------------------------------------------------------------
 * Download helper
 * ---------------------------------------------------------------------- */
function pang_data_export_send_csv($filename, $csv) {
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Length: '.strlen($csv));
    echo $csv;
}
