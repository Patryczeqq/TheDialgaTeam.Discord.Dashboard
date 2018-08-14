<?php

namespace App\Form;

use App\Form\Element\Csrf;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Form;

/**
 * Class CsrfGuardedForm
 * @package App\Form
 */
class CsrfGuardedForm extends Form
{
    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * CsrfGuardedForm constructor.
     * @param SessionCsrfGuard $guard
     * @param SessionInterface $session
     */
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session)
    {
        parent::__construct();

        $this->guard = $guard;
        $this->session = $session;

        $this->add([
            'name' => 'csrf',
            'type' => Csrf::class,
            'attributes' => [
                'value' => $this->getCsrfToken()
            ],
            'options' => [
                'session_guard' => $guard
            ]
        ]);
    }

    private function getCsrfToken()
    {
        if (!$this->session->has('__csrf')) {
            return $this->guard->generateToken();
        }

        return $this->session->get('__csrf');
    }
}