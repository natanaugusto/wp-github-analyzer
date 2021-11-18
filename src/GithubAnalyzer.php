<?php
namespace GithubAnalyzer;

class GithubAnalyzer extends GithubAPI
{
    /**
     * @var GithubAPI $api
     */
    protected $api;
    /**
     * @var string $handle_name
     */
    protected $handle_name = 'github_analyzer';

    public function __construct()
    {
        $indexFile = plugin_dir_path(__DIR__) . 'index.php';
        register_activation_hook($indexFile, [$this, 'create_database_table']);
        register_deactivation_hook($indexFile, [$this, 'drop_database_table']);

        add_action('plugins_loaded', [$this, 'set_credentials']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action("wp_ajax_{$this->handle_name}_search", [$this, 'ajax_search_handler']);
        add_action("wp_ajax_{$this->handle_name}_add", [$this, 'ajax_add_handler']);
        add_action("wp_ajax_nopriv_{$this->handle_name}_search", [$this, 'ajax_search_handler']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);

        add_filter('http_request_timeout', [$this, 'http_request_timeout']);
    }

    /**
     * If you find yourself hitting rate limits, then you can register an application
     * with GitHub(http://developer.github.com/v3/oauth/) use the filters here to
     * provide the credentials.
     */
    public function set_credentials()
    {
        $this->api = new GithubAPI(
            apply_filters("{$this->handle_name}_client_id", null),
            apply_filters("{$this->handle_name}_client_secret", null),
            apply_filters("{$this->handle_name}_access_token", null),
            apply_filters("{$this->handle_name}_access_token_username", null)
        );
    }

    /**
     * Extend the timeout since API calls can easily exceed 5 seconds
     *
     * @param int $seconds The current timeout setting
     *
     * @return int          The revised timeout setting
     */
    public function http_request_timeout($seconds)
    {
        return $seconds < 25 ? 25 : $seconds;
    }

    /**
     * Fires when scripts and styles are enqueued.
     *
     * @return void
     */
    public function enqueue_assets(): void
    {
        wp_enqueue_script(
            'jquery',
            plugins_url('assets/scripts/jquery.js', __DIR__),
            ['wp-blocks', 'wp-element']
        );

        wp_enqueue_script(
            'ajax-script',
            plugins_url('assets/scripts/github.js', __DIR__),
            ['jquery'],
            false,
            true
        );

        wp_localize_script(
            'ajax-script',
            "{$this->handle_name}_ajax",
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce($this->handle_name),
            ]
        );

        wp_enqueue_style(
            "{$this->handle_name}_style",
            plugins_url('assets/styles/block.css', __DIR__),
            []
        );
    }

    /**
     * Fires after block assets have been enqueued for the editing interface.
     *
     */
    public function enqueue_block_editor_assets(): void
    {
        // WP Enqueue
        wp_enqueue_script(
            "{$this->handle_name}_block_js",
            plugins_url('assets/scripts/block.js', __DIR__),
            ['wp-blocks', 'wp-element']
        );
        wp_enqueue_style(
            "{$this->handle_name}_block_css",
            plugins_url('assets/styles/block.css', __DIR__),
            []
        );
    }

    /**
     * Handle the search from GitHubAPI
     *
     * @return void
     */
    public function ajax_search_handler(): void
    {
        check_ajax_referer($this->handle_name);

        // Mysterious, isn't it?
        wp_send_json($this->api->{$_POST['type']}($_POST['search']));
    }

    /**
     * Add a new data from a github response
     *
     * @return void
     */
    public function ajax_add_handler(): void
    {
        check_ajax_referer($this->handle_name);
        global $wpdb;
        $table = $this->table_name($wpdb);
        # TODO: Insert/Update method
        $wpdb->insert(
            $table,
            [
                'id' => (int) $_POST['data']['id'],
                'type' => $_POST['type'],
                'data' => json_encode($_POST['data']),
            ],
            ['%d', '%s', '%s']
        ) ? wp_send_json(null, 201) : wp_send_json(["message" => "Error"], 500);

    }

    /**
     * The method does what they name say
     *
     * @return void
     */
    public function drop_database_table(): void
    {
        $this->run_query("DROP TABLE IF EXISTS :table");
    }

    /**
     * The method does what they name say
     *
     * @return void
     */

    public function create_database_table(): void
    {
        $this->run_query(<<<SQL
		CREATE TABLE IF NOT EXISTS :table (
			`id` INT NOT NULL,
			`type` enum('user', 'repository') default 'user',
			`data` text DEFAULT NULL,
			PRIMARY KEY(`id`)
		)
		SQL);
    }

    /**
     * Execute an CREATE/DROP query in database
     *
     * @param string $sql
     *
     * @return void
     */
    private function run_query(string $sql): void
    {
        global $wpdb;
        $table = $this->table_name($wpdb);
        $sql = str_replace(':table', $table, $sql);
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

    }

    /**
     * Get the plugin table name
     *
     * @param object $wpdb
     *
     * @return string
     */
    private function table_name(object $wpdb): string
    {
        return "{$wpdb->prefix}{$this->handle_name}_data";
    }
}
