<?php

namespace App\Form\Element;

use Zend\Filter\StringTrim;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\StringLength;

class Text extends Element implements InputProviderInterface
{
    protected $attributes = [
        'type' => 'text'
    ];

    protected $options = [
        'minInputLength' => 0,
        'maxInputLength' => null
    ];

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
                    'name' => StringLength::class,
                    'options' => [
                        'min' => $this->getOption('minInputLength'),
                        'max' => $this->getOption('maxInputLength')
                    ]
                ]
            ]
        ];
    }
}