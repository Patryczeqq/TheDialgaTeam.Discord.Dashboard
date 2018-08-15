<?php

namespace App\Handler;

use App\Constant\Error;
use App\Constant\Session;
use App\Form\CsrfGuardedForm;
use App\Form\Home\BotSelectionForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class DiscordAppAuthenticationHandler extends BaseFormHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($this->post['action']) && $this->post['action'] == 'discordAppAuthentication') {
            // If this request is from HomeHandler form.
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();

            $botSelectionForm = new BotSelectionForm($this->guard, $this->session, $discordAppTables);
            $botSelectionForm->setData($this->post);

            if (!$botSelectionForm->isValid())
                throw new \Exception($botSelectionForm->getMessages());

            $botSelectionFormData = $botSelectionForm->getData();
            $clientId = $botSelectionFormData['clientId'];

            $this->session = $this->session->regenerate();

            if (!$this->session->isRegenerated())
                throw new \Exception(Error::ERROR_INVALID_SESSION);

            $this->session->set(Session::CLIENT_ID, $clientId);

            $discordOAuth2 = $this->getDiscordOAuth2($clientId);

            return new RedirectResponse($discordOAuth2->getAuthorizationUrl([
                'state' => $this->getCsrfToken(),
                'scope' => ['identify', 'guilds', 'guilds.join']
            ]));
        }

        if (isset($this->get['code']) && isset($this->get['state'])) {
            // If this request is from Discord OAuth2.
            $csrfGuardedForm = new CsrfGuardedForm($this->guard, $this->session, 'state');
            $csrfGuardedForm->setData($this->get);

            if (!$csrfGuardedForm->isValid())
                throw new \Exception($csrfGuardedForm->getMessages());

            $clientId = $this->session->get(Session::CLIENT_ID);

            $discordOAuth2 = $this->getDiscordOAuth2($clientId);

            try {
                $accessToken = $discordOAuth2->getAccessToken('authorization_code', [
                    'code' => $this->get['code']
                ]);

                $this->session->set(Session::DISCORD_OAUTH2_ACCESS_TOKEN, $accessToken->jsonSerialize());

                return new RedirectResponse($this->urlHelper->generate('home'));
            } catch (\Exception $ex) {
                throw new \Exception(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        throw new \Exception(Error::ERROR_INVALID_REQUEST);
    }
}