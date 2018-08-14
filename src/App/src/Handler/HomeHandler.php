<?php

namespace App\Handler;

use App\Form\HomeHandlerForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandler extends BaseFormHandler
{
    public function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway)
    {
        parent::__construct($templateRenderer, $nancyGateway);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->preProcess($request);

        $form = new HomeHandlerForm($this->guard, $this->session, $this->nancyGateway->getDiscordAppTable());

        if (isset($this->get['error'])) {
            if (is_array($this->get['error'])) {
                $error = join('\n', $this->get['error']);
            } else {
                $error = $this->get['error'];
            }
        } else {
            $error = '';
        }

        return new HtmlResponse($this->templateRenderer->render('app::home', [
            'form' => $form,
            'error' => $error
        ]));
    }
}