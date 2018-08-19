<?php

namespace App\Handler;

use App\Constant\Error;
use App\Constant\Session;
use App\TheDialgaTeam\Discord\NancyGateway;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RestCord\DiscordClient;
use RestCord\Model\Guild\Guild;
use RestCord\Model\User\User;
use Wohali\OAuth2\Client\Provider\Discord;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Form\Form;
use Zend\Json\Json;

/**
 * Class BaseFormHandler
 * @package App\Handler
 */
abstract class BaseFormHandler implements MiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    protected $templateRenderer;

    /**
     * @var NancyGateway
     */
    protected $nancyGateway;

    /**
     * @var ServerUrlHelper
     */
    protected $serverUrlHelper;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var SessionCsrfGuard
     */
    protected $guard;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var array
     */
    protected $get;

    /**
     * @var array
     */
    protected $post;

    /**
     * @var Discord
     */
    private $discordOAuth2;

    /**
     * @var DiscordClient
     */
    private $discordClient;

    /**
     * BaseFormHandler constructor.
     * @param array $options
     */
    public function __construct($options)
    {
        $this->templateRenderer = $options['templateRenderer'];
        $this->nancyGateway = $options['nancyGateway'];
        $this->serverUrlHelper = $options['serverUrlHelper'];
        $this->urlHelper = $options['urlHelper'];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $this->session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $this->get = $request->getQueryParams();
        $this->post = $request->getParsedBody();

        try {
            return $this->onProcess($request, $handler);
        } catch (\Exception $ex) {
            $this->session->clear();

            if (get_class($this) != HomeHandler::class) {
                return new RedirectResponse($this->urlHelper->generate('home', [], [
                    'error' => $ex->getMessage()
                ]));
            }
        }
    }

    protected abstract function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    /**
     * @param string $clientId
     * @return Discord
     * @throws \Exception
     */
    protected function getDiscordOAuth2($clientId)
    {
        if (isset($this->discordOAuth2))
            return $this->discordOAuth2;

        try {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);
        } catch (\Exception $ex) {
            throw new \Exception(Error::ERROR_NANCY_GATEWAY);
        }

        $this->discordOAuth2 = new Discord([
            'clientId' => $discordAppTables[0]->getClientId(),
            'clientSecret' => $discordAppTables[0]->getClientSecret(),
            'redirectUri' => $this->serverUrlHelper->generate($this->urlHelper->generate('discordAppAuthentication'))
        ]);

        return $this->discordOAuth2;
    }

    /**
     * @return DiscordClient
     * @throws \Exception
     */
    protected function getDiscordClient()
    {
        if (isset($this->discordClient))
            return $this->discordClient;

        if (!$this->session->has(Session::DISCORD_OAUTH2_ACCESS_TOKEN))
            throw new \Exception(Error::ERROR_INVALID_SESSION);

        $accessToken = new AccessToken($this->session->get(Session::DISCORD_OAUTH2_ACCESS_TOKEN));

        if ($accessToken->hasExpired()) {
            if (!$this->session->has(Session::CLIENT_ID))
                throw new \Exception(Error::ERROR_INVALID_SESSION);

            $clientId = $this->session->get(Session::CLIENT_ID);
            $discordOAuth2 = $this->getDiscordOAuth2($clientId);

            try {
                $accessToken = $discordOAuth2->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken()
                ]);

                $this->session->set(Session::DISCORD_OAUTH2_ACCESS_TOKEN, $accessToken->jsonSerialize());
            } catch (\Exception $ex) {
                throw new \Exception(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        $this->discordClient = new DiscordClient([
            'tokenType' => 'OAuth',
            'token' => $accessToken->getToken(),
        ]);

        return $this->discordClient;
    }

    /**
     * @param string $csrfKey
     * @return string
     */
    protected function getCsrfToken($csrfKey = 'csrf')
    {
        if (!$this->session->has($csrfKey))
            return $this->guard->generateToken();

        return $this->session->get($csrfKey);
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    protected function getFormError($form)
    {
        $error = array();

        foreach ($form->getMessages() as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $error[] = sprintf('%s: %s', $key2, $value2);
            }
        }

        throw new \Exception(join(' ', $error));
    }

    /**
     * @param bool $getFromCache
     * @return User
     * @throws \Exception
     */
    protected function getCurrentUser($getFromCache = true)
    {
        if ($getFromCache && $this->session->has(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER)) {
            $user = new User($this->session->get(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER));
        } else {
            try {
                $user = $this->getDiscordClient()->user->getCurrentUser(array());
                $this->session->set(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER, Json::encode($user));
            } catch (\Exception $ex) {
                throw new \Exception(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        return $user;
    }

    /**
     * @param bool $getFromCache
     * @return array|Guild[]
     * @throws \Exception
     */
    protected function getCurrentUserGuilds($getFromCache = true)
    {
        $guilds = array();

        if ($this->session->has(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER_GUILDS)) {
            $guilds_array = Json::decode($this->session->get(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER_GUILDS), Json::TYPE_ARRAY);

            foreach ($guilds_array as $key => $value) {
                $guilds[] = new Guild($value);
            }
        } else {
            try {
                $guilds = $this->getDiscordClient()->user->getCurrentUserGuilds(array());
                $guilds_json = array();

                foreach ($guilds as $guild) {
                    $guilds_json[] = Json::encode($guild);
                }

                $this->session->set(Session::DISCORD_CLIENT_USER_GET_CURRENT_USER_GUILDS, sprintf('[%s]', join(',', $guilds_json)));
            } catch (\Exception $ex) {
                throw new \Exception(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        return $guilds;
    }
}