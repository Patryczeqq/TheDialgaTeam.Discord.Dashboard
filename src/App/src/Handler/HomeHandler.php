<?php

namespace App\Handler;

use App\Form\HomeHandlerForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandler implements MiddlewareInterface
{
    private $templateRenderer;

    private $nancyGateway;

    public function __construct(TemplateRendererInterface $renderer, NancyGateway $nancyGateway)
    {
        $this->templateRenderer = $renderer;
        $this->nancyGateway = $nancyGateway;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();
        } catch (\Exception $ex) {
            $discordAppTables = array();
        }

        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $form = new HomeHandlerForm($guard, $discordAppTables);

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $token = $this->getToken($session, $guard);

        $form->get('csrf')->setValue($token);

        return new HtmlResponse($this->templateRenderer->render('app::home', [
            'form' => $form
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