<?php namespace Barryvdh\Form\Extension;

use Illuminate\Session\SessionManager;
use Symfony\Component\Form\AbstractExtension;

/**
 * Give access to the session to the Form
 *
 */
class SessionExtension extends AbstractExtension
{
    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param  SessionManager  $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->session = $sessionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypeExtensions()
    {
        return array(
            new Type\CsrfTypeExtension($this->session),
        );
    }
}
