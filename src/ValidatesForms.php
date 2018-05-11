<?php namespace Barryvdh\Form;

use Illuminate\Http\Request;
use Symfony\Component\Form\Form;
use Illuminate\Foundation\Validation\ValidatesRequests;

trait ValidatesForms
{

    use ValidatesRequests;


    /**
     * Validate the given request with the given rules.
     *
     * @param  \Symfony\Component\Form\Form  $form
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @return void
     */
    public function validateForm(Form $form, Request $request, array $rules, array $messages = array())
    {
        $data = $form->getName()
            ? $request->input($form->getName(), []) + $request->file($form->getName(), [])
            : $request->all();
        $validator = $this->getValidationFactory()->make($data, $rules, $messages);

        $validator->validate();
    }
}
