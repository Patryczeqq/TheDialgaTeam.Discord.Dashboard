<?php

namespace App\Form\Dashboard;

use App\Form\CsrfGuardedForm;
use App\Form\Element\Text;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Element\Button;

class CommandPrefixForm extends CsrfGuardedForm
{
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session)
    {
        parent::__construct($guard, $session, 'commandPrefixCsrf');

        $this->add([
            'name' => 'commandPrefix',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'maxInputLength' => 50
            ]
        ]);

        $this->add([
            'name' => 'updatecommandPrefix',
            'type' => Button::class,
            'attributes' => [
                'class' => 'btn btn-outline-primary'
            ],
            'options' => [
                'label' => 'Update'
            ]
        ]);
    }
}