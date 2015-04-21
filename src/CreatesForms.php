<?php namespace Barryvdh\Form;

trait CreatesForms {

	/**
	 * Create a form with a give type
	 *
	 * @param string|\Symfony\Component\Form\FormTypeInterface  $type
	 * @param mixed  $data
	 * @param array  $options
	 *
	 * @return \Symfony\Component\Form\Form
	 */
	public function createForm($type, $data = null, array $options = array())
	{
		return $this->getFormFactory()->create($type, $data, $options);
	}

	/**
	 * Create a form with a give type
	 *
	 * @param string|null  $name
	 * @param string|\Symfony\Component\Form\FormTypeInterface  $type
	 * @param mixed  $data
	 * @param array  $options
	 *
	 * @return \Symfony\Component\Form\Form
	 */
	public function createNamed($name, $type = 'form', $data = null, array $options = array())
	{
		return $this->getFormFactory()->createNamed($name, $type, $data, $options);
	}


	/**
	 * Create a FormBuilder with default name
	 *
	 * @param  mixed $data
	 * @param  array $options
	 * @return \Symfony\Component\Form\FormBuilder
	 */
	public function createFormBuilder($data = null, array $options = array())
	{
		return $this->getFormFactory()->createBuilder('', $data, $options);
	}

	/**
	 * Create a FormBuilder without name
	 *
	 * @param  string  $name
	 * @param  mixed  $data
	 * @param  array  $options
	 * @return \Symfony\Component\Form\FormBuilder
	 */
	public function createNamedFormBuilder($name = '', $data = null, array $options = array())
	{
		return $this->getFormFactory()->createNamedBuilder($name, 'form', $data, $options);
	}

	/**
	 * Get the FormFactory
	 *
	 * @return \Symfony\Component\Form\FormFactoryInterface
	 */
	protected function getFormFactory(){
		return app('Symfony\Component\Form\FormFactoryInterface');
	}

}
