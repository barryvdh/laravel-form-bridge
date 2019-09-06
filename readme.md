## Laravel Form Bridge

See http://symfony.com/doc/current/forms.html

Laravel integration:
 - Pre-set old input
 - Add validation errors
 - Translate field names

### Install
 - `composer require barryvdh/laravel-form-bridge`
 - Add `Barryvdh\Form\ServiceProvider::class,` to you ServiceProviders.
 - (optional) Add `'FormFactory' => Barryvdh\Form\Facade\FormFactory::class,` to your Facades.
 - (optional) Add `'FormRenderer' => Barryvdh\Form\Facade\FormRenderer::class,` to your Facades.

### Basic example

You can use the FormFactory to create a form. You can supply a Model as data, so it will fill the values.

You can use `$form->handleRequest($request);` to update the values in the user, or you can just use `$request` object or `Input` facade like usual.
However, by default, the form is grouped under a `form` key, so you have to use `$request->get('form')` to get the form values.
Or you can create a Named form, with an empty name.

If you need to set more options, use the `createBuilder` function instead of `create`, to be able to use `setAction()` etc. You need to call `->getForm()`  to get the actual form instance again.

```php
use FormFactory;
use Illuminate\Http\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

Route::any('create', function()
{
    $user = App\User::first();
    
    $form = FormFactory::create(FormType::class, $user)
        ->add('name', TextType::class)
        ->add('email', EmailType::class, [
            'rules' => 'unique:users,email',
        ])
        ->add('save', SubmitType::class, ['label' => 'Save user']);

    $form->handleRequest();

    if ($form->isSubmitted() && $form->isValid()) {
        // Save the user with the new mapped data
        $user->save();
        
        return redirect('home')->withStatus('User saved!');
    }

    return view('user.create', compact('form'));
});
```

Use the following in your Blade templates:

```php
@formStart($form)
@formWidget($form)
@formEnd($form)
```

Other directives are: @form, @formLabel, @formErrors, @formRest and @formRow

```php
@form($form)
```

```php
@formStart($form)

<h2>Name</h2>
@formLabel($form['name'], 'Your name')
@formWidget($form['name'], ['attr' => ['class' => 'name-input']])

<h2>Rest</h2>
@formRest($form)

@formEnd($form)
```

Or use the following in your Twig templates to render the view:

```twig
{{ formStart(form) }}
{{ formWidget(form) }}
{{ formEnd(form) }}
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends Controller{

    use ValidatesForms, CreatesForms;
    
    public function anyIndex(Request $request)
    {
        $user = User::first();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, ['label' => 'Save user'])
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
use Symfony\Component\Form\Extension\Core\Type\FormType;

$form = $this->createNamed('user', FormType::class, $user) 
    ->add('name', TextType::class)
    ->add('email', EmailType::class)
    ->add('save', SubmitType::class, ['label' => 'Save user']);
```

See http://symfony.com/doc/current/book/forms.html for more information.
## BelongsToMany relations

BelongsToMany behaves differently, because it isn't an actual attribute on your model. Instead, we can set the `mapped` option to `false` and sync it manually.

```php
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

$builder->add('users', ChoiceType::class, [
    'choices' => \App\User::pluck('name', 'id'),
    'multiple' => true,
    'mapped' => false,
    'expanded' => true, // true=checkboxes, false=multi select
]);
```

```php
$form->handleRequest($request);
if ($form->isSubmitted()) {
    $this->validate($request, $rules);

    $item->save();
    $item->users()->sync($form->get('users')->getData());

    return redirect()->back();
}
```
See for more options the [choice type documentation](http://symfony.com/doc/current/reference/forms/types/choice.html).

> Note: The BelongsToManyType is deprecated in favor of the ChoiceType from Symfony.

## Translation labels

If you want to translate your labels automatically, just pass the translation key as the `label` attribute. It will run throught Twig's `trans` filter.

```php
->add('name', TextType::class, ['label' => 'fields.name'])
```

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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

$user = User::first();

$form = $this->createFormBuilder($user)
    ->add('name', TextType::class)
    ->add('file', FileType::class)
    ->add('save', SubmitType::class, ['label' => 'Save user'])
    ->getForm();
    
$form->handleRequest($request);

if ($form->isValid()) {
    $user->upload();
    $user->save();
}
```

## Extending

You can extend some of the arrays in the ServiceProvider, eg. to add Types, add this to the `register()` method in your own ServiceProvider:

```php
$this->app->extend('form.types', function($types, $app){
    $types[] = new CustomType();
    return $types;
});
```
