## Laravel Form Bridge

> _Note:_ This package is still work-in-progress. Don't use this in production yet.

For Laravel 5.0, requires a configured [TwigBridge](https://github.com/rcrowe/TwigBridge) or similar laravel package that registers `app('twig')`.

Features:
 - [x] Set up basic extensions 
 - [x] Pre-set old input
 - [x] Add validation errors
 - [ ] Add plain/blade rendering?
 - [ ] Pre-set HTML5 validation attributes based on rules?
 - [ ] Translate field names
 - [ ] Test it 

### Install
Composer require `"barryvdh/laravel-form-bridge": "0.1.x@dev"`, add `Barryvdh\Form\ServiceProvider` to you ServiceProviders.

### Basic example

You can use the FormFactory to create a form. You can supply a Model as data, so it will fill the values.

You can use `$form->handleRequest($request);` to update the values in the user, or you can just use `$request` object or `Input` facade like usual.
However, by default, the form is grouped under a `form` key, so you have to use `$request->get('form')` to get the form values.
Or you can create a Named form, with an empty name.

If you need to set more options, use the `createBuilder` function instead of `create`, to be able to use `setAction()` etc. You need to call `->getForm()`  to get the actual form instance again.

```php
Route::any('form', function(\Illuminate\Http\Request $request){
    $user = App\User::first();
    
    $form = app('form.factory')->create('form', $user)
        ->add('name', 'text')
        ->add('email', 'email')
        ->add('save', 'submit', array('label' => 'Save user'));

    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        $v = Validator::make($request->get($form->getName()), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($v->fails()) {
            return redirect()->back()->withInput()->withErrors($v->errors());
        }
        
        // Save the user with the new mapped data
        $user->save();
    }

    return view('form', ['form' => $form->createView()]);
});
```

Use the following in your twig templates to render the view:

```twig
{{ form_start(form) }}
{{ form_widget(form) }}
{{ form_end(form) }}
```

See http://symfony.com/doc/current/book/forms.html#form-rendering-template for more options.

## Traits

To make it easier to use in a Controller, you can use 2 traits:

```php
use Barryvdh\Form\ValidatesForms;
use Barryvdh\Form\CreatesForms;

class UserController extends Controller{

    use ValidatesForms, CreatesForms;
    
    public function anyIndex(Request $request)
	{
		$user = User::first();

        $form = $this->createForm('form', $user)
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('save', 'submit', array('label' => 'Save user'));

		$form->handleRequest($request);

		if ($form->isSubmitted()) {

			$this->validateForm($form, $request, [
			  'name' => 'required',
			  'email' => 'required|email',
			]);

			$user->save();
		}

		return view('user', ['form' => $form->createView()]);
	}    
}
```

Creating a named form:

```php
        
$form = $this->createNamed('', 'form', $user) 
  ->add('name', 'text')
  ->add('email', 'email')
  ->add('save', 'submit', array('label' => 'Save user'));
```

See http://symfony.com/doc/current/book/forms.html for more information.