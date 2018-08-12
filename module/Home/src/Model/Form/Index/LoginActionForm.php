<?php

namespace Home\Model\Form\Index;

use Zend\Form\Element;
use Zend\Form\Form;

class LoginActionForm extends Form
{
    public function __construct()
    {
        parent::__construct();

        $this->add([
            'name' => 'state',
            'type' => Element\Csrf::class,
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);
    }
}