<?php
/**
 * Plugin Name: Github Analyzer
 * Description: A simple WordPress Plugin to analyzer GitHub Profiles
 * Author: Natan Augusto
 * Version: 0.0.1
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

class GithubAnalyzer
{
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_uninstall_hook(__FILE__, [$this, 'uninstall']);
    }

    public function activate()
    {
        
    }

    public function uninstall()
    {

    }
}

new GithubAnalyzer();
