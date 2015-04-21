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

By default, a group key for the form gets added, so fields a `form[name]` instead of `name`. You can create a NamedBuilder with empty name to prevent this.

```php
        
$form = $this->createNamed('', 'form', $user) 
  ->add('name', 'text')
  ->add('email', 'email')
  ->add('save', 'submit', array('label' => 'Save user'));
```

See http://symfony.com/doc/current/book/forms.html for more information.