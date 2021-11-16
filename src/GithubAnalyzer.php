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
        add_action('plugins_loaded', [$this, 'set_credentials']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action("wp_ajax_{$this->handle_name}", [$this, 'ajax_handler']);
        add_action("wp_ajax_nopriv_{$this->handle_name}", [$this, 'ajax_handler']);
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

    public function ajax_handler(): void
    {
        check_ajax_referer($this->handle_name);
        wp_send_json($this->api->repositores($_POST['search']));
    }
}
