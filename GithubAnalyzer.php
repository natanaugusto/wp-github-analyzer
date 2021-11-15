<?php
/**
 * Plugin Name: Github Analyzer
 * Description: A simple WordPress Plugin to analyzer GitHub Profiles
 * Author: Natan Augusto
 * Version: 0.0.1
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * This plugin is almost a Ctrl+C, Ctrl+v from the repository below
 * @link https://github.com/leewillis77/wp-github-oembed
 */
class GithubAnalyzer
{
    /**
     * @var string $handleName
     */
    protected string $handleName = 'github-analyzer-block-editor';

    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function deactivate()
    {
        remove_action('enqueue_block_editor_assets', [$this, 'action_enqueue_block_editor_assets']);
        remove_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
    }

    /**
     * Activate the plugin
     *
     * @return void
     */
    public function activate()
    {
        add_action('enqueue_block_editor_assets', [$this, 'action_enqueue_block_editor_assets']);
        add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
    }

    /**
     * Fires when scripts and styles are enqueued.
     *
     */
    public function action_wp_enqueue_scripts(): void
    {
        wp_enqueue_style(
            $this->handleName,
            plugins_url('block.css', __FILE__),
            []
        );
    }

    /**
     * Fires after block assets have been enqueued for the editing interface.
     *
     */
    public function action_enqueue_block_editor_assets(): void
    {
        // WP Enqueue
        wp_enqueue_script(
            $this->handleName,
            plugins_url('block.js', __FILE__),
            ['wp-blocks', 'wp-element']
        );
        wp_enqueue_style(
            $this->handleName,
            plugins_url('block.css', __FILE__),
            []
        );
    }

}

new GithubAnalyzer();
