<?php
namespace Barryvdh\Form\Tests;

use Barryvdh\Form\Facade\FormFactory;
use Barryvdh\Form\Tests\Types\UserFormType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class ValidationTest extends TestCase
{

    public function testValidForm()
    {
        /** @var UserFormType $form */
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
        /** @var UserFormType $form */
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
    }

    public function testInvalidEmailForm()
    {
        /** @var UserFormType $form */
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
    }

    private function createPostRequest($data)
    {
        return new Request([], $data, [], [], [], [
            'REQUEST_METHOD' => 'POST'
        ]);
    }
}
