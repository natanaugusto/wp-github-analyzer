<?php
namespace GithubAnalyzer;

/**
 * This class contains all the functions that actually retrieve information from the GitHub API
 */
class GithubAPI
{
    const GITHUB_URL = 'https://api.github.com/';

    protected $clientId = null;
    protected $clientSecret = null;
    protected $accessToken = null;
    protected $accessTokenUsername = null;

    public function __construct(
        $clientId = null,
        $clientSecret = null,
        $accessToken = null,
        $accessTokenUsername = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenUsername = $accessTokenUsername;
    }

    /**
     * Search for repositories from the GitHub API
     * @param string $search Search subject
     * @return array The response from the GitHub API
     */
    public function repositores($search = null): array
    {
        $this->log("repositores( {$search} )");
        return $this->call('search/repositories', [
            'q' => $search,
        ]);
    }

	/**
	 * Search for users from the GitHub API
	 * @param string $search Search subject
	 * @return array The response from the GitHub API
	 * (Yes, that two methods are basicle the same)
	 */
    public function users($search = null): array
    {
        $this->log("repositores( {$search} )");
        return $this->call('search/users', [
            'q' => $search,
        ]);
    }

	/**
	 * Execute a call using wp_remote_get
	 *
	 * @param string $endpoint
	 * @param array $query
	 *
	 * @return array
	 */
	protected function call(string $endpoint, array $query = []): array
    {
        $url = self::GITHUB_URL . $endpoint;
        // Allow users to supply auth details to enable a higher rate limit [Deprecated]
        if (!empty($this->clientId) && !empty($this->clientSecret)) {
            $query = array_merge([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ], $query);
        }

        $url = add_query_arg($query, $url);
        $args = [
            'user-agent' => 'WordPress Github',
        ];
        if (!empty($this->accessTokenUsername) && !empty($this->accessToken)) {
            $args['headers'] = [
                'Authorization' => 'Basic ' . base64_encode("{$this->accessTokenUsername}:{$this->accessToken}"),
            ];
        }
        $this->log(__FUNCTION__ . " : {$url}");
        $results = wp_remote_get($url, $args);
        $results['response']['body'] = json_decode($results['body'], true);
        return $results['response'];
    }

	/**
	 * Guess what that does?
	 *
	 * @param string $msg
	 *
	 * @return void
	 */
    protected function log(string $msg): void
    {
        log("[GitHub]: " . $msg);
    }
}
