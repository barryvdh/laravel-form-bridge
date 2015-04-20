<?php namespace Barryvdh\Form;

use Symfony\Component\Form\FormFactoryInterface;

class Builder {

    /**
     * The FormFactoryBuilder instance
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

	public function build($data = [], $options = [])
    {
        return $this->formFactory->createNamedBuilder(null, 'form', $data, $options);
    }
}
