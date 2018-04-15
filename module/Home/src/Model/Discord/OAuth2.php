<?php

namespace Home\Model\Discord;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;

class OAuth2
{
    private $clientId;
    private $clientSecret;

    private $authorizationUrl = 'https://discordapp.com/api/oauth2/authorize';
    private $tokenUrl = 'https://discordapp.com/api/oauth2/token';

    private $redirectUrl = 'https://thedialgateambot.aggressivegaming.org/login';

    const SCOPE_BOT = 'bot';
    const SCOPE_CONNECTIONS = 'connections';
    const SCOPE_EMAIL = 'email';
    const SCOPE_IDENTIFY = 'identify';
    const SCOPE_GUILDS = 'guilds';
    const SCOPE_GUILDS_JOIN = 'guilds.join';
    const SCOPE_GDM_JOIN = 'gdm.join';
    const SCOPE_MESSAGES_READ = 'messages.read';
    const SCOPE_RPC = 'rpc';
    const SCOPE_RPC_API = 'rpc.api';
    const SCOPE_RPC_NOTIFICAITONS_READ = 'rpc.notifications.read';
    const SCOPE_WEBHOOK_INCOMING = 'webhook.incoming';

    public function __construct($clientId, $clientSecret, $redirectUrl = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        if (isset($redirectUrl))
            $this->redirectUrl = $redirectUrl;
    }

    /**
     * Get authorization url string.
     * @param $scopes string|array Scopes
     * @param $state string Csrf token.
     * @return string Authorization url string.
     */
    public function getAuthorizationUrl($scopes, $state)
    {
        if (is_array($scopes))
            $scope = join("%20", $scopes);
        else
            $scope = $scopes;

        return sprintf("%s?response_type=code&client_id=%s&scope=%s&state=%s&redirect_uri=%s",
            $this->authorizationUrl, $this->clientId, $scope, $state, urlencode($this->redirectUrl));
    }

    /**
     * Get access token.
     * @param $code string
     * @return array
     */
    public function getAccessToken($code)
    {
        $client = new Client();

        $request = new Request();
        $request->setUri($this->tokenUrl);
        $request->setMethod(Request::METHOD_POST);
        $request->getPost('client_id', $this->clientId);
        $request->getPost('client_secret', $this->clientSecret);
        $request->getPost('grant_type', 'authorization_code');
        $request->getPost('code', $code);
        $request->getPost('redirect_uri', $this->redirectUrl);
        $request->getHeaders()->addHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-length' => strlen($request->getPost()->toString())
        ]);

        $response = $client->send($request);
        $json = $response->getBody();
        /*$jsonArray = Json::decode($json, Json::TYPE_ARRAY);

        $_SESSION['access_token'] = $jsonArray['access_token'];
        $_SESSION['token_type'] = $jsonArray['token_type'];
        $_SESSION['expires_in'] = $jsonArray['expires_in'];
        $_SESSION['refresh_token'] = $jsonArray['refresh_token'];
        $_SESSION['scope'] = $jsonArray['scope'];
        $_SESSION['session_start_time'] = microtime(true);

        return $jsonArray;*/
        return $json;
    }

    /**
     * Get new access token.
     * @return array
     */
    public function getNewAccessToken()
    {
        $client = new Client();

        $request = new Request();
        $request->setUri($this->tokenUrl);
        $request->setMethod(Request::METHOD_POST);
        $request->getHeaders()->addHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        $request->getPost('client_id', $this->clientId);
        $request->getPost('client_secret', $this->clientSecret);
        $request->getPost('grant_type', 'refresh_token');
        $request->getPost('refresh_token', $_SESSION['refresh_token']);
        $request->getPost('redirect_uri', $this->redirectUrl);

        $response = $client->send($request);
        $json = $response->getBody();
        $jsonArray = Json::decode($json, Json::TYPE_ARRAY);

        $_SESSION['access_token'] = $jsonArray['access_token'];
        $_SESSION['token_type'] = $jsonArray['token_type'];
        $_SESSION['expires_in'] = $jsonArray['expires_in'];
        $_SESSION['refresh_token'] = $jsonArray['refresh_token'];
        $_SESSION['scope'] = $jsonArray['scope'];
        $_SESSION['session_start_time'] = microtime(true);

        return $jsonArray;
    }

    /**
     * Validate if the access token is still valid.
     * @return bool true if the access token is still valid, else false.
     */
    public function isAccessTokenValid()
    {
        return microtime(true) - $_SESSION['session_start_time'] < $_SESSION['expires_in'];
    }
}