<?php namespace Barryvdh\Form\Extension\Http;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;

class HttpRequestHandler extends HttpFoundationRequestHandler implements RequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(FormInterface $form, $request = null)
    {
        if ($request === null) {
            $request = app('request');
        }

        parent::handleRequest($form, $request);
    }
}
