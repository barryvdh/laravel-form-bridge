<?php namespace Barryvdh\Form\Twig;

use Symfony\Bridge\Twig\Extension\FormExtension as SymfonyFormExtension;

class FormExtension extends SymfonyFormExtension
{
	/**
	 * If the $intention is null, use the Laravel CSRF token.
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
