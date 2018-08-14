<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
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

    protected function preProcess(ServerRequestInterface $request)
    {
        $this->guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $this->session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $this->get = $request->getQueryParams();
        $this->post = $request->getParsedBody();
    }

    protected function getCsrfToken()
    {
        if (!$this->session->has('__csrf')) {
            return $this->guard->generateToken();
        }

        return $this->session->get('__csrf');
    }
}