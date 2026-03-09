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

## Routing (Auto Routing Legacy)

Ragnos comes pre-configured by default to operate under CodeIgniter 4's **Auto Routing (Legacy)** mode. This automatic routing mechanism refers to the framework's core ability to directly map HTTP requests (URLs) to controller classes and their respective methods based on simple naming conventions, entirely eliminating the need to explicitly define or register each route within the `Routes.php` configuration file. When a request enters the system, the routing component parses the uniform resource identifier's segments: typically, the first segment matches the controller's name, while the second segment subsequently delegates execution to a specific method defined within that class.

The defining characteristics of the Auto Routing mode lie in its transparency and agility. By removing the tedious burden of maintaining a massive list of routes and closures for every new module or endpoint added to the application, developers are free to immediately focus entirely on writing business logic and leveraging the rapid Dataset controllers that Ragnos provides out of the box. This deeply aligns with the framework's overarching "Low Code" philosophy, where robust CRUDs and advanced reports are spun up almost instantaneously, making their methods publicly accessible just by generating the file. Furthermore, it is a robust approach that has matured substantially over time, historically serving as the classic foundation in earlier iterations of CodeIgniter.

The most notable advantages of utilizing the Legacy Auto Routing environment in Ragnos revolve around significantly reducing setup time and establishing vastly predictable URL structures. This proves to be invaluable for management platforms, administrative dashboards, and internal data applications interconnected seamlessly, where the volume of distinct controllers usually expands extremely fast. It naturally facilitates quick prototyping without forcing constant context switches between routing configuration layers and the actual controllers holding the logic. Crucially, leveraging auto-routing does not severely bottleneck the system's flexibility; developers can effectively embrace a hybrid model by continuing to manually write specific secure routes conventionally in the registry whenever strict control is necessary over particular parameterized URIs, successfully blending peak productivity speed with granular protective control when demanded.

## Other Configurations (CodeIgniter)

Remember that Ragnos respects the native CI4 configuration. Important files in `app/Config/`:

- **App.php**: Base configuration (`baseURL`, `indexPage`).
- **Database.php**: Connection credentials.
- **Security.php**: CSRF configuration and security headers.
