<?php

namespace App\Form\Home;

use App\Form\CsrfGuardedForm;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Element\Button;
use Zend\Form\Element\Hidden;

class RefreshGuildsForm extends CsrfGuardedForm
{
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session)
    {
        parent::__construct($guard, $session, 'csrf_refresh_guilds_form');

        $this->add([
            'name' => 'refresh',
            'type' => Button::class,
            'attributes' => [
                'class' => 'btn btn-primary',
                'style' => 'color: white',
                'type' => 'submit'
            ],
            'options' => [
                'label' => 'Refresh Guilds'
            ]
        ]);

        $this->add([
            'name' => 'action',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 'refresh_discord_client_models'
            ]
        ]);
    }
}