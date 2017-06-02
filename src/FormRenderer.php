<?php

namespace Barryvdh\Form;

use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;

/**
 * FormRenderer to use outside Twig Templates, based on hostnet/form-twig-bridge (Copyright Hostnetbv)
 * @see https://github.com/hostnet/form-twig-bridge/blob/master/src/PhpRenderer.php
 * @author Nico Schoenmaker <nschoenmaker@hostnet.nl>
 */
class FormRenderer
{
    /** @var FormRendererInterface  */
    protected $renderer;

    public function __construct(FormRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Renders the form
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function form(FormView $view, array $variables = [])
    {
        return $this->renderer->renderBlock($view, 'form', $variables);
    }

    /**
     * Renders the opening form tag of the form.
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function start(FormView $view, array $variables = [])
    {
        return $this->renderer->renderBlock($view, 'form_start', $variables);
    }

    /**
     * Renders the closing form tag of the form.
     *
     * @param FormView $view
     * @param array $variables
     * @return string
     */
    public function end(FormView $view, array $variables = [])
    {
        return $this->renderer->renderBlock($view, 'form_end', $variables);
    }

    /**
     * Renders the form widget
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function widget(FormView $view, array $variables = [])
    {
        return $this->renderer->searchAndRenderBlock($view, 'widget', $variables);
    }

    /**
     * Renders only the errors of the passed FormView.
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function errors(FormView $view, array $variables = [])
    {
        return $this->renderer->searchAndRenderBlock($view, 'errors', $variables);
    }

    /**
     * Renders the label of a field.
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function label(FormView $view, $label, $variables = [])
    {
        if (!isset($variables['label'])) {
            $variables['label'] = $label;
        }
        return $this->renderer->searchAndRenderBlock($view, 'label', $variables);
    }

    /**
     * Renders a row for a field.
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function row(FormView $view, array $variables = [])
    {
        return $this->renderer->searchAndRenderBlock($view, 'row', $variables);
    }

    /**
     * Renders all unrendered children of the given form.
     *
     * @param  FormView $view
     * @param  array $variables
     * @return string
     */
    public function rest(FormView $view, array $variables = [])
    {
        return $this->renderer->searchAndRenderBlock($view, 'rest', $variables);
    }
}
