<?php
namespace Barryvdh\Form\Tests;

use Barryvdh\Form\Extension\Validation\ValidationListener;
use Barryvdh\Form\Facade\FormFactory;
use Barryvdh\Form\Tests\Types\ParentFormType;
use Barryvdh\Form\Tests\Types\UserFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ValidationTest extends TestCase
{
    public function testValidForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'name' => 'Barry',
                'email' => 'barry@example.com',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    public function testMissingNameForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'email' => 'barry@example.com',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The name field is required.', $form->getErrors(true)[0]->getMessage());
    }

    public function testInvalidEmailForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'name' => 'Barry',
                'email' => 'foo',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The email must be a valid email address.', $form->getErrors(true)[0]->getMessage());
    }

    public function testEmptyCollectionForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The children field is required.', $form->getErrors(true)[0]->getMessage());
    }

    public function testInvalidCollectionForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'children' => [
                    [
                        'name' => 'Foo',
                        'email' => 'foo@example.com',
                    ],
                    [
                        'name' => 'Bar',
                        'email' => 'bar',
                    ],
                ],
                'emails' => [
                    'foo',
                    'bar@example.com',
                ],
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());

        $this->assertEqualsCanonicalizing([
            'The children.1.email must be a valid email address.',
            'The emails.0 must be a valid email address.',
        ], array_map(function($error) {
            return $error->getMessage();
        }, iterator_to_array($form->getErrors(true))));
    }

    public function testValidCollectionForm()
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'children' => [
                    [
                        'name' => 'Foo',
                        'email' => 'foo@example.com',
                    ],
                    [
                        'name' => 'Bar',
                        'email' => 'bar@example.com',
                    ]
                ],
                'emails' => [
                    'foo@example.com',
                    'bar@example.com',
                ],
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    public function testFindRules() {
        /** @var \Symfony\Component\Form\Form $form */
        $form = FormFactory::create(ParentFormType::class, []);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'children' => [
                    [
                        'name' => 'Foo',
                        'email' => 'foo@example.com',
                    ],
                    [
                        'name' => 'Bar',
                        'email' => 'bar@example.com',
                    ]
                ],
                'emails' => [
                    'foo@example.com',
                    'bar@example.com',
                ],
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $validator = $this->app->make(TestValidationListener::class);
        $rules = $validator->publicFindRules($form);

        $this->assertSame([
            'name' => [
                'required',
                'string',
            ],
            'children' => [
                'required',
                'array',
            ],
            'children.*' => [
                'required',
            ],
            'children.*.name' => [
                'required',
                'string',
            ],
            'children.*.email' => [
                'email',
                'required',
            ],
            'emails' => [
                'min:1',
                'required',
                'array',
            ],
            'emails.*' => [
                'distinct',
                'required',
                'email',
            ],
        ], $rules);
    }

    private function createPostRequest($data)
    {
        return new Request([], $data, [], [], [], [
            'REQUEST_METHOD' => 'POST'
        ]);
    }
}

class TestValidationListener extends ValidationListener
{
    public function publicFindRules(FormInterface $parent)
    {
        return $this->findRules($parent);
    }
}
