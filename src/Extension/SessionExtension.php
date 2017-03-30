<?php namespace Barryvdh\Form\Extension;

use Symfony\Component\Form\AbstractExtension;

/**
 * Give access to the session to the Form
 *
 */
class SessionExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadTypeExtensions()
    {
        return array(
            new Session\CsrfTypeExtension,
            new Session\SessionTypeExtension,
        );
    }
}
