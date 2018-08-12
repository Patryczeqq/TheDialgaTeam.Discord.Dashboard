<?php

namespace Home\Model\Discord;

use Home\Model\Discord\OAuth2\AccessToken;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Hydrator\ClassMethods;
use Zend\Json\Json;
use Zend\Session\Container;

/**
 * Class OAuth2
 * @package Home\Model\Discord
 */
class OAuth2
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $authorizationUrl = 'https://discordapp.com/api/oauth2/authorize';

    /**
     * @var string
     */
    private $tokenUrl = 'https://discordapp.com/api/oauth2/token';

    /**
     * @var string
     */
    private $redirectUrl = 'https://discord.aggressivegaming.org/login';

    /**
     * @var Container
     */
    private $sessionContainer;

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

    /**
     * OAuth2 constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param null|string $redirectUrl
     */
    public function __construct($clientId, $clientSecret, $redirectUrl = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        if (isset($redirectUrl))
            $this->redirectUrl = $redirectUrl;

        $this->sessionContainer = new Container('discord_oauth2_access_token');
    }

    /**
     * @param string[] $scopes
     * @param string $state
     * @return string
     */
    public function getAuthorizationUrl($scopes, $state)
    {
        $this->clearAccessTokenInSession();

        $scope = is_array($scopes) ? join("%20", $scopes) : $scopes;

        return sprintf("%s?response_type=code&client_id=%s&scope=%s&state=%s&redirect_uri=%s",
            $this->authorizationUrl, $this->clientId, $scope, $state, urlencode($this->redirectUrl));
    }

    /**
     * @param $authorizationCode
     * @return null|object|AccessToken
     */
    public function getAccessToken($authorizationCode)
    {
        if (!isset($authorizationCode))
            return null;

        $accessTokenObject = $this->getAccessTokenResponse('authorization_code', $authorizationCode);
        $this->setAccessTokenInSession($accessTokenObject);

        return $accessTokenObject;
    }

    /**
     * @return null|object|AccessToken
     */
    public function getNewAccessToken()
    {
        if (!isset($this->sessionContainer->refresh_token))
            return null;

        $accessTokenObject = $this->getAccessTokenResponse('refresh_token', $this->sessionContainer->refresh_token);
        $this->setAccessTokenInSession($accessTokenObject);

        return $accessTokenObject;
    }

    /**
     * @return bool
     */
    public function isAccessTokenExpired()
    {
        return isset($this->sessionContainer->access_token);
    }

    /**
     * @param string $grantType
     * @param string $param
     * @return object|AccessToken
     */
    private function getAccessTokenResponse($grantType, $param)
    {
        $client = new Client();

        $request = new Request();
        $request->setUri($this->tokenUrl);
        $request->setMethod(Request::METHOD_POST);
        $request->getPost()->set('client_id', $this->clientId);
        $request->getPost()->set('client_secret', $this->clientSecret);

        if ($grantType == 'authorization_code') {
            $request->getPost()->set('grant_type', 'authorization_code');
            $request->getPost()->set('code', $param);
        } elseif ($grantType == 'refresh_token') {
            $request->getPost()->set('grant_type', 'refresh_token');
            $request->getPost()->set('refresh_token', $param);
        }

        $request->getPost()->set('redirect_uri', $this->redirectUrl);

        $response = $client->send($request);
        $json = $response->getBody();
        $jsonArray = Json::decode($json, Json::TYPE_ARRAY);

        return (new ClassMethods())->hydrate($jsonArray, new AccessToken());
    }

    private function clearAccessTokenInSession()
    {
        $this->sessionContainer->setExpirationSeconds(0);
    }

    /**
     * @param AccessToken|object $accessTokenObject
     */
    private function setAccessTokenInSession($accessTokenObject)
    {
        $this->sessionContainer->access_token = $accessTokenObject->getAccessToken();
        $this->sessionContainer->token_type = $accessTokenObject->getTokenType();
        $this->sessionContainer->expires_in = $accessTokenObject->getExpiresIn();
        $this->sessionContainer->refresh_token = $accessTokenObject->getRefreshToken();
        $this->sessionContainer->scope = $accessTokenObject->getScope();
        $this->sessionContainer->setExpirationSeconds($accessTokenObject->getExpiresIn(), "access_token");
    }
}