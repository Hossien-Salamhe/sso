##sso
sso for accounts.engenesis.com

###
[![PHP Version Require](http://poser.pugx.org/zaman-tech/sso/require/php)](https://packagist.org/packages/zaman-tech/sso)
[![Total Downloads](http://poser.pugx.org/zaman-tech/sso/downloads)](https://packagist.org/packages/zaman-tech/sso)
[![License](http://poser.pugx.org/zaman-tech/sso/license)](https://packagist.org/packages/zaman-tech/sso)

## Installation

Run the following command to install the latest applicable version of the package:

```bash
    composer require zaman-tech/sso
```

## Setup:

add this lines to your .env file:

```ini
    ENGENESES_APP_ID=app_id
    ENGENESIS_APP_SECRET=app_secret
    ENGENESIS_APP_URL=app_url
```

add this lines to config/service file:

```ini
    'engenesis' => [
        "app_id" =>env('ENGENESES_APP_ID'),
        "app_secret" => env('ENGENESIS_APP_SECRET'),
        "app_url"   => env('ENGENESIS_APP_URL')
    ],
```

## Use:

```injectablephp
    use ZamanTech\Sso\Http\Controllers\SsoController;
    
    
    $sso = new SsoController($validated['code']);
    $content = $sso->getContent();
    $userDetails = $sso->getUserDetails();
    $userInfo = $sso->getUserInfo();
```

## License

Package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
