<?php

namespace App\Handler;

use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class LogoutHandler extends BaseFormHandler
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway, UrlHelper $urlHelper)
    {
        parent::__construct($templateRenderer, $nancyGateway);

        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->session->clear();
        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}