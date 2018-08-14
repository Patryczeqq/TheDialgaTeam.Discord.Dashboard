<?php

namespace App\Middleware;

use App\Error\Error;
use App\Handler\BaseFormHandler;
use App\TheDialgaTeam\Discord\NancyGateway;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class SessionCheckerMiddleware extends BaseFormHandler
{
    /**
     * @var ServerUrlHelper
     */
    private $serverUrlHelper;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway, ServerUrlHelper $serverUrlHelper, UrlHelper $urlHelper)
    {
        parent::__construct($templateRenderer, $nancyGateway);

        $this->serverUrlHelper = $serverUrlHelper;
        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->preProcess($request);

        if ($this->session->has('discord_oauth2')) {
            $accessToken = new AccessToken($this->session->get('discord_oauth2'));

            if ($accessToken->hasExpired()) {
                $clientId = $this->session->get('clientId');
                $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);

                if (count($discordAppTables) == 0) {
                    return $this->onError(Error::ERROR_NANCY_GATEWAY);
                }

                $redirectUri = $this->serverUrlHelper->generate($this->urlHelper->generate('discordAppAuthentication'));

                $discordOAuth2 = new Discord([
                    'clientId' => $discordAppTables[0]->getClientId(),
                    'clientSecret' => $discordAppTables[0]->getClientSecret(),
                    'redirectUri' => $redirectUri
                ]);

                try {
                    $token = $discordOAuth2->getAccessToken('refresh_token', [
                        'refresh_token' => $accessToken->getRefreshToken()
                    ]);

                    $this->session->set('discord_oauth2', $token->jsonSerialize());
                } catch (\Exception $ex) {
                    return $this->onError(Error::ERROR_DISCORD_GATEWAY);
                }
            }
        }

        return $handler->handle($request);
    }

    private function onError($error)
    {
        $this->session->clear();

        return new RedirectResponse($this->urlHelper->generate('home', [], [
            'error' => $error
        ]));
    }
}