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

ValidatesForms: Adds a validation method, similar to the ValidatesRequests trait:
`$this->validateForm($form, $request, $rules)`

CreatesForms: Create a Form or FormBuilder:
 - createForm($type, $data, $options) -> Form for a type (`form` or a Type class)
 - createNamed($name, $type, $data, $options) -> Form with a given name
 - createFormBuilder($data, $options) -> FormBuilder with an empty name
 - createNamedFormBuilder($name, $data, $options) -> FormBuilder with a given name

 
```php
use Barryvdh\Form\ValidatesForms;
use Barryvdh\Form\CreatesForms;

class UserController extends Controller{

    use ValidatesForms, CreatesForms;
    
    public function anyIndex(Request $request)
	{
		$user = User::first();

        $form = $this->createFormBuilder($user)
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('save', 'submit', array('label' => 'Save user'))
            ->getForm();

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
        
$form = $this->createNamed('user', 'form', $user) 
  ->add('name', 'text')
  ->add('email', 'email')
  ->add('save', 'submit', array('label' => 'Save user'));
```

See http://symfony.com/doc/current/book/forms.html for more information.

## Uploading Files

You can use the `file` type in the FormBuilder, and use the magic `getFile()` and `setFile()` method on your Model or mark it as not mapped, so you can handle it yourself. See http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html

```php
Class User extends Model {

	/** @var UploadedFile  */
	private $file;
	
	public function getFile()
	{
		return $this->file;
	}
	
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;
	}
	
	public function upload()
	{
		    // the file property can be empty if the field is not required
		    if (null === $this->getFile()) {
		        return;
		    }
		
		    // use the original file name here but you should
		    // sanitize it at least to avoid any security issues
		
		    // move takes the target directory and then the
		    // target filename to move to
		    $this->getFile()->move(
		        $this->getUploadRootDir(),
		        $this->getFile()->getClientOriginalName()
		    );
		
		    // set the path property to the filename where you've saved the file
		    $this->path = $this->getFile()->getClientOriginalName();
		
		    // clean up the file property as you won't need it anymore
		    $this->file = null;
	}
}
```

```php
$user = User::first();

$form = $this->createFormBuilder($user)
    ->add('name', 'text')
    ->add('file', 'file')
    ->add('save', 'submit', array('label' => 'Save user'))
    ->getForm();
    
 $form->handleRequest($request);
 
 if ($form->isValid()) {
  	$user->upload();
        $user->save();
}
