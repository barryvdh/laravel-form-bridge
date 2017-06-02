<?php

use Barryvdh\Form\Facade\FormFactory;
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

        $app['router']->get('create', function () {
            $user = [];

            $form = FormFactory::create(FormType::class, $user)
                ->add('name', TextType::class)
                ->add('email', EmailType::class, [
                    'rules' => 'unique:users,email',
                ])
                ->add('save', SubmitType::class, ['label' => 'Save user']);

            $form->handleRequest();

            if ($form->isSubmitted() && $form->isValid()) {
                return 'saved';
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

        $this->assertContains('<form name="form" method="post">', $crawler->getContent());
    }
}
