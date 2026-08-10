<?php
/**
 * Plugin Name: PANG News Display
 * Description: News archive and Home news cards for the PANG website. Uses standard WordPress posts in the News category.
 * Version: 1.0.15
 * Author: PANG
 */

if (!defined('ABSPATH')) exit;

final class PANG_News_Display {
    const VERSION = '1.0.15';

    public static function init() {
        add_shortcode('pang_news_archive', [__CLASS__, 'archive_shortcode']);
        add_shortcode('pang_latest_news', [__CLASS__, 'latest_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_assets() {
        wp_register_style(
            'pang-news-display',
            plugins_url('assets/pang-news.css', __FILE__),
            [],
            self::VERSION
        );
    }

    private static function enqueue_assets() {
        wp_enqueue_style('pang-news-display');
    }

    private static function news_category_id() {
        $term = get_category_by_slug('news');
        return $term ? (int) $term->term_id : 0;
    }

    private static function child_categories() {
        $parent = self::news_category_id();
        if (!$parent) return [];
        return get_categories([
            'parent' => $parent,
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    }

    private static function years() {
        global $wpdb;
        $news_id = self::news_category_id();
        if (!$news_id) return [];
        $sql = $wpdb->prepare(
            "SELECT DISTINCT YEAR(p.post_date) AS y
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             WHERE p.post_type='post' AND p.post_status='publish' AND tt.term_id=%d
             ORDER BY y DESC",
             $news_id
        );
        return array_map('intval', $wpdb->get_col($sql));
    }

    private static function display_category($post_id) {
        $terms = get_the_category($post_id);
        foreach ($terms as $term) {
            if ($term->slug !== 'news') return $term->name;
        }
        return 'News';
    }

    private static function card($post_id, $compact = false) {
        $date_day = get_the_date('d', $post_id);
        $date_mon = strtoupper(get_the_date('M', $post_id));
        $date_year = get_the_date('Y', $post_id);
        $category = self::display_category($post_id);
        $excerpt = get_the_excerpt($post_id);
        if (!$excerpt) {
            $excerpt = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 28);
        }

        ob_start(); ?>
        <article class="pang-news-card<?php echo $compact ? ' pang-news-card--compact' : ''; ?>">
            <?php if ($compact): ?>
                <div class="pang-news-card__accent" aria-hidden="true"></div>
                <div class="pang-news-card__body">
                    <div class="pang-news-card__meta">
                        <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('j M Y', $post_id)); ?></time>
                    </div>
                    <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
                    <p><?php echo esc_html(wp_trim_words($excerpt, 18)); ?></p>
                    <a class="pang-news-card__link" href="<?php echo esc_url(get_permalink($post_id)); ?>">Read more <span aria-hidden="true">→</span></a>
                </div>
            <?php else: ?>
                <div class="pang-news-card__top">
                    <div class="pang-news-date" aria-label="<?php echo esc_attr(get_the_date('', $post_id)); ?>">
                        <strong><?php echo esc_html($date_day); ?></strong>
                        <span><?php echo esc_html($date_mon); ?></span>
                        <small><?php echo esc_html($date_year); ?></small>
                    </div>
                    <span class="pang-news-card__category"><?php echo esc_html($category); ?></span>
                </div>
                <div class="pang-news-card__body">
                    <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
                    <p><?php echo esc_html($excerpt); ?></p>
                    <a class="pang-news-card__link" href="<?php echo esc_url(get_permalink($post_id)); ?>">Read more <span aria-hidden="true">→</span></a>
                </div>
            <?php endif; ?>
        </article>
        <?php return ob_get_clean();
    }

    public static function archive_shortcode($atts) {
        self::enqueue_assets();
        $atts = shortcode_atts(['posts_per_page' => 9], $atts, 'pang_news_archive');
        $per_page = max(1, min(24, (int) $atts['posts_per_page']));
        $news_cat = self::news_category_id();
        if (!$news_cat) return '<p class="pang-news-notice">News category not found.</p>';

        $selected_cat = isset($_GET['pang_cat']) ? sanitize_key(wp_unslash($_GET['pang_cat'])) : '';
        $selected_year = isset($_GET['pang_year']) ? absint($_GET['pang_year']) : 0;
        $paged = isset($_GET['pang_page']) ? max(1, absint($_GET['pang_page'])) : 1;

        $tax_query = [[
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => [$news_cat],
        ]];

        if ($selected_cat) {
            $term = get_term_by('slug', $selected_cat, 'category');
            if ($term && (int) $term->parent === $news_cat) {
                $tax_query[] = [
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => [(int) $term->term_id],
                ];
            }
        }

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'tax_query' => $tax_query,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        if ($selected_year) $args['year'] = $selected_year;
        $q = new WP_Query($args);

        $base_url = remove_query_arg(['pang_cat','pang_year','pang_page']);
        $cats = self::child_categories();
        $years = self::years();

        ob_start(); ?>
        <section class="pang-news-archive">
            <header class="pang-news-hero">
                <div class="pang-news-wrap">
                    <h1>News</h1>
                    <p>Updates, events, research activities and international collaborations from PANG.</p>
                </div>
            </header>

            <div class="pang-news-wrap pang-news-layout">
                <aside class="pang-news-filters" aria-label="News filters">
                    <div class="pang-news-filter-group">
                        <h2>Categories</h2>
                        <a class="<?php echo $selected_cat ? '' : 'is-active'; ?>" href="<?php echo esc_url(remove_query_arg(['pang_cat','pang_page'], $base_url)); ?>">All news</a>
                        <?php foreach ($cats as $cat):
                            $url = add_query_arg('pang_cat', $cat->slug, remove_query_arg('pang_page', $base_url)); ?>
                            <a class="<?php echo $selected_cat === $cat->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($cat->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($years): ?>
                    <div class="pang-news-filter-group">
                        <h2>Year</h2>
                        <a class="<?php echo $selected_year ? '' : 'is-active'; ?>" href="<?php echo esc_url(remove_query_arg(['pang_year','pang_page'], $base_url)); ?>">All years</a>
                        <?php foreach ($years as $year):
                            $url = add_query_arg('pang_year', $year, remove_query_arg('pang_page', $base_url)); ?>
                            <a class="<?php echo $selected_year === $year ? 'is-active' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($year); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </aside>

                <main class="pang-news-results">
                    <div class="pang-news-results__head">
                        <strong><?php echo esc_html(number_format_i18n($q->found_posts)); ?> news</strong>
                        <?php if ($selected_cat || $selected_year): ?>
                            <a href="<?php echo esc_url($base_url); ?>">Clear filters</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($q->have_posts()): ?>
                        <div class="pang-news-grid">
                            <?php while ($q->have_posts()): $q->the_post(); echo self::card(get_the_ID()); endwhile; ?>
                        </div>
                        <?php if ($q->max_num_pages > 1): ?>
                            <nav class="pang-news-pagination" aria-label="News pagination">
                                <?php for ($i=1; $i <= $q->max_num_pages; $i++):
                                    $url = add_query_arg('pang_page', $i);
                                    $cls = $i === $paged ? 'is-active' : ''; ?>
                                    <a class="<?php echo esc_attr($cls); ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($i); ?></a>
                                <?php endfor; ?>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="pang-news-notice">No news found with the selected filters.</p>
                    <?php endif; wp_reset_postdata(); ?>
                </main>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    public static function latest_shortcode($atts) {
        self::enqueue_assets();
        $atts = shortcode_atts([
            'limit' => 3,
            'title' => 'Latest News',
            'news_page_url' => '/news/',
            'show_title' => '0',
            'show_link' => '0',
        ], $atts, 'pang_latest_news');
        $limit = max(1, min(8, (int) $atts['limit']));
        $news_cat = self::news_category_id();
        if (!$news_cat) return '';

        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'cat' => $news_cat,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        if (!$q->have_posts()) return '';

        ob_start(); ?>
        <section class="pang-home-news">
            <div class="pang-news-wrap">
                <?php if ($atts['show_title'] !== '0'): ?>
                <div class="pang-home-news__head">
                    <h3><?php echo esc_html($atts['title']); ?></h3>
                    <span class="pang-home-news__separator" aria-hidden="true"></span>
                </div>
                <?php endif; ?>
                <div class="pang-home-news__grid">
                    <?php while ($q->have_posts()): $q->the_post(); echo self::card(get_the_ID(), true); endwhile; wp_reset_postdata(); ?>
                </div>
                <?php if ($atts['show_link'] !== '0'): ?>
                <div class="pang-home-news__footer">
                    <a href="<?php echo esc_url($atts['news_page_url']); ?>">All news <span aria-hidden="true">→</span></a>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php return ob_get_clean();
    }
}
PANG_News_Display::init();
