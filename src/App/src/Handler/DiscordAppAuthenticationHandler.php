<?php

namespace App\Handler;

use App\Form\DiscordAppAuthenticationHandlerForm;
use App\Form\HomeHandlerForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;

class DiscordAppAuthenticationHandler implements MiddlewareInterface
{
    private $urlHelper;

    private $templateRenderer;

    private $nancyGateway;

    public function __construct(UrlHelper $urlHelper, TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway)
    {
        $this->urlHelper = $urlHelper;
        $this->templateRenderer = $templateRenderer;
        $this->nancyGateway = $nancyGateway;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $get = $request->getQueryParams();
        $post = $request->getParsedBody();

        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);

        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if (isset($post['action']) && $post['action'] == 'discordAppAuthentication') {
            try {
                $discordAppTables = $this->nancyGateway->getDiscordAppTable();
            } catch (\Exception $ex) {
                $discordAppTables = array();
            }

            $form = new HomeHandlerForm($guard, $discordAppTables);
            $form->setValidationGroup('clientId', 'csrf');
            $form->setData($post);

            if (!$form->isValid()) {
                $session->unset('__csrf');

                return new RedirectResponse($this->urlHelper->generate('home', [], [
                    'error' => $form->getMessages()
                ]));
            }

            $data = $form->getData();
            $clientId = $data['clientId'];
            $session = $session->regenerate();

            if (!$session->isRegenerated()) {
                return new RedirectResponse($this->urlHelper->generate('home', [], [
                    'error' => 'Unable to create a session. Please try again later.'
                ]));
            }

            $session->set('clientId', $clientId);

            foreach ($discordAppTables as $discordAppTable) {
                if ($discordAppTable->getClientId() != $clientId) {
                    continue;
                }

                $oauth2 = new Discord([
                    'clientId' => $discordAppTable->getClientId(),
                    'clientSecret' => $discordAppTable->getClientSecret(),
                    'redirectUri' => $this->urlHelper->generate('discordAppAuthentication')
                ]);

                $token = $this->getToken($session, $guard);

                return new RedirectResponse($oauth2->getAuthorizationUrl(['state' => $token]));
            }
        }

        if (isset($get['code']) && isset($get['state'])) {
            $form = new DiscordAppAuthenticationHandlerForm($guard);
            $form->setData(['csrf' => $get['state']]);

            if (!$form->isValid()) {
                $session->unset('__csrf');

                return new RedirectResponse($this->urlHelper->generate('home', [], [
                    'error' => $form->getMessages()
                ]));
            }

            $clientId = $session->get('clientId');

            try {
                $discordAppTables = $this->nancyGateway->getDiscordAppTable($clientId);
            } catch (\Exception $ex) {
                $discordAppTables = array();
            }

            foreach ($discordAppTables as $discordAppTable) {
                if ($discordAppTable->getClientId() != $clientId) {
                    continue;
                }

                $oauth2 = new Discord([
                    'clientId' => $discordAppTable->getClientId(),
                    'clientSecret' => $discordAppTable->getClientSecret(),
                    'redirectUri' => $this->urlHelper->generate('discordAppAuthentication')
                ]);

                $token = $oauth2->getAccessToken('authorization_code', [
                    'code' => $get['code']
                ]);

                $session->set('discord_oauth2', $token->jsonSerialize());

                // Do something? Route to dashboard :)
            }
        }

        return new RedirectResponse($this->urlHelper->generate('home', [], [
            'error' => 'Malformed or Invalid request have been made.'
        ]));
    }

    private function getToken(SessionInterface $session, SessionCsrfGuard $guard)
    {
        if (!$session->has('__csrf')) {
            return $guard->generateToken();
        }

        return $session->get('__csrf');
    }
}