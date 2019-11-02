<?php

return array(
   
    /*
    |--------------------------------------------------------------------------
    | Form theme
    |--------------------------------------------------------------------------
    |
    | Set the template to render the form. Possible values:
    | - form_div_layout.html.twig
    | - form_table_layout.html.twig
    | - bootstrap_3_layout.html.twig
    | - bootstrap_3_horizontal_layout.html.twig
    | - bootstrap_4_layout.html.twig
    | - bootstrap_4_horizontal_layout.html.twig
    | - foundation_5_layout.html.twig
    | - (your own template..)
    | 
    | See http://symfony.com/doc/current/cookbook/form/form_customization.html#what-are-form-themes
    */
    'theme' => 'bootstrap_4_layout.html.twig',

    /*
    |--------------------------------------------------------------------------
    | Form template directories
    |--------------------------------------------------------------------------
    |
    | Add custom template directories to render the form.
    |
    | See http://symfony.com/doc/current/form/form_customization.html
    */
    'template_directories' => [
        // eg: resource_path('views/form')
    ],

    'defaults' => [
        'required' => true,
    ]
);
