<?php
/**
 * Plugin Name: PANG Research
 * Description: Structured PANG research projects, Research page grids and Home selected projects.
 * Version: 0.3.3
 * Author: PArthenope Navigation Group
 */
if (!defined('ABSPATH')) exit;

define('PANG_RESEARCH_VERSION', '0.3.3');

add_action('init', function () {
    register_post_type('pang_project', array(
        'labels' => array(
            'name' => 'Research Projects',
            'singular_name' => 'Research Project',
            'add_new_item' => 'Add Research Project',
            'edit_item' => 'Edit Research Project',
            'menu_name' => 'Research Projects',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title'),
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
    ));
});

function pang_research_meta_fields() {
    return array(
        '_pang_acronym' => 'Acronym',
        '_pang_description' => 'Description',
        '_pang_programme' => 'Programme',
        '_pang_start_year' => 'Start year',
        '_pang_end_year' => 'End year',
        '_pang_status' => 'Status',
        '_pang_selected_project' => 'Selected Project',
        '_pang_responsible_person' => 'Responsible person',
        '_pang_responsible_role' => 'Responsible role',
        '_pang_project_url' => 'Project URL',
        '_pang_source_url' => 'Source URL',
        '_pang_research_areas' => 'Research areas',
    );
}

add_action('add_meta_boxes', function () {
    add_meta_box('pang_research_project_meta', 'Project Details', 'pang_research_project_meta_box', 'pang_project', 'normal', 'high');
});

function pang_research_project_meta_box($post) {
    wp_nonce_field('pang_research_save_meta', 'pang_research_nonce');
    foreach (pang_research_meta_fields() as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        echo '<p><label for="'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br>';
        if ($key === '_pang_description') {
            echo '<textarea style="width:100%;min-height:120px" id="'.esc_attr($key).'" name="'.esc_attr($key).'">'.esc_textarea($value).'</textarea>';
        } elseif ($key === '_pang_status') {
            echo '<select id="'.esc_attr($key).'" name="'.esc_attr($key).'">';
            foreach (array('ongoing'=>'Ongoing','completed'=>'Completed') as $v=>$t) {
                echo '<option value="'.esc_attr($v).'" '.selected($value,$v,false).'>'.esc_html($t).'</option>';
            }
            echo '</select>';
        } elseif ($key === '_pang_selected_project') {
            echo '<select id="'.esc_attr($key).'" name="'.esc_attr($key).'">';
            foreach (array('no'=>'No','yes'=>'Yes') as $v=>$t) {
                echo '<option value="'.esc_attr($v).'" '.selected($value ?: 'no',$v,false).'>'.esc_html($t).'</option>';
            }
            echo '</select>';
        } else {
            echo '<input style="width:100%" type="text" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        }
        echo '</p>';
    }
    echo '<p><em>Use semicolons for multiple Research Areas, e.g. Positioning; Navigation.</em></p>';
}

add_action('save_post_pang_project', function ($post_id) {
    if (!isset($_POST['pang_research_nonce']) || !wp_verify_nonce($_POST['pang_research_nonce'], 'pang_research_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    foreach (pang_research_meta_fields() as $key => $label) {
        if (!isset($_POST[$key])) continue;
        $val = $key === '_pang_description' ? sanitize_textarea_field(wp_unslash($_POST[$key])) : sanitize_text_field(wp_unslash($_POST[$key]));
        update_post_meta($post_id, $key, $val);
    }
});

function pang_research_split_areas($raw) {
    return array_values(array_filter(array_map('trim', preg_split('/\s*;\s*/', (string)$raw, -1, PREG_SPLIT_NO_EMPTY))));
}

function pang_research_excerpt($text, $words) {
    return wp_trim_words(wp_strip_all_tags((string)$text), $words, '…');
}

function pang_research_short_acronym($post_id) {
    $acronym = trim((string)get_post_meta($post_id, '_pang_acronym', true));
    if ($acronym !== '' && preg_match('/^(.+?)\s*[-–—:]\s+/u', $acronym, $m)) $acronym = trim($m[1]);
    if ($acronym === '') {
        $title = trim((string)get_the_title($post_id));
        if (preg_match('/^(.+?)\s*[-–—:]\s+/u', $title, $m)) $acronym = trim($m[1]);
    }
    return $acronym;
}

function pang_research_project_key($post_id) {
    /*
     * The post title is the most reliable identity source across all importer
     * revisions.  Older imports sometimes stored the complete project title
     * inside _pang_acronym, which previously defeated deduplication.
     */
    $title = trim((string)get_the_title($post_id));
    if ($title !== '' && preg_match('/^(.+?)\s*[-–—:]\s+/u', $title, $m)) {
        return sanitize_title(trim($m[1]));
    }

    $acronym = trim((string)get_post_meta($post_id, '_pang_acronym', true));
    if ($acronym !== '' && preg_match('/^(.+?)\s*[-–—:]\s+/u', $acronym, $m)) {
        return sanitize_title(trim($m[1]));
    }

    /* If an older acronym field contains a suspiciously long phrase, prefer
       the short WordPress title when available. */
    if ($title !== '' && mb_strlen($title) <= 50) {
        return sanitize_title($title);
    }

    if ($acronym !== '') return sanitize_title($acronym);
    return sanitize_title($title);
}

function pang_research_project_score($post_id) {
    $score = 0;
    $a = trim((string)get_post_meta($post_id, '_pang_acronym', true));
    $title = trim((string)get_the_title($post_id));

    if ($a !== '') {
        $score += 5;
        if (!preg_match('/[-–—:]/u', $a) && mb_strlen($a) < 40) $score += 8;
    }
    if ($title !== '' && !preg_match('/[-–—:]/u', $title) && mb_strlen($title) < 40) $score += 4;

    foreach (array('_pang_description','_pang_programme','_pang_status','_pang_responsible_person','_pang_research_areas','_pang_project_url') as $k) {
        if (trim((string)get_post_meta($post_id, $k, true)) !== '') $score += 1;
    }
    return $score;
}

function pang_research_unique_posts($posts) {
    $best = array();
    foreach ($posts as $post) {
        $key = pang_research_project_key($post->ID);
        if ($key === '') $key = 'id-'.$post->ID;
        if (!isset($best[$key]) || pang_research_project_score($post->ID) > pang_research_project_score($best[$key]->ID)) {
            $best[$key] = $post;
        }
    }
    return array_values($best);
}

function pang_research_clean_full_title($acronym, $title) {
    $title = trim((string)$title);
    if ($title === '') return '';
    if ($acronym === '') return $title;
    return trim((string)preg_replace('/^\s*'.preg_quote($acronym,'/').'\s*[-–—:]\s*/iu', '', $title));
}

function pang_research_project_card($post_id, $status) {
    $acronym = pang_research_short_acronym($post_id);
    $title = trim((string)get_the_title($post_id));
    $full_title = pang_research_clean_full_title($acronym, $title);
    $description = trim((string)get_post_meta($post_id, '_pang_description', true));
    $programme = trim((string)get_post_meta($post_id, '_pang_programme', true));
    $start = trim((string)get_post_meta($post_id, '_pang_start_year', true));
    $end = trim((string)get_post_meta($post_id, '_pang_end_year', true));
    $responsible = trim((string)get_post_meta($post_id, '_pang_responsible_person', true));
    $role = trim((string)get_post_meta($post_id, '_pang_responsible_role', true));
    $url = trim((string)get_post_meta($post_id, '_pang_project_url', true));
    $areas = pang_research_split_areas(get_post_meta($post_id, '_pang_research_areas', true));
    $label = $acronym !== '' ? $acronym : $title;

    ob_start(); ?>
    <article class="pang-project-card pang-project-card--<?php echo esc_attr($status); ?>">
      <h3 class="pang-project-card__title"><?php echo esc_html($label); ?></h3>
      <?php if ($full_title !== '' && strcasecmp($full_title, $label) !== 0) : ?>
        <p class="pang-project-card__subtitle"><?php echo esc_html($full_title); ?></p>
      <?php endif; ?>
      <?php if ($description !== '') : ?>
        <p class="pang-project-card__description"><?php echo esc_html(pang_research_excerpt($description, $status === 'ongoing' ? 42 : 28)); ?></p>
      <?php endif; ?>
      <?php if ($programme !== '' || $start !== '' || $end !== '' || $responsible !== '') : ?>
        <dl class="pang-project-card__meta">
          <?php if ($programme !== '') : ?><div><dt>Programme</dt><dd><?php echo esc_html($programme); ?></dd></div><?php endif; ?>
          <?php if ($start !== '' || $end !== '') : ?><div><dt>Period</dt><dd><?php echo esc_html($start); ?><?php if ($end !== '') echo '–'.esc_html($end); elseif ($status === 'ongoing') echo '–ongoing'; ?></dd></div><?php endif; ?>
          <?php if ($responsible !== '') : ?><div><dt>Responsible</dt><dd><?php echo esc_html($responsible); ?><?php if ($role !== '') echo ' · '.esc_html($role); ?></dd></div><?php endif; ?>
        </dl>
      <?php endif; ?>
      <?php if ($areas) : ?><div class="pang-project-card__areas"><?php foreach ($areas as $area) : ?><span><?php echo esc_html($area); ?></span><?php endforeach; ?></div><?php endif; ?>
      <?php if ($url !== '') : ?><p class="pang-project-card__link"><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">Project website →</a></p><?php endif; ?>
    </article>
    <?php return ob_get_clean();
}


/**
 * PANG 0.3.3: Completed Projects ordering.
 * Year descending; same year alphabetically by acronym/title.
 */
function pang_research_completed_sort_posts($posts) {
    usort($posts, function ($a, $b) {
        $meta_keys_end   = array('_pang_end_year', 'end_year', 'project_end_year');
        $meta_keys_start = array('_pang_start_year', 'start_year', 'project_start_year');
        $meta_keys_acr   = array('_pang_acronym', 'acronym', 'project_acronym');

        $get_first = function($post_id, $keys) {
            foreach ($keys as $key) {
                $v = trim((string) get_post_meta($post_id, $key, true));
                if ($v !== '') return $v;
            }
            return '';
        };

        $ae = $get_first($a->ID, $meta_keys_end);
        $be = $get_first($b->ID, $meta_keys_end);
        $as = $get_first($a->ID, $meta_keys_start);
        $bs = $get_first($b->ID, $meta_keys_start);

        $ay = is_numeric($ae) ? (int)$ae : (is_numeric($as) ? (int)$as : 0);
        $by = is_numeric($be) ? (int)$be : (is_numeric($bs) ? (int)$bs : 0);

        if ($ay !== $by) return $by <=> $ay;

        $al = $get_first($a->ID, $meta_keys_acr);
        $bl = $get_first($b->ID, $meta_keys_acr);
        if ($al === '') $al = get_the_title($a->ID);
        if ($bl === '') $bl = get_the_title($b->ID);

        return strcasecmp($al, $bl);
    });
    return $posts;
}

add_shortcode('pang_projects', function ($atts) {
    $atts = shortcode_atts(array('status'=>'ongoing'), $atts, 'pang_projects');
    $status = strtolower((string)$atts['status']) === 'completed' ? 'completed' : 'ongoing';
    $q = new WP_Query(array(
        'post_type'=>'pang_project', 'post_status'=>'publish', 'posts_per_page'=>-1,
        'orderby'=>array('menu_order'=>'ASC','title'=>'ASC'),
        'meta_query'=>array(array('key'=>'_pang_status','value'=>$status)),
    ));
    if (!$q->have_posts()) return '';
    $html = '<div class="pang-projects pang-projects--'.esc_attr($status).'">';
    $pang_posts = pang_research_unique_posts($q->posts);
    if ($status === 'completed') {
        $pang_posts = pang_research_completed_sort_posts($pang_posts);
    }
    foreach ($pang_posts as $post) $html .= pang_research_project_card($post->ID, $status);
    return $html.'</div>';
});

function pang_research_selected_project_card($post_id) {
    $acronym = pang_research_short_acronym($post_id);
    $title = trim((string)get_the_title($post_id));
    $full_title = pang_research_clean_full_title($acronym, $title);
    $description = trim((string)get_post_meta($post_id, '_pang_description', true));
    $areas = pang_research_split_areas(get_post_meta($post_id, '_pang_research_areas', true));
    $label = $acronym !== '' ? $acronym : $title;
    ob_start(); ?>
    <article class="pang-selected-project-card">
      <h3><?php echo esc_html($label); ?></h3>
      <?php if ($full_title !== '' && strcasecmp($full_title,$label) !== 0) : ?><p class="pang-selected-project-card__subtitle"><?php echo esc_html($full_title); ?></p><?php endif; ?>
      <?php if ($description !== '') : ?><p class="pang-selected-project-card__description"><?php echo esc_html(pang_research_excerpt($description,20)); ?></p><?php endif; ?>
      <?php if ($areas) : ?><div class="pang-project-card__areas pang-selected-project-card__areas"><?php foreach ($areas as $area) : ?><span><?php echo esc_html($area); ?></span><?php endforeach; ?></div><?php endif; ?>
    </article>
    <?php return ob_get_clean();
}

add_shortcode('pang_selected_projects', function () {
    $q = new WP_Query(array(
        'post_type'=>'pang_project', 'post_status'=>'publish', 'posts_per_page'=>-1,
        'orderby'=>array('menu_order'=>'ASC','title'=>'ASC'),
        'meta_query'=>array(array('key'=>'_pang_selected_project','value'=>'yes')),
    ));
    if (!$q->have_posts()) return '';
    $html = '<div class="pang-selected-projects">';
    foreach (pang_research_unique_posts($q->posts) as $post) $html .= pang_research_selected_project_card($post->ID);
    return $html.'</div>';
});

add_action('wp_enqueue_scripts', function () {
    wp_register_style('pang-research', false, array(), PANG_RESEARCH_VERSION);
    wp_enqueue_style('pang-research');
    $css = <<<'CSS'
.pang-projects{display:grid;gap:22px;margin:20px 0 44px;align-items:start}
.pang-projects--ongoing{grid-template-columns:repeat(2,minmax(0,1fr))}
.pang-projects--completed{grid-template-columns:repeat(3,minmax(0,1fr))}
.pang-project-card{display:block;padding:22px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;box-shadow:0 4px 16px rgba(16,42,67,.045)}
.pang-project-card--ongoing{padding:24px}
.pang-project-card__title{margin:0 0 8px;font-size:1.18rem;line-height:1.2;color:#0c4f9a}
.pang-project-card--ongoing .pang-project-card__title{font-size:1.35rem}
.pang-project-card__subtitle{margin:0 0 10px;font-size:.92rem;font-style:italic;line-height:1.4;color:#41566f}
.pang-project-card__description{margin:0 0 14px;color:#334e68;line-height:1.55}
.pang-project-card__meta{display:block;margin:12px 0 10px;font-size:.82rem;color:#62758a}
.pang-project-card__meta div{display:grid;grid-template-columns:82px minmax(0,1fr);column-gap:12px;align-items:start;margin:5px 0}
.pang-project-card__meta dt{margin:0;font-weight:700;color:#334e68;white-space:nowrap;word-break:normal;overflow-wrap:normal;hyphens:none}
.pang-project-card__meta dd{margin:0;min-width:0;white-space:normal;word-break:normal;overflow-wrap:anywhere}
.pang-project-card__areas{display:flex;flex-wrap:wrap;gap:7px;margin-top:12px}
.pang-project-card__areas span{display:inline-block;padding:4px 9px;border-radius:999px;background:#edf5ff;color:#145dba;font-size:.75rem;font-weight:600}
.pang-project-card__areas span:nth-child(2){background:#e8f8ef;color:#26734d}
.pang-project-card__areas span:nth-child(3){background:#f3edff;color:#6741a5}
.pang-project-card__link{margin:12px 0 0}.pang-project-card__link a{font-weight:600;text-decoration:none}.pang-project-card__link a:hover{text-decoration:underline}
.pang-selected-projects{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:18px 0 24px;align-items:stretch;grid-auto-rows:1fr}
.pang-selected-project-card{position:relative;display:flex;flex-direction:column;min-width:0;height:100%;padding:20px 20px 18px;border:1px solid #e3e8ef;border-radius:8px;background:#fff;box-shadow:0 2px 12px rgba(17,42,76,.05);overflow:hidden;transition:transform .18s ease,box-shadow .18s ease}
.pang-selected-project-card::before{content:"";display:block;position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0c4f9f,#2b83d6)}
.pang-selected-project-card:hover{transform:translateY(-2px);box-shadow:0 7px 20px rgba(17,42,76,.09)}
.pang-selected-project-card h3{margin:14px 0 8px;color:#0c4f9f;font-size:18px;line-height:1.35;font-weight:700}
.pang-selected-project-card__subtitle{margin:0 0 10px;color:#41566f;font-size:14px;font-style:italic;line-height:1.45}
.pang-selected-project-card__description{margin:0 0 18px;color:#5c6878;font-size:14px;line-height:1.55}
.pang-selected-project-card__areas{margin-top:auto;padding-top:4px}
@media(max-width:980px){.pang-projects--completed{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:820px){.pang-selected-projects{grid-template-columns:1fr;gap:22px}}
@media(max-width:720px){.pang-projects--ongoing,.pang-projects--completed{grid-template-columns:1fr}.pang-project-card,.pang-project-card--ongoing{padding:18px}}
@media(max-width:520px){.pang-project-card__meta div{grid-template-columns:1fr;row-gap:1px}.pang-project-card__meta dd{margin-bottom:5px}}
CSS;
    wp_add_inline_style('pang-research', $css);
});
