<?php

namespace App\Handler;

use App\Error\Error;
use App\Form\BotSelectionForm;
use App\Form\CsrfGuardedForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class DiscordAppAuthenticationHandler extends BaseFormHandler
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

        if (isset($this->post['action']) && $this->post['action'] == 'discordAppAuthentication') {
            // If this request is from HomeHandler form.
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();

            if (count($discordAppTables) == 0) {
                return $this->onError(Error::ERROR_NANCY_GATEWAY);
            }

            $form = new BotSelectionForm($this->guard, $this->session, $discordAppTables);
            $form->setData($this->post);

            if (!$form->isValid()) {
                return $this->onError($form->getMessages());
            }

            $data = $form->getData();
            $clientId = $data['clientId'];
            $this->session = $this->session->regenerate();

            if (!$this->session->isRegenerated()) {
                return $this->onError(Error::ERROR_SESSION_GENERATE);
            }

            $this->session->set('clientId', $clientId);

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

            return new RedirectResponse($discordOAuth2->getAuthorizationUrl([
                'state' => $this->getCsrfToken(),
                'scope' => ['identify', 'guilds', 'guilds.join']
            ]));
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
                return $this->onError(Error::ERROR_NANCY_GATEWAY);
            }

            $redirectUri = $this->serverUrlHelper->generate($this->urlHelper->generate('discordAppAuthentication'));

            $discordOAuth2 = new Discord([
                'clientId' => $discordAppTables[0]->getClientId(),
                'clientSecret' => $discordAppTables[0]->getClientSecret(),
                'redirectUri' => $redirectUri
            ]);

            try {
                $token = $discordOAuth2->getAccessToken('authorization_code', [
                    'code' => $this->get['code']
                ]);

                $this->session->set('discord_oauth2', $token->jsonSerialize());

                return new RedirectResponse($this->urlHelper->generate('home'));
            } catch (\Exception $ex) {
                return $this->onError(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        return $this->onError('Malformed or Invalid request have been made.');
    }

    private function onError($error)
    {
        $this->session->clear();

        return new RedirectResponse($this->urlHelper->generate('home', [], [
            'error' => $error
        ]));
    }
}