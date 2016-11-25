<?php namespace Barryvdh\Form\Facade;

use Barryvdh\Form\FormRenderer as RealFormRenderer;
use Illuminate\Support\Facades\Facade;

class FormRenderer extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return RealFormRenderer::class;
    }
}
