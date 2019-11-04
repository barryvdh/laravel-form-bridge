<?php
namespace Barryvdh\Form\Tests;

use Barryvdh\Form\Facade\FormFactory;
use Barryvdh\Form\Tests\Types\UserFormType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BladeTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->any('create', function () {
            $user = [];

            $form = FormFactory::create(UserFormType::class, $user)
                ->add('save', SubmitType::class, ['label' => 'Save user']);

            $form->handleRequest();

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    return 'valid';
                }

                return 'invalid';
            }

            return view('forms::create', compact('form'));
        });
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testInlineForm()
    {
        $crawler = $this->call('GET', 'create');

        $this->assertContains('<form name="user_form" method="post">', $crawler->getContent());
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testPostFormInvalid()
    {
        $crawler = $this->call('POST', 'create', [
            'user_form' => ['save' => true]
        ]);

        $this->assertEquals('invalid', $crawler->getContent());
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testPostForm()
    {
        $crawler = $this->call('POST', 'create', [
            'user_form' => [
                'name' => 'Barry',
                'email' => 'barryvdh@gmail.com',
                'save' => true
            ]
        ]);

        $this->assertEquals('valid', $crawler->getContent());
    }
}
