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
     * @param string $csrfKey
     */
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session, string $csrfKey = 'csrf')
    {
        parent::__construct();

        $this->guard = $guard;
        $this->session = $session;

        $this->add([
            'name' => $csrfKey,
            'type' => Csrf::class,
            'attributes' => [
                'value' => $this->getCsrfToken($csrfKey)
            ],
            'options' => [
                'session_guard' => $guard
            ]
        ]);
    }

    private function getCsrfToken(string $csrfKey)
    {
        if (!$this->session->has($csrfKey)) {
            return $this->guard->generateToken($csrfKey);
        }

        return $this->session->get($csrfKey);
    }
}