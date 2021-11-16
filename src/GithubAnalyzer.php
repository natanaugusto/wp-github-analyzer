<?php
namespace GithubAnalyzer;

class GithubAnalyzer
{
    /**
     * @var string $handleName
     */
    protected $handleName = 'github_analyzer';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_ajax']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_action("wp_ajax_{$this->handleName}", [$this, 'ajax_handler']);
        add_action("wp_ajax_nopriv_{$this->handleName}", [$this, 'ajax_handler']);
    }

    /**
     * Fires when scripts and styles are enqueued.
     *
     * @return void
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script(
            'jquery',
            plugins_url('assets/scripts/jquery.js', __DIR__),
            ['wp-blocks', 'wp-element']
        );

        wp_enqueue_style(
            "{$this->handleName}_style",
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
            "{$this->handleName}_block_js",
            plugins_url('assets/scripts/block.js', __DIR__),
            ['wp-blocks', 'wp-element']
        );
        wp_enqueue_style(
            "{$this->handleName}_block_css",
            plugins_url('assets/styles/block.css', __DIR__),
            []
        );
    }

    public function enqueue_ajax(): void
    {
        wp_enqueue_script(
            'ajax-script',
            plugins_url('assets/scripts/github.js', __DIR__),
            ['jquery'],
            false,
            true
        );

        wp_localize_script(
            'ajax-script',
            "{$this->handleName}_ajax",
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce($this->handleName),
            ]
        );
    }

    public function ajax_handler(): void
    {
        check_ajax_referer($this->handleName);
		$search = $_POST['search'];

		wp_send_json(["The search is {$search}"]);
    }

}
