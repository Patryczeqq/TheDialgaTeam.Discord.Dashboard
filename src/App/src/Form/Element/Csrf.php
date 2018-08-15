<?php

namespace App\Form\Element;

use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Filter\StringTrim;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;

class Csrf extends Element implements InputProviderInterface
{
    protected $attributes = [
        'type' => 'hidden',
    ];

    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    public function setOptions($options)
    {
        parent::setOptions($options);

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
                    'name' => 'callback',
                    'options' => [
                        'callback' => function ($value) {
                            return $this->guard->validateToken($value, $this->getName());
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