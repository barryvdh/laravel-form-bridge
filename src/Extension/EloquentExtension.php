<?php namespace Barryvdh\Form\Extension;

use Barryvdh\Form\Extension\Eloquent\BelongsToManyType;
use Symfony\Component\Form\AbstractExtension;

/**
 * Give access to the session to the Form
 *
 */
class EloquentExtension extends AbstractExtension
{

    /**
     * Registers the types.
     *
     * @return FormTypeInterface[] An array of FormTypeInterface instances
     */
    protected function loadTypes()
    {
        return [
          new BelongsToManyType()
        ];
    }
}
