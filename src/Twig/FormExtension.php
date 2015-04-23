<?php namespace Barryvdh\Form\Twig;

use Symfony\Bridge\Twig\Extension\FormExtension as SymfonyFormExtension;

class FormExtension extends SymfonyFormExtension
{
	/**
	 *
	 * {@inheritdoc}
	 */
	public function renderCsrfToken($intention = null)
	{
		if (is_null($intention)) {
			return csrf_token();
		}

		return parent::renderCsrfToken($intention);
	}
}

