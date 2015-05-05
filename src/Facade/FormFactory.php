<?php namespace Barryvdh\Form\Facade;

use Illuminate\Support\Facades\Facade;

class FormFactory extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'form.factory';
    }
}
