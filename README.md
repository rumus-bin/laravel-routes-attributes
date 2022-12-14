# Package for using PHP Attributes as route mapping in Laravel applications.

## The package requires a minimum version of PHP 8.
This package was inspired by the work of the [Spatie] team, namely their package:
[laravel-route-attributes]
Please use the above package for your production environments.

## License
MIT

### Responsibility:
The author of the package does not bear any responsibility for possible losses associated with the use of this package 
in a production environment. 
The author also does not accept any comments about the performance or inoperability of this 
package. You can freely use all the program code of this package for your own purposes, but only 
under your own responsibility. Support, security and updating of this package is not guaranteed in
any way by the author.

After install publish config with artisan command:

`php artisan vendor:publish --provider="RumusBin\AttributesRouter\AttributesRouterProvider" --tag="config"`
this will publish config class:
`
return [
/*
     *  Automatic registration of routes will only happen if this setting is `true`
*/
'enabled' => true,

    /*
     * Controllers in these directories that have routing attributes
     * will automatically be registered.
     *
     * Optionally, you can specify group configuration by using key/values
     */
    'directories' => [
        app_path('Http/Controllers'),

        app_path('Http/Controllers/Web') => [
            'middleware' => ['web']
        ],
        
        app_path('Http/Controllers/Api') => [
            'prefix' => 'api',
            'middleware' => 'api'
        ],
    ],
];
`

[Spatie]: <https://github.com/spatie>
[laravel-route-attributes]: <https://github.com/spatie/laravel-route-attributes>