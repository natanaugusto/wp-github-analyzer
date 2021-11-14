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
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_uninstall_hook(__FILE__, [$this, 'uninstall']);
        add_action('init', [$this, 'registerOembedHandler']);
        add_action('init', [$this, 'handleOembed']);

    }

    public function activate()
    {

    }

    public function uninstall()
    {

    }

    /**
     * Register the oEmbed provider, and point it at a local endpoint since github
     * doesn't directly support oEmbed yet. Our local endpoint will use the github
     * API to fulfil the request.
     */
    public function registerOembedHandler(): void
    {
        $oembed_url = home_url();
        $key = $this->getKey();
        $oembed_url = add_query_arg([
            'github_oembed' => $key,
            $oembed_url,
        ]);
        wp_oembed_add_provider(
            '#https?://github.com/.*#i',
            $oembed_url,
            true
        );
    }

    /**
     * Check whether this is an oembed request, handle if it is
     * Ignore it if not.
     * Insert rant here about WP's lack of a front-end AJAX handler.
     */
    public function handleOembed()
    {
        if (empty($_GET['github_oembed'])) {
            return;
        }
        // Check this request is valid
        if ($_GET['github_oembed'] !== $this->getKey()) {
            header('HTTP/1.0 403 Forbidden');
            die('Sad Octocat is sad.');
        }

        // Check we have the required information
        $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : null;
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;

        if (!empty($format) && 'json' !== $format) {
            header('HTTP/1.0 501 Not implemented');
            die('This octocat only does json');
        }

        if (!$url) {
            header('HTTP/1.0 404 Not Found');
            die('Octocat is lost, and afraid');
        }

        // Issues / Milestones
        if (preg_match('#https?://github.com/([^/]*)/([^/]*)/graphs/contributors/?$#i', $url, $matches) && !empty($matches[2])) {
            $this->oembedGithubRepoContributors($matches[1], $matches[2]);
        } elseif (preg_match('#https?://github.com/([^/]*)/([^/]*)/issues.*$#i', $url, $matches) && !empty($matches[2])) {
            if (preg_match('#issues.?milestone=([0-9]*)#i', $url, $milestones)) {
                $milestone = $milestones[1];
            } else {
                $milestone = null;
            }
            if ($milestone) {
                $this->oembedGithubRepoMilestoneSummary($matches[1], $matches[2], $milestone);
            }
        } elseif (preg_match('#https?://github.com/([^/]*)/([^/]*)/milestone/([0-9]*)$#i', $url, $matches)) {
            // New style milestone URL, e.g. https://github.com/example/example/milestone/1.
            $this->oembedGithubRepoMilestoneSummary($matches[1], $matches[2], $matches[3]);
        } elseif (preg_match('#https?://github.com/([^/]*)/([^/]*)/?$#i', $url, $matches) && !empty($matches[2])) {
            // Repository.
            $this->oembedGithubRepo($matches[1], $matches[2]);
        } elseif (preg_match('#https?://github.com/([^/]*)/?$#i', $url, $matches)) {
            // User.
            $this->oembedGithubAuthor($matches[1]);
        }

    }

    /**
     * Generate a unique key that can be used on our requests to stop others
     * hijacking our internal oEmbed API
     * @return string The site key
     */
    private function getKey(): string
    {
        $key = get_option('github_oembed_key');
        if (!$key) {
            $key = md5(time() . rand(0, 65535));
            add_option('github_oembed_key', $key, '', 'yes');
        }

        return $key;
    }

    /**
     * Retrieve a list of contributors for a project
     *
     * @param string $owner The owner of the repository
     * @param string $repository The repository name
     */
    private function oembedGithubRepoContributors($owner, $repository)
    {
        $data = [];
        $data['repo'] = $this->api->get_repo($owner, $repository);
        $data['contributors'] = $this->api->get_repo_contributors($owner, $repository);
        $data['gravatar_size'] = apply_filters('github_oembed_gravatar_size', 64);
        $data['logo_class'] = apply_filters('wp_github_oembed_logo_class', 'github-logo-octocat');
        $data['details_expanded'] = apply_filters('wp_github_oembed_contributor_details_expanded', true);

        $response = new stdClass();
        $response->type = 'rich';
        $response->width = '10';
        $response->height = '10';
        $response->version = '1.0';
        $response->title = $data['repo']->description;
        $response->html = $this->processTemplate(
            'repository_contributors.php', $data);

        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }

    /**
     * Retrieve the summary information for a repo's milestone, and
     * output it as an oembed response
     */
    private function oembedGithubRepoMilestoneSummary($owner, $repository, $milestone)
    {
        $data = [];
        $data['repo'] = $this->api->get_repo($owner, $repository);
        $data['summary'] = $this->api->get_repo_milestone_summary($owner, $repository, $milestone);
        $data['logo_class'] = apply_filters('wp_github_oembed_logo_class', 'github-logo-octocat');

        $response = new stdClass();
        $response->type = 'rich';
        $response->width = '10';
        $response->height = '10';
        $response->version = '1.0';
        $response->title = $data['repo']->description;
        $response->html = $this->processTemplate(
            'repository_milestone_summary.php', $data);

        header('Content-Type: application/json');
        echo json_encode($response);
        die();

    }

    /**
     * Retrieve the information from github for a repo, and
     * output it as an oembed response
     */
    private function oembedGithubRepo($owner, $repository)
    {
        $data = [
            'owner_slug' => $owner,
            'repo_slug' => $repository,
        ];
        $data['repo'] = $this->api->get_repo($owner, $repository);
        $data['commits'] = $this->api->get_repo_commits($owner, $repository);
        $data['logo_class'] = apply_filters('wp_github_oembed_logo_class', 'github-logo-mark');
        $data['details_expanded'] = apply_filters('wp_github_oembed_repository_commit_details_expanded', true);

        $response = new stdClass();
        $response->type = 'rich';
        $response->width = '10';
        $response->height = '10';
        $response->version = '1.0';
        $response->title = $data['repo']->description;
        $response->html = $this->processTemplate(
            'repository.php', $data);

        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }

    /**
     * Retrieve the information from github for an author, and output
     * it as an oembed response
     */
    private function oembedGithubAuthor($owner)
    {
        $data = [];
        $data["owner"] = $owner;
        $data["owner_info"] = $this->api->get_user($owner);
        $data["logo_class"] = apply_filters('wp_github_oembed_logo_class',
            'github-logo-octocat');

        $response = new stdClass();
        $response->type = 'rich';
        $response->width = '10';
        $response->height = '10';
        $response->version = '1.0';
        $response->title = $data['owner_info']->name;
        $response->html = $this->processTemplate(
            'author.php', $data);

        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }

    /**
     * Capture then return output of template, provided theme or fallback to plugin default.
     *
     * @param string $template The template name to process.
     * @param string $data Array, object, or variable that the template needs.
     *
     * @return string
     */
    private function processTemplate($template, $data)
    {
        ob_start();
        if (!locate_template('wp-github-analyzer/' . $template, true)) {
            require_once 'templates/' . $template;
        }

        return ob_get_clean();
    }
}

new GithubAnalyzer();
