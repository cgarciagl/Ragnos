# Ragnos Configuration

In addition to standard CodeIgniter 4 configuration (database, routes, etc.), Ragnos includes a specific configuration file to customize the global behavior of the framework.

## Configuration File

The file is located at `app/Config/RagnosConfig.php`.

```php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class RagnosConfig extends BaseConfig
{
    public $Ragnos_application_title = '🏪 Store';
    public $Ragnos_all_to_uppercase = false;

    public $currency = 'USD';
    public $locale = 'en_US';
}
```

## Available Variables

### Application Identity

| Variable                    | Type     | Description                                                                                                                       |
| :-------------------------- | :------- | :-------------------------------------------------------------------------------------------------------------------------------- |
| `$Ragnos_application_title` | `string` | Defines the public name of the application. This text will appear in the top bar (Topbar), side menu (Sidebar), and page headers. |

### Data Behavior

| Variable                   | Type   | Description                                                                                                         |
| :------------------------- | :----- | :------------------------------------------------------------------------------------------------------------------ |
| `$Ragnos_all_to_uppercase` | `bool` | If set to `true`, forces conversion to uppercase for certain default input fields in automatically generated forms. |

### Localization

| Variable    | Type     | Description                                                                                                                                          |
| :---------- | :------- | :--------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$currency` | `string` | Defines the default currency code (e.g., 'USD', 'MXN', 'EUR') used in money formatting helpers.                                                      |
| `$locale`   | `string` | Defines the default locale for date and number formats (e.g., 'es_MX', 'en_US'). Note: This is independent of the interface language handled by CI4. |

## Routing (Auto Routing)

An important feature of Ragnos is that it keeps the **Auto Routing** feature (`$routes->setAutoRoute(true)`) of CodeIgniter 4 enabled by default.

### What does this imply for the developer?

1.  **Zero Route Configuration:** When creating a new controller (e.g., with the [CLI Generator](plantilla.md)), it is immediately accessible via URL (`/YourController/method`) without needing to edit `app/Config/Routes.php`.
2.  **Agility:** This is fundamental to Ragnos' "Low Code" philosophy, allowing CRUD modules to be prototyped and deployed in seconds.
3.  **Security and Customization:** If you need specific URLs or to restrict access, you can define manual routes in `Config/Routes.php`. Manual routes have priority over automatic ones.

> ⚠️ **Important:** If you decide to disable Auto Routing (`false`) due to strict security policies, you will need to manually register each route for your Datasets, which increases maintenance work.

## Other Configurations (CodeIgniter)

Remember that Ragnos respects the native CI4 configuration. Important files in `app/Config/`:

- **App.php**: Base configuration (`baseURL`, `indexPage`).
- **Database.php**: Connection credentials.
- **Security.php**: CSRF configuration and security headers.
