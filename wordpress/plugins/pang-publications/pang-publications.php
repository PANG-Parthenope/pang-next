<?php
/**
 * Plugin Name: PANG Publications
 * Description: Publications archive for PANG: Drupal historical records (2004-2024), automatic Scopus synchronization (2025-current), filters and citation export.
 * Version: 1.1.1
 * Author: PANG - Parthenope Navigation Group
 */

defined('ABSPATH') || exit;

final class PANG_Publications {
    private const VERSION = '1.1.1';
    private const DB_VERSION = '1';
    private const OPTION_DB_VERSION = 'pang_publications_db_version';
    private const OPTION_API_KEY = 'pang_scopus_api_key';
    private const OPTION_INSTTOKEN = 'pang_scopus_insttoken';
    private const OPTION_LAST_SYNC = 'pang_publications_last_sync';
    private const OPTION_LAST_LOG = 'pang_publications_last_log';
    private const CRON_HOOK = 'pang_publications_weekly_sync';
    private const API_URL = 'https://api.elsevier.com/content/search/scopus';
    private const ABSTRACT_API_URL = 'https://api.elsevier.com/content/abstract/eid/';
    private const CROSSREF_API_URL = 'https://api.crossref.org/works/';

    public static function authors(): array {
        return [
            'Salvatore Gaglione'  => '44861224900',
            'Silvio Del Pizzo'    => '57414770300',
            'Antonio Angrisano'   => '42260974600',
            'Ciro Gioia'          => '44861227800',
            'Salvatore Troisi'    => '6603874255',
            'Vincenzo Piscopo'    => '6507257720',
            'Antonio Scamardella' => '36170904600',
            'Silvia Pennino'      => '56097577100',
            'Giampaolo Ferraioli' => '21933757700',
        ];
    }

    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_post_pang_publications_save_settings', [__CLASS__, 'save_settings']);
        add_action('admin_post_pang_publications_import_history', [__CLASS__, 'import_history_action']);
        add_action('admin_post_pang_publications_sync', [__CLASS__, 'sync_action']);
        add_action(self::CRON_HOOK, [__CLASS__, 'cron_sync']);
        add_shortcode('pang_publications', [__CLASS__, 'shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_filter('cron_schedules', [__CLASS__, 'cron_schedules']);
    }

    public static function activate(): void {
        self::install_table();
        self::import_history(false);
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'pang_weekly', self::CRON_HOOK);
        }
    }

    public static function deactivate(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    public static function cron_schedules(array $schedules): array {
        if (!isset($schedules['pang_weekly'])) {
            $schedules['pang_weekly'] = [
                'interval' => 7 * DAY_IN_SECONDS,
                'display' => 'Once Weekly (PANG)',
            ];
        }
        return $schedules;
    }

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'pang_publications';
    }

    private static function install_table(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $table = self::table();
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            unique_key varchar(191) NOT NULL,
            publication_id varchar(191) NOT NULL DEFAULT '',
            year smallint(4) unsigned NOT NULL DEFAULT 0,
            pub_date varchar(20) NOT NULL DEFAULT '',
            title text NOT NULL,
            authors longtext NOT NULL,
            pang_authors text NOT NULL,
            document_type varchar(120) NOT NULL DEFAULT '',
            source_title text NOT NULL,
            publisher text NOT NULL,
            volume varchar(80) NOT NULL DEFAULT '',
            issue varchar(80) NOT NULL DEFAULT '',
            pages varchar(120) NOT NULL DEFAULT '',
            doi varchar(255) NOT NULL DEFAULT '',
            scopus_eid varchar(120) NOT NULL DEFAULT '',
            issn varchar(80) NOT NULL DEFAULT '',
            isbn varchar(120) NOT NULL DEFAULT '',
            url text NOT NULL,
            abstract longtext NOT NULL,
            language varchar(30) NOT NULL DEFAULT '',
            record_source varchar(30) NOT NULL DEFAULT '',
            legacy_nid bigint(20) unsigned NOT NULL DEFAULT 0,
            raw_json longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_key (unique_key),
            KEY year (year),
            KEY source (record_source),
            KEY eid (scopus_eid),
            KEY doi (doi(100))
        ) {$charset};";
        dbDelta($sql);
        update_option(self::OPTION_DB_VERSION, self::DB_VERSION, false);
    }

    public static function admin_menu(): void {
        add_menu_page(
            'PANG Publications',
            'PANG Publications',
            'manage_options',
            'pang-publications',
            [__CLASS__, 'render_admin'],
            'dashicons-welcome-learn-more',
            26
        );
    }

    private static function api_key(): string {
        if (defined('PANG_SCOPUS_API_KEY') && is_string(PANG_SCOPUS_API_KEY)) {
            return trim(PANG_SCOPUS_API_KEY);
        }
        return trim((string) get_option(self::OPTION_API_KEY, ''));
    }

    private static function insttoken(): string {
        if (defined('PANG_SCOPUS_INSTTOKEN') && is_string(PANG_SCOPUS_INSTTOKEN)) {
            return trim(PANG_SCOPUS_INSTTOKEN);
        }
        return trim((string) get_option(self::OPTION_INSTTOKEN, ''));
    }

    public static function save_settings(): void {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('pang_publications_save_settings');
        if (!defined('PANG_SCOPUS_API_KEY')) {
            $key = isset($_POST['api_key']) ? trim(sanitize_text_field(wp_unslash($_POST['api_key']))) : '';
            if ($key !== '') update_option(self::OPTION_API_KEY, $key, false);
        }
        if (!defined('PANG_SCOPUS_INSTTOKEN')) {
            $token = isset($_POST['insttoken']) ? trim(sanitize_text_field(wp_unslash($_POST['insttoken']))) : '';
            if ($token !== '') update_option(self::OPTION_INSTTOKEN, $token, false);
        }
        wp_safe_redirect(add_query_arg(['page'=>'pang-publications','notice'=>'settings'], admin_url('admin.php')));
        exit;
    }

    public static function import_history_action(): void {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('pang_publications_import_history');
        $result = self::import_history(true);
        set_transient('pang_publications_admin_result_' . get_current_user_id(), $result, 120);
        wp_safe_redirect(add_query_arg(['page'=>'pang-publications','notice'=>'history'], admin_url('admin.php')));
        exit;
    }

    public static function sync_action(): void {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('pang_publications_sync');
        $result = self::sync_scopus();
        set_transient('pang_publications_admin_result_' . get_current_user_id(), $result, 300);
        wp_safe_redirect(add_query_arg(['page'=>'pang-publications','notice'=>'sync'], admin_url('admin.php')));
        exit;
    }

    public static function cron_sync(): void {
        self::sync_scopus();
    }

    private static function import_history(bool $replace_history): array {
        global $wpdb;
        self::install_table();
        $file = plugin_dir_path(__FILE__) . 'assets/data/publications-master-2004-2024.csv';
        if (!is_readable($file)) return ['ok'=>false,'message'=>'Historical CSV not found.'];
        if ($replace_history) {
            $wpdb->query($wpdb->prepare('DELETE FROM ' . self::table() . ' WHERE record_source = %s', 'drupal'));
        }
        $fh = fopen($file, 'rb');
        if (!$fh) return ['ok'=>false,'message'=>'Cannot open historical CSV.'];
        $headers = fgetcsv($fh);
        if (!$headers) { fclose($fh); return ['ok'=>false,'message'=>'Invalid historical CSV.']; }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$headers[0]);
        $count=0; $updated=0; $skipped=0;
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) !== count($headers)) { $skipped++; continue; }
            $r = array_combine($headers, $row);
            if (!$r || strtolower((string)($r['include_in_master'] ?? 'yes')) !== 'yes') { $skipped++; continue; }
            $data = [
                'publication_id'=>(string)($r['publication_id']??''),
                'year'=>(int)($r['year']??0),
                'pub_date'=>(string)($r['date']??''),
                'title'=>(string)($r['title']??''),
                'authors'=>(string)($r['authors']??''),
                'pang_authors'=>(string)($r['pang_faculty_authors']??''),
                'document_type'=>(string)($r['document_type']??''),
                'source_title'=>(string)($r['source_title']??''),
                'publisher'=>(string)($r['publisher']??''),
                'volume'=>(string)($r['volume']??''),
                'issue'=>(string)($r['issue']??''),
                'pages'=>(string)($r['pages']??''),
                'doi'=>(string)($r['doi_normalized'] ?: ($r['doi']??'')),
                'scopus_eid'=>self::eid_from_url((string)($r['url']??'')),
                'issn'=>(string)($r['issn']??''),
                'isbn'=>(string)($r['isbn']??''),
                'url'=>(string)($r['url']??''),
                'abstract'=>(string)($r['abstract']??''),
                'language'=>(string)($r['language']??''),
                'record_source'=>'drupal',
                'legacy_nid'=>(int)($r['legacy_nid']??0),
                'raw_json'=>'',
            ];
            $res = self::upsert($data);
            if ($res === 'insert') $count++; elseif ($res === 'update') $updated++; else $skipped++;
        }
        fclose($fh);
        return ['ok'=>true,'message'=>'Historical archive imported.','inserted'=>$count,'updated'=>$updated,'skipped'=>$skipped];
    }

    private static function eid_from_url(string $url): string {
        if (preg_match('/[?&]eid=([^&]+)/', $url, $m)) return rawurldecode($m[1]);
        return '';
    }

    private static function normalize_doi(string $doi): string {
        $doi = trim(strtolower($doi));
        $doi = preg_replace('~^https?://(?:dx\.)?doi\.org/~i', '', $doi);
        $doi = preg_replace('/^doi:\s*/i', '', $doi);
        return trim((string)$doi);
    }

    private static function unique_key(array $data): string {
        $doi = self::normalize_doi((string)($data['doi']??''));
        if ($doi !== '') return 'doi:' . $doi;
        $eid = trim((string)($data['scopus_eid']??''));
        if ($eid !== '') return 'eid:' . strtolower($eid);
        $title = remove_accents(wp_strip_all_tags((string)($data['title']??'')));
        $title = strtolower(preg_replace('/[^a-z0-9]+/i', ' ', $title));
        return 'title:' . md5(trim($title) . '|' . (int)($data['year']??0));
    }

    private static function upsert(array $data): string {
        global $wpdb;
        $table = self::table();
        $key = self::unique_key($data);
        $now = current_time('mysql');
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE unique_key=%s LIMIT 1", $key), ARRAY_A);
        $values = [
            'unique_key'=>$key,
            'publication_id'=>sanitize_text_field((string)($data['publication_id']??'')),
            'year'=>(int)($data['year']??0),
            'pub_date'=>sanitize_text_field((string)($data['pub_date']??'')),
            'title'=>sanitize_text_field((string)($data['title']??'')),
            'authors'=>sanitize_textarea_field((string)($data['authors']??'')),
            'pang_authors'=>sanitize_textarea_field((string)($data['pang_authors']??'')),
            'document_type'=>sanitize_text_field((string)($data['document_type']??'')),
            'source_title'=>sanitize_text_field((string)($data['source_title']??'')),
            'publisher'=>sanitize_text_field((string)($data['publisher']??'')),
            'volume'=>sanitize_text_field((string)($data['volume']??'')),
            'issue'=>sanitize_text_field((string)($data['issue']??'')),
            'pages'=>sanitize_text_field((string)($data['pages']??'')),
            'doi'=>self::normalize_doi((string)($data['doi']??'')),
            'scopus_eid'=>sanitize_text_field((string)($data['scopus_eid']??'')),
            'issn'=>sanitize_text_field((string)($data['issn']??'')),
            'isbn'=>sanitize_text_field((string)($data['isbn']??'')),
            'url'=>esc_url_raw((string)($data['url']??'')),
            'abstract'=>sanitize_textarea_field((string)($data['abstract']??'')),
            'language'=>sanitize_text_field((string)($data['language']??'')),
            'record_source'=>sanitize_key((string)($data['record_source']??'')),
            'legacy_nid'=>(int)($data['legacy_nid']??0),
            'raw_json'=>(string)($data['raw_json']??''),
            'updated_at'=>$now,
        ];
        if ($existing) {
            // When the same Scopus record is found through multiple PANG authors, merge PANG author names.
            $merged = self::merge_names((string)$existing['pang_authors'], (string)$values['pang_authors']);
            $values['pang_authors'] = $merged;
            // Never replace a richer author list with a poorer one.
            $existing_author_count=self::author_count((string)($existing['authors']??''));
            $incoming_author_count=self::author_count((string)($values['authors']??''));
            if ($existing_author_count > $incoming_author_count) {
                $values['authors']=(string)$existing['authors'];
            }
            // Prefer richer incoming metadata, but preserve non-empty existing values if incoming empty.
            foreach ($values as $k=>$v) {
                if ($v === '' && isset($existing[$k]) && $existing[$k] !== '') $values[$k] = $existing[$k];
            }
            $wpdb->update($table, $values, ['id'=>(int)$existing['id']]);
            return 'update';
        }
        $values['created_at']=$now;
        $wpdb->insert($table, $values);
        return $wpdb->insert_id ? 'insert' : 'skip';
    }

    private static function author_count(string $authors): int {
        $authors=trim($authors);
        if ($authors==='') return 0;
        return count(array_filter(array_map('trim', preg_split('/\s*;\s*/', $authors) ?: [])));
    }

    private static function merge_names(string $a, string $b): string {
        $all=[];
        foreach ([$a,$b] as $set) {
            foreach (preg_split('/\s*;\s*/', trim($set)) ?: [] as $name) {
                $name=trim($name); if ($name!=='') $all[$name]=$name;
            }
        }
        return implode('; ', array_values($all));
    }

    private static function scopus_headers(): array {
        $headers=['X-ELS-APIKey'=>self::api_key(),'Accept'=>'application/json'];
        if (self::insttoken() !== '') $headers['X-ELS-Insttoken']=self::insttoken();
        return $headers;
    }

    public static function sync_scopus(): array {
        if (self::api_key() === '') return ['ok'=>false,'message'=>'Scopus API Key is not configured.'];
        self::install_table();
        $log=[]; $all_ok=true; $inserted=0; $updated=0; $unique_seen=[];
        foreach (self::authors() as $name=>$author_id) {
            $query='AU-ID(' . $author_id . ') AND PUBYEAR > 2024';
            $start=0; $author_total=0; $author_processed=0; $status=0; $error=''; $full_author_records=0; $single_author_records=0; $enrichment_errors=0; $enrichment_error_example=''; $crossref_enriched=0; $crossref_errors=0; $crossref_error_example='';
            do {
                $url=add_query_arg(['query'=>$query,'count'=>25,'start'=>$start,'sort'=>'-coverDate','view'=>'STANDARD'], self::API_URL);
                $response=wp_remote_get($url,['timeout'=>30,'redirection'=>3,'headers'=>self::scopus_headers()]);
                if (is_wp_error($response)) { $all_ok=false; $error=$response->get_error_message(); break; }
                $status=(int)wp_remote_retrieve_response_code($response);
                $json=json_decode((string)wp_remote_retrieve_body($response),true);
                if ($status<200 || $status>=300 || !is_array($json)) {
                    $all_ok=false; $error=self::api_message($json); break;
                }
                $sr=$json['search-results']??[];
                $author_total=(int)($sr['opensearch:totalResults']??0);
                $entries=$sr['entry']??[];
                foreach ($entries as $entry) {
                    if (!is_array($entry) || isset($entry['error'])) continue;
                    $data=self::map_scopus_entry($entry,$name);
                    if (!empty($data['_enrichment_error'])) { $enrichment_errors++; if ($enrichment_error_example==='') $enrichment_error_example=(string)$data['_enrichment_error']; }
                    if (!empty($data['_crossref_used'])) $crossref_enriched++;
                    if (!empty($data['_crossref_error'])) { $crossref_errors++; if ($crossref_error_example==='') $crossref_error_example=(string)$data['_crossref_error']; }
                    if ((int)$data['year'] < 2025) continue;
                    if (self::author_count((string)$data['authors']) > 1) $full_author_records++; else $single_author_records++;
                    $key=self::unique_key($data);
                    if (isset($unique_seen[$name][$key])) continue;
                    $unique_seen[$name][$key]=true;
                    $r=self::upsert($data);
                    if ($r==='insert') $inserted++; elseif ($r==='update') $updated++;
                    $author_processed++;
                }
                $page_count=count($entries);
                $start += $page_count;
                if ($page_count===0) break;
            } while ($start < $author_total && $start < 500);
            $log[]=['name'=>$name,'author_id'=>$author_id,'http'=>$status,'total'=>$author_total,'processed'=>$author_processed,'full_author_records'=>$full_author_records,'single_author_records'=>$single_author_records,'enrichment_errors'=>$enrichment_errors,'enrichment_error_example'=>$enrichment_error_example,'crossref_enriched'=>$crossref_enriched,'crossref_errors'=>$crossref_errors,'crossref_error_example'=>$crossref_error_example,'error'=>$error];
        }
        $result=['ok'=>$all_ok,'message'=>$all_ok?'Scopus synchronization completed.':'Synchronization completed with errors.','inserted'=>$inserted,'updated'=>$updated,'authors'=>$log,'time'=>current_time('mysql')];
        update_option(self::OPTION_LAST_SYNC, current_time('mysql'), false);
        update_option(self::OPTION_LAST_LOG, $result, false);
        return $result;
    }

    private static function api_message($json): string {
        if (!is_array($json)) return 'Invalid response from Scopus.';
        foreach (['service-error','error-response'] as $root) {
            if (isset($json[$root]['status']['statusText'])) return (string)$json[$root]['status']['statusText'];
            if (isset($json[$root]['message'])) return is_array($json[$root]['message']) ? wp_json_encode($json[$root]['message']) : (string)$json[$root]['message'];
        }
        return 'HTTP error returned by Scopus.';
    }

    private static function map_scopus_entry(array $e, string $pang_name): array {
        $date=(string)($e['prism:coverDate']??'');
        $year=preg_match('/^(\d{4})/',$date,$m)?(int)$m[1]:0;
        $eid=(string)($e['eid']??'');
        $doi=trim((string)($e['prism:doi']??''));
        $authors=[];

        // COMPLETE Search view normally contains the ordered full author list.
        if (isset($e['author']) && is_array($e['author'])) {
            $author_list=$e['author'];
            if (isset($author_list['authid']) || isset($author_list['authname']) || isset($author_list['surname']) || isset($author_list['ce:surname'])) {
                $author_list=[$author_list];
            }
            foreach ($author_list as $a) {
                if (!is_array($a)) continue;
                $given=trim((string)($a['given-name']??$a['ce:given-name']??''));
                $surname=trim((string)($a['surname']??$a['ce:surname']??''));
                $name=trim($given . ' ' . $surname);
                if ($surname!=='' && $given==='') $name=$surname;
                if ($name==='') $name=self::display_name_from_scopus_authname((string)($a['authname']??$a['ce:indexed-name']??$a['indexed-name']??''));
                if ($name!=='') $authors[$name]=$name;
            }
            $authors=array_values($authors);
        }

        // Secondary source: Scopus Abstract Retrieval META_ABS.
        $enrichment_diag=['http'=>0,'error'=>''];
        if (count($authors) < 2 && $eid !== '') {
            $abstract_authors=self::scopus_full_authors($eid,$enrichment_diag);
            if (count($abstract_authors) > count($authors)) $authors=$abstract_authors;
        }

        // Independent fallback: Crossref metadata by DOI.
        $crossref_diag=['http'=>0,'error'=>'','used'=>false];
        if (count($authors) < 2 && $doi !== '') {
            $crossref_authors=self::crossref_full_authors($doi,$crossref_diag);
            if (count($crossref_authors) > count($authors)) {
                $authors=$crossref_authors;
                $crossref_diag['used']=true;
            }
        }

        // Last-resort fallback: first author only.
        if (!$authors && !empty($e['dc:creator'])) {
            $fallback=self::display_name_from_scopus_authname((string)$e['dc:creator']);
            if ($fallback!=='') $authors=[$fallback];
        }
        $url='';
        if ($doi!=='') $url='https://doi.org/' . rawurlencode($doi);
        elseif ($eid!=='') $url='https://www.scopus.com/record/display.uri?eid=' . rawurlencode($eid) . '&origin=resultslist';
        $pages='';
        if (!empty($e['prism:pageRange'])) $pages=(string)$e['prism:pageRange'];
        elseif (!empty($e['prism:startingPage'])) $pages=(string)$e['prism:startingPage'];
        return [
            'publication_id'=>$eid!==''?'scopus-' . $eid:'scopus-' . md5((string)($e['dc:title']??'') . '|' . $year),
            'year'=>$year,
            'pub_date'=>$date,
            'title'=>(string)($e['dc:title']??''),
            'authors'=>implode('; ',$authors),
            'pang_authors'=>$pang_name,
            'document_type'=>(string)($e['subtypeDescription']??$e['subtype']??''),
            'source_title'=>(string)($e['prism:publicationName']??''),
            'publisher'=>'',
            'volume'=>(string)($e['prism:volume']??''),
            'issue'=>(string)($e['prism:issueIdentifier']??''),
            'pages'=>$pages,
            'doi'=>$doi,
            'scopus_eid'=>$eid,
            'issn'=>(string)($e['prism:issn']??''),
            'isbn'=>(string)($e['prism:isbn']??''),
            'url'=>$url,
            'abstract'=>'',
            'language'=>'',
            'record_source'=>'scopus',
            'legacy_nid'=>0,
            'raw_json'=>wp_json_encode($e,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            '_enrichment_http'=>(int)($enrichment_diag['http']??0),
            '_enrichment_error'=>(string)($enrichment_diag['error']??''),
            '_crossref_http'=>(int)($crossref_diag['http']??0),
            '_crossref_used'=>!empty($crossref_diag['used']) ? 1 : 0,
            '_crossref_error'=>(string)($crossref_diag['error']??''),
        ];
    }

    /**
     * Return the complete ordered author list for a Scopus record.
     * The META_ABS Abstract Retrieval view includes the full authors array.
     * Results are cached for 30 days to keep API usage very low.
     */
    private static function scopus_full_authors(string $eid, array &$diag=[]): array {
        $eid=trim($eid);
        if ($eid==='') { $diag=['http'=>0,'error'=>'Missing EID']; return []; }

        $cache_key='pang_pub_abs_auth_' . md5($eid);
        $cached=get_transient($cache_key);
        if (is_array($cached) && $cached) { $diag=['http'=>200,'error'=>'']; return $cached; }

        $url=add_query_arg(
            ['view'=>'META_ABS'],
            self::ABSTRACT_API_URL . rawurlencode($eid)
        );
        $response=wp_remote_get($url,[
            'timeout'=>30,
            'redirection'=>3,
            'headers'=>self::scopus_headers(),
        ]);
        if (is_wp_error($response)) { $diag=['http'=>0,'error'=>$response->get_error_message()]; return []; }

        $status=(int)wp_remote_retrieve_response_code($response);
        if ($status<200 || $status>=300) {
            $body=(string)wp_remote_retrieve_body($response);
            $j=json_decode($body,true);
            $diag=['http'=>$status,'error'=>'Abstract Retrieval HTTP ' . $status . ': ' . self::api_message($j)];
            return [];
        }

        $json=json_decode((string)wp_remote_retrieve_body($response),true);
        if (!is_array($json)) { $diag=['http'=>$status,'error'=>'Abstract Retrieval returned invalid JSON']; return []; }

        $root=$json['abstracts-retrieval-response']??[];
        $list=$root['authors']['author']??[];
        if (!is_array($list) || !$list) { $diag=['http'=>$status,'error'=>'Abstract Retrieval returned no author list']; return []; }

        // A single author may occasionally be represented as one associative array.
        if (isset($list['@auid']) || isset($list['ce:surname']) || isset($list['surname'])) {
            $list=[$list];
        }

        $authors=[];
        foreach ($list as $a) {
            if (!is_array($a)) continue;

            $preferred=is_array($a['preferred-name']??null) ? $a['preferred-name'] : [];

            $given=trim((string)(
                $a['ce:given-name']
                ?? $a['given-name']
                ?? $preferred['ce:given-name']
                ?? $preferred['given-name']
                ?? ''
            ));
            $surname=trim((string)(
                $a['ce:surname']
                ?? $a['surname']
                ?? $preferred['ce:surname']
                ?? $preferred['surname']
                ?? ''
            ));

            $name=trim($given . ' ' . $surname);

            if ($name==='') {
                $indexed=(string)(
                    $a['ce:indexed-name']
                    ?? $a['indexed-name']
                    ?? $preferred['ce:indexed-name']
                    ?? ''
                );
                $name=self::display_name_from_scopus_authname($indexed);
            }

            if ($name!=='' && !isset($authors[$name])) {
                $authors[$name]=$name;
            }
        }

        $authors=array_values($authors);
        if ($authors) set_transient($cache_key,$authors,30*DAY_IN_SECONDS);
        $diag=['http'=>$status,'error'=>$authors?'':'Abstract Retrieval author list could not be parsed'];
        return $authors;
    }

    /**
     * Retrieve the complete ordered author list from Crossref using a DOI.
     * Crossref enriches metadata only; Scopus still determines which papers
     * belong to the configured PANG members.
     */
    private static function crossref_full_authors(string $doi, array &$diag=[]): array {
        $doi=trim($doi);
        if ($doi==='') { $diag=['http'=>0,'error'=>'Missing DOI','used'=>false]; return []; }

        $cache_key='pang_pub_crossref_auth_' . md5(strtolower($doi));
        $cached=get_transient($cache_key);
        if (is_array($cached) && $cached) {
            $diag=['http'=>200,'error'=>'','used'=>false];
            return $cached;
        }

        $url=self::CROSSREF_API_URL . rawurlencode($doi);
        $admin_email=sanitize_email((string)get_option('admin_email',''));
        if ($admin_email!=='') $url=add_query_arg('mailto',$admin_email,$url);

        $response=wp_remote_get($url,[
            'timeout'=>20,
            'redirection'=>3,
            'headers'=>[
                'Accept'=>'application/json',
                'User-Agent'=>'PANG-Publications/' . self::VERSION . ' (' . home_url('/') . ($admin_email!=='' ? '; mailto:' . $admin_email : '') . ')',
            ],
        ]);

        if (is_wp_error($response)) {
            $diag=['http'=>0,'error'=>'Crossref: ' . $response->get_error_message(),'used'=>false];
            return [];
        }

        $status=(int)wp_remote_retrieve_response_code($response);
        if ($status<200 || $status>=300) {
            $diag=['http'=>$status,'error'=>'Crossref HTTP ' . $status,'used'=>false];
            return [];
        }

        $json=json_decode((string)wp_remote_retrieve_body($response),true);
        if (!is_array($json)) {
            $diag=['http'=>$status,'error'=>'Crossref returned invalid JSON','used'=>false];
            return [];
        }

        $message=$json['message']??[];
        $list=is_array($message) ? ($message['author']??[]) : [];
        if (!is_array($list) || !$list) {
            $diag=['http'=>$status,'error'=>'Crossref returned no author list','used'=>false];
            return [];
        }

        $authors=[];
        foreach ($list as $a) {
            if (!is_array($a)) continue;
            $given=trim((string)($a['given']??''));
            $family=trim((string)($a['family']??''));
            $literal=trim((string)($a['name']??''));

            if ($family!=='') $name=trim($given . ' ' . $family);
            elseif ($literal!=='') $name=$literal;
            else $name='';

            if ($name!=='' && !isset($authors[$name])) $authors[$name]=$name;
        }

        $authors=array_values($authors);
        if ($authors) set_transient($cache_key,$authors,30*DAY_IN_SECONDS);

        $diag=['http'=>$status,'error'=>$authors?'':'Crossref author list could not be parsed','used'=>false];
        return $authors;
    }

    private static function display_name_from_scopus_authname(string $name): string {
        $name=trim(preg_replace('/\s+/', ' ', $name));
        if ($name==='') return '';
        // Scopus commonly uses "Surname, Given name/initials" in authname.
        if (strpos($name, ',')!==false) {
            [$surname,$given]=array_pad(array_map('trim', explode(',', $name, 2)),2,'');
            return trim($given . ' ' . $surname);
        }
        return $name;
    }

    public static function render_admin(): void {
        if (!current_user_can('manage_options')) return;
        global $wpdb;
        $table=self::table();
        $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $historical=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE record_source=%s",'drupal'));
        $scopus=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE record_source=%s",'scopus'));
        $last_sync=(string)get_option(self::OPTION_LAST_SYNC,'Never');
        $result=get_transient('pang_publications_admin_result_' . get_current_user_id());
        if ($result) delete_transient('pang_publications_admin_result_' . get_current_user_id());
        ?>
        <div class="wrap">
            <h1>PANG Publications</h1>
            <p>Historical archive from Drupal (2004-2024) + automatic Scopus synchronization for 9 PANG members from 2025 onward.</p>
            <?php if (is_array($result)): ?>
                <div class="notice <?php echo !empty($result['ok'])?'notice-success':'notice-error'; ?>"><p><strong><?php echo esc_html((string)($result['message']??'')); ?></strong> Inserted: <?php echo (int)($result['inserted']??0); ?> · Updated: <?php echo (int)($result['updated']??0); ?> · Skipped: <?php echo (int)($result['skipped']??0); ?></p></div>
            <?php endif; ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;max-width:900px;margin:20px 0;">
                <?php foreach ([['Total publications',$total],['Drupal 2004-2024',$historical],['Scopus 2025-current',$scopus],['Last sync',$last_sync]] as $card): ?>
                    <div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #1677b8;padding:16px;"><strong><?php echo esc_html((string)$card[0]); ?></strong><div style="font-size:24px;margin-top:6px;"><?php echo esc_html((string)$card[1]); ?></div></div>
                <?php endforeach; ?>
            </div>

            <h2>Scopus settings</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:760px;background:#fff;border:1px solid #dcdcde;padding:20px;">
                <input type="hidden" name="action" value="pang_publications_save_settings">
                <?php wp_nonce_field('pang_publications_save_settings'); ?>
                <table class="form-table"><tbody>
                    <tr><th>API Key</th><td><?php if (defined('PANG_SCOPUS_API_KEY')): ?><code>Configured in wp-config.php</code><?php else: ?><input type="password" name="api_key" class="regular-text" autocomplete="new-password" placeholder="Leave blank to keep current key"><p class="description">Current key: <?php echo self::api_key()!==''?'configured':'not configured'; ?>. The key saved by PANG Scopus Diagnostic is reused automatically.</p><?php endif; ?></td></tr>
                    <tr><th>Institutional Token</th><td><?php if (defined('PANG_SCOPUS_INSTTOKEN')): ?><code>Configured in wp-config.php</code><?php else: ?><input type="password" name="insttoken" class="regular-text" autocomplete="new-password" placeholder="Optional"><p class="description">Not required in the tests performed so far.</p><?php endif; ?></td></tr>
                </tbody></table>
                <?php submit_button('Save settings'); ?>
            </form>

            <h2 style="margin-top:28px;">Data management</h2>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="pang_publications_import_history"><?php wp_nonce_field('pang_publications_import_history'); ?><button class="button">Re-import historical archive 2004-2024</button></form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="pang_publications_sync"><?php wp_nonce_field('pang_publications_sync'); ?><button class="button button-primary">Synchronize Scopus now</button></form>
            </div>
            <p class="description">Automatic Scopus synchronization runs once per week via WP-Cron.</p>

            <h2 style="margin-top:28px;">PANG members synchronized</h2>
            <table class="widefat striped" style="max-width:760px;"><thead><tr><th>Name</th><th>Scopus Author ID</th></tr></thead><tbody><?php foreach(self::authors() as $name=>$id): ?><tr><td><?php echo esc_html($name); ?></td><td><code><?php echo esc_html($id); ?></code></td></tr><?php endforeach; ?></tbody></table>

            <?php $last_log=get_option(self::OPTION_LAST_LOG,[]); if (is_array($last_log) && !empty($last_log['authors'])): ?>
            <h2 style="margin-top:28px;">Last Scopus sync diagnostics</h2>
            <table class="widefat striped" style="max-width:1280px;"><thead><tr><th>Name</th><th>HTTP</th><th>Found</th><th>Processed</th><th>Full authors</th><th>Single author</th><th>Crossref enriched</th><th>Crossref errors</th><th>Scopus enrichment errors</th><th>Message</th></tr></thead><tbody>
            <?php foreach($last_log['authors'] as $row): ?><tr><td><?php echo esc_html((string)($row['name']??'')); ?></td><td><?php echo esc_html((string)($row['http']??'')); ?></td><td><?php echo esc_html((string)($row['total']??'')); ?></td><td><?php echo esc_html((string)($row['processed']??'')); ?></td><td><?php echo esc_html((string)($row['full_author_records']??0)); ?></td><td><?php echo esc_html((string)($row['single_author_records']??0)); ?></td><td><?php echo esc_html((string)($row['crossref_enriched']??0)); ?></td><td><?php echo esc_html((string)($row['crossref_errors']??0)); ?></td><td><?php echo esc_html((string)($row['enrichment_errors']??0)); ?></td><td><?php $msg=(string)(($row['error']??'')!==''?$row['error']:(($row['crossref_error_example']??'')!==''?$row['crossref_error_example']:($row['enrichment_error_example']??''))); echo esc_html($msg); ?></td></tr><?php endforeach; ?>
            </tbody></table>
            <?php endif; ?>

            <h2 style="margin-top:28px;">Shortcode</h2>
            <p>Create a WordPress page named <strong>Publications</strong> and insert:</p><p><code>[pang_publications]</code></p>
        </div>
        <?php
    }

    public static function register_assets(): void {
        wp_register_style('pang-publications', plugins_url('assets/pang-publications.css', __FILE__), [], self::VERSION);
        wp_register_script('pang-publications', plugins_url('assets/pang-publications.js', __FILE__), [], self::VERSION, true);
    }

    public static function shortcode($atts=[]): string {
        global $wpdb;
        wp_enqueue_style('pang-publications');
        wp_enqueue_script('pang-publications');
        $rows=$wpdb->get_results('SELECT * FROM ' . self::table() . ' ORDER BY year DESC, pub_date DESC, title ASC', ARRAY_A);
        if (!$rows) return '<div class="pang-publications-message">No publications available.</div>';
        $years=[]; $authors=[]; $types=[];
        foreach ($rows as $r) {
            if ((int)$r['year']>0) $years[(int)$r['year']]=(int)$r['year'];
            foreach (preg_split('/\s*;\s*/',(string)$r['pang_authors'])?:[] as $a) if (trim($a)!=='') $authors[trim($a)]=trim($a);
            if (trim((string)$r['document_type'])!=='') $types[trim((string)$r['document_type'])]=trim((string)$r['document_type']);
        }
        rsort($years); ksort($authors,SORT_NATURAL|SORT_FLAG_CASE); ksort($types,SORT_NATURAL|SORT_FLAG_CASE);
        ob_start(); ?>
        <div class="pang-publications" data-pang-publications>
            <div class="pang-publications__toolbar">
                <div class="pang-publications__filter pang-publications__filter--search"><label>Search</label><div class="pang-publications__search-wrap"><span class="pang-publications__search-icon">⌕</span><input type="search" data-pang-search placeholder="Title, author, journal, DOI…"></div></div>
                <div class="pang-publications__filter"><label>Year</label><select data-pang-year><option value="">All years</option><?php foreach($years as $y): ?><option value="<?php echo esc_attr((string)$y); ?>"><?php echo esc_html((string)$y); ?></option><?php endforeach; ?></select></div>
                <div class="pang-publications__filter"><label>PANG member</label><select data-pang-author><option value="">All members</option><?php foreach($authors as $a): ?><option value="<?php echo esc_attr($a); ?>"><?php echo esc_html($a); ?></option><?php endforeach; ?></select></div>
                <div class="pang-publications__filter"><label>Type</label><select data-pang-type><option value="">All types</option><?php foreach($types as $t): ?><option value="<?php echo esc_attr($t); ?>"><?php echo esc_html($t); ?></option><?php endforeach; ?></select></div>
                <div class="pang-publications__filter pang-publications__filter--reset"><span class="pang-publications__label-spacer">&nbsp;</span><button type="button" class="pang-publications__reset" data-pang-reset>Reset</button></div>
            </div>
            <div class="pang-publications__summary"><strong data-pang-visible-count><?php echo count($rows); ?></strong> publications</div>
            <div class="pang-publications__table-wrap"><table class="pang-publications__table"><thead><tr><th class="pang-publications__col-year">Year</th><th class="pang-publications__col-title">Publication</th><th class="pang-publications__col-authors">Authors</th><th class="pang-publications__col-source">Source</th><th class="pang-publications__col-actions">Links &amp; Cite</th></tr></thead><tbody>
            <?php foreach($rows as $r):
                $search=implode(' ',[$r['title'],$r['authors'],$r['source_title'],$r['doi'],$r['document_type']]);
                $cite=self::citation_payload($r);
            ?>
                <tr data-pang-row data-year="<?php echo esc_attr((string)$r['year']); ?>" data-authors="<?php echo esc_attr((string)$r['pang_authors']); ?>" data-type="<?php echo esc_attr((string)$r['document_type']); ?>" data-search="<?php echo esc_attr($search); ?>">
                    <td data-label="Year" class="pang-publications__year-cell"><?php echo esc_html((string)$r['year']); ?></td>
                    <td data-label="Publication" class="pang-publications__title-cell"><strong><?php echo esc_html((string)$r['title']); ?></strong><?php if($r['document_type']): ?><div class="pang-publications__meta"><?php echo esc_html((string)$r['document_type']); ?></div><?php endif; ?></td>
                    <td data-label="Authors" class="pang-publications__authors-cell"><?php echo esc_html((string)$r['authors']); ?></td>
                    <td data-label="Source" class="pang-publications__source-cell"><?php echo esc_html((string)$r['source_title']); ?><?php if($r['volume']||$r['issue']||$r['pages']): ?><div class="pang-publications__meta"><?php echo esc_html(self::vol_issue_pages($r)); ?></div><?php endif; ?></td>
                    <td data-label="Links & Cite" class="pang-publications__actions-cell">
                        <div class="pang-publications__actions">
                            <?php if($r['doi']): ?><a class="pang-publications__pill" href="<?php echo esc_url('https://doi.org/'.$r['doi']); ?>" target="_blank" rel="noopener">DOI</a><?php elseif($r['url']): ?><a class="pang-publications__pill" href="<?php echo esc_url((string)$r['url']); ?>" target="_blank" rel="noopener">Open</a><?php endif; ?>
                            <?php if($r['scopus_eid']): ?><a class="pang-publications__pill pang-publications__pill--secondary" href="<?php echo esc_url('https://www.scopus.com/record/display.uri?eid='.rawurlencode((string)$r['scopus_eid']).'&origin=resultslist'); ?>" target="_blank" rel="noopener">Scopus</a><?php endif; ?>
                            <button type="button" class="pang-publications__cite" data-pang-cite data-cite="<?php echo esc_attr(wp_json_encode($cite,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)); ?>">Cite</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
            <div class="pang-publications__provenance">Publication data are derived from the PANG historical archive and Scopus. Recent records are periodically synchronized with Scopus.</div>
            <div class="pang-publications__empty" data-pang-empty hidden>No publications match the selected filters.</div>
            <div class="pang-cite-modal" data-pang-cite-modal hidden role="dialog" aria-modal="true" aria-labelledby="pang-cite-title">
                <div class="pang-cite-modal__backdrop" data-pang-cite-close></div>
                <div class="pang-cite-modal__panel">
                    <button type="button" class="pang-cite-modal__close" data-pang-cite-close aria-label="Close">×</button>
                    <h3 id="pang-cite-title">Cite this publication</h3>
                    <div class="pang-cite-modal__title" data-pang-cite-title></div>
                    <div class="pang-cite-modal__group">
                        <div class="pang-cite-modal__group-title">Copy citation</div>
                        <div class="pang-cite-modal__buttons">
                            <button type="button" data-cite-format="apa">APA</button>
                            <button type="button" data-cite-format="ieee">IEEE</button>
                            <button type="button" data-cite-format="doi">DOI</button>
                        </div>
                    </div>
                    <div class="pang-cite-modal__group">
                        <div class="pang-cite-modal__group-title">Export</div>
                        <div class="pang-cite-modal__buttons">
                            <button type="button" data-cite-format="bibtex">BibTeX</button>
                            <button type="button" data-cite-format="ris">RIS</button>
                        </div>
                    </div>
                    <label class="pang-cite-modal__preview-label" for="pang-cite-preview">Citation preview</label>
                    <textarea id="pang-cite-preview" class="pang-cite-modal__preview" data-pang-cite-preview readonly></textarea>
                    <div class="pang-cite-modal__status" data-pang-cite-status aria-live="polite"></div>
                </div>
            </div>
        </div>
        <?php return (string)ob_get_clean();
    }

    private static function vol_issue_pages(array $r): string {
        $bits=[];
        if ($r['volume']!=='') $bits[]='Vol. '.$r['volume'];
        if ($r['issue']!=='') $bits[]='No. '.$r['issue'];
        if ($r['pages']!=='') $bits[]='pp. '.$r['pages'];
        return implode(' · ',$bits);
    }

    private static function citation_payload(array $r): array {
        return [
            'title'=>(string)$r['title'],'authors'=>(string)$r['authors'],'year'=>(string)$r['year'],
            'source'=>(string)$r['source_title'],'volume'=>(string)$r['volume'],'issue'=>(string)$r['issue'],
            'pages'=>(string)$r['pages'],'doi'=>(string)$r['doi'],'type'=>(string)$r['document_type'],
            'publisher'=>(string)$r['publisher'],'issn'=>(string)$r['issn'],'isbn'=>(string)$r['isbn'],
        ];
    }
}

register_activation_hook(__FILE__, ['PANG_Publications','activate']);
register_deactivation_hook(__FILE__, ['PANG_Publications','deactivate']);
PANG_Publications::init();
