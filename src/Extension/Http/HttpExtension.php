<?php namespace Barryvdh\Form\Extension\Http;

use Symfony\Component\Form\AbstractExtension;

class HttpExtension extends AbstractExtension
{
    protected function loadTypeExtensions(): array
    {
        return array(
            new FormTypeHttpExtension(),
        );
    }
}
