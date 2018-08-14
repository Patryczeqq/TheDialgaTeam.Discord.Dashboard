<?php

namespace App\Form\Element;

use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Filter\Callback;
use Zend\Filter\StringTrim;

class Csrf extends \Zend\Form\Element\Csrf
{
    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    public function setOptions($options)
    {
        if (isset($options['session_guard'])) {
            $this->guard = $options['session_guard'];
        }
    }

    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
            'validators' => [
                [
                    'name' => Callback::class,
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
        ];
    }
}