<?php

namespace App\Form;

use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class DiscordAppAuthenticationHandlerForm
 * @package App\Form
 */
class DiscordAppAuthenticationHandlerForm extends Form
{
    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    /**
     * DiscordAppAuthenticationHandlerForm constructor.
     * @param SessionCsrfGuard $guard
     */
    public function __construct(SessionCsrfGuard $guard)
    {
        parent::__construct();

        $this->guard = $guard;

        $this->add([
            'name' => 'csrf',
            'type' => Element\Hidden::class
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'csrf',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => function ($value) {
                                return $this->guard->validateToken($value);
                            },
                            'messages' => [
                                'callbackValue' => 'The form submitted did not originate from the expected site'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}