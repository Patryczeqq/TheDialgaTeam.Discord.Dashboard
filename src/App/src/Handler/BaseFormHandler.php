<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RestCord\DiscordClient;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;

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
     * @var DiscordClient
     */
    protected $discordClient;

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
     * BaseFormHandler constructor.
     * @param TemplateRendererInterface $templateRenderer
     * @param NancyGateway $nancyGateway
     */
    protected function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway)
    {
        $this->templateRenderer = $templateRenderer;
        $this->nancyGateway = $nancyGateway;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    protected function preProcess(ServerRequestInterface $request)
    {
        $this->guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $this->session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $this->get = $request->getQueryParams();
        $this->post = $request->getParsedBody();

        if ($this->session->has('discord_oauth2')) {
            $accessToken = new AccessToken($this->session->get('discord_oauth2'));

            $this->discordClient = new DiscordClient([
                'tokenType' => 'OAuth',
                'token' => $accessToken->getToken(),
            ]);
        }
    }

    protected function getCsrfToken()
    {
        if (!$this->session->has('__csrf')) {
            return $this->guard->generateToken();
        }

        return $this->session->get('__csrf');
    }
}