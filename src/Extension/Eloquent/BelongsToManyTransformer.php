<?php namespace Barryvdh\Form\Extension\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;

class BelongsToManyTransformer implements DataTransformerInterface
{
    /**
     * Transforms a BelongsToMany relationship into an array.
     *
     * @param mixed $value
     *
     * @return mixed An array of ids
     *
     * @throws TransformationFailedException
     */
    public function transform($value)
    {
        if ($value instanceof BelongsToMany) {
            return $value->pluck($value->getOtherKey())->toArray();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        return null;
    }
}
