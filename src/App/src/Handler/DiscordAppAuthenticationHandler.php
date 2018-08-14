<?php

namespace App\Handler;

use App\Form\CsrfGuardedForm;
use App\Form\HomeHandlerForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class DiscordAppAuthenticationHandler extends BaseFormHandler
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    private const ERROR_SESSION_GENERATE = 'Unable to generate a new session. Please try again later.';

    private const ERROR_NANCY_GATEWAY = 'Disconnect from nancy gateway. Please try again later.';

    private const ERROR_DISCORD_GATEWAY = 'Unable to connect to discord api server. Please try again later.';

    public function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway, UrlHelper $urlHelper)
    {
        parent::__construct($templateRenderer, $nancyGateway);

        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->preProcess($request);

        if (isset($this->post['action']) && $this->post['action'] == 'discordAppAuthentication') {
            // If this request is from HomeHandler form.
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();

            if (count($discordAppTables) == 0) {
                return $this->onError(self::ERROR_NANCY_GATEWAY);
            }

            $form = new HomeHandlerForm($this->guard, $this->session, $discordAppTables);
            $form->setData($this->post);

            if (!$form->isValid()) {
                return $this->onError($form->getMessages());
            }

            $data = $form->getData();
            $clientId = $data['clientId'];
            $this->session = $this->session->regenerate();

            if (!$this->session->isRegenerated()) {
                return $this->onError(self::ERROR_SESSION_GENERATE);
            }

            $this->session->set('clientId', $clientId);

            $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);

            if (count($discordAppTables) == 0) {
                return $this->onError(self::ERROR_NANCY_GATEWAY);
            }

            $discordOAuth2 = new Discord([
                'clientId' => $discordAppTables[0]->getClientId(),
                'clientSecret' => $discordAppTables[0]->getClientSecret(),
                'redirectUri' => 'https://' . $request->getUri()->getHost() . $this->urlHelper->generate('discordAppAuthentication')
            ]);

            return new RedirectResponse($discordOAuth2->getAuthorizationUrl(['state' => $this->getCsrfToken()]));
        }

        if (isset($this->get['code']) && isset($this->get['state'])) {
            // If this request is from Discord OAuth2.
            $form = new CsrfGuardedForm($this->guard, $this->session);
            $form->setData(['csrf' => $this->get['state']]);

            if (!$form->isValid()) {
                return $this->onError($form->getMessages());
            }

            $clientId = $this->session->get('clientId');
            $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);

            if (count($discordAppTables) == 0) {
                return $this->onError(self::ERROR_NANCY_GATEWAY);
            }

            $discordOAuth2 = new Discord([
                'clientId' => $discordAppTables[0]->getClientId(),
                'clientSecret' => $discordAppTables[0]->getClientSecret(),
                'redirectUri' => 'https://' . $request->getUri()->getHost() . $this->urlHelper->generate('discordAppAuthentication')
            ]);

            try {
                $token = $discordOAuth2->getAccessToken('authorization_code', [
                    'code' => $this->get['code']
                ]);

                $this->session->set('discord_oauth2', $token->jsonSerialize());

                // Redirect to next route >
            } catch (\Exception $ex) {
                return $this->onError(self::ERROR_DISCORD_GATEWAY);
            }
        }

        if ($this->session->has('discord_oauth2')) {
            // If session exist.
            $accessToken = new AccessToken($this->session->get('discord_oauth2'));

            if ($accessToken->hasExpired()) {
                $clientId = $this->session->get('clientId');
                $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);

                if (count($discordAppTables) == 0) {
                    return $this->onError(self::ERROR_NANCY_GATEWAY);
                }

                $discordOAuth2 = new Discord([
                    'clientId' => $discordAppTables[0]->getClientId(),
                    'clientSecret' => $discordAppTables[0]->getClientSecret(),
                    'redirectUri' => 'https://' . $request->getUri()->getHost() . $this->urlHelper->generate('discordAppAuthentication')
                ]);

                try {
                    $token = $discordOAuth2->getAccessToken('refresh_token', [
                        'refresh_token' => $accessToken->getRefreshToken()
                    ]);

                    $this->session->set('discord_oauth2', $token->jsonSerialize());
                } catch (\Exception $ex) {
                    return $this->onError(self::ERROR_DISCORD_GATEWAY);
                }
            }

            return $handler->handle($request);
        }

        return $this->onError('Malformed or Invalid request have been made.');
    }

    private function onError($error)
    {
        return new RedirectResponse($this->urlHelper->generate('home', [], [
            'error' => $error
        ]));
    }
}