# Configuración de Ragnos

Además de la configuración estándar de CodeIgniter 4 (base de datos, rutas, etc.), Ragnos incluye un archivo de configuración específico para personalizar el comportamiento global del framework.

## Archivo de Configuración

El archivo se encuentra en `app/Config/RagnosConfig.php`.

```php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class RagnosConfig extends BaseConfig
{
    public $Ragnos_application_title = '🏪 Tienda';
    public $Ragnos_all_to_uppercase = false;

    public $currency = 'USD';
    public $locale = 'es_MX';
}
```

## Variables Disponibles

### Identidad de la Aplicación

| Variable                    | Tipo     | Descripción                                                                                                                                                 |
| :-------------------------- | :------- | :---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `$Ragnos_application_title` | `string` | Define el nombre público de la aplicación. Este texto aparecerá en la barra superior (Topbar), en el menú lateral (Sidebar) y en los encabezados de página. |

### Comportamiento de Datos

| Variable                   | Tipo   | Descripción                                                                                                                                             |
| :------------------------- | :----- | :------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `$Ragnos_all_to_uppercase` | `bool` | Si se establece en `true`, fuerza la conversión a mayúsculas de ciertos campos de entrada predeterminados en los formularios generados automáticamente. |

### Regionalización

| Variable    | Tipo     | Descripción                                                                                                                                                   |
| :---------- | :------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `$currency` | `string` | Define el código de moneda por defecto (ej. 'USD', 'MXN', 'EUR') utilizado en los helpers de formateo de dinero.                                              |
| `$locale`   | `string` | Define el locale predeterminado para formatos de fecha y número (ej. 'es_MX', 'en_US'). Nota: Esto es independiente del idioma de la interfaz que maneja CI4. |

## Enrutamiento (Auto Routing)

Una particularidad importante de Ragnos es que mantiene habilitada la característica de **Auto Routing** (`$routes->setAutoRoute(true)`) de CodeIgniter 4 por defecto.

### ¿Qué implica esto para el desarrollador?

1.  **Cero Configuración de Rutas:** Al crear un nuevo controlador (por ejemplo, con el [Generador CLI](plantilla.md)), este es accesible inmediatamente vía URL (`/TuControlador/metodo`) sin necesidad de editar `app/Config/Routes.php`.
2.  **Agilidad:** Esto es fundamental para la filosofía "Low Code" de Ragnos, permitiendo prototipar y desplegar módulos CRUD en segundos.
3.  **Seguridad y Personalización:** Si necesitas URLs específicas o restringir el acceso, puedes definir rutas manuales en `Config/Routes.php`. Las rutas manuales tienen prioridad sobre las automáticas.

> ⚠️ **Importante:** Si decides desactivar el Auto Routing (`false`) por políticas de seguridad estrictas, deberás registrar manualmente cada ruta de tus Datasets, lo cual incrementa el trabajo de mantenimiento.

## Otras Configuraciones (CodeIgniter)

Recuerda que Ragnos respeta la configuración nativa de CI4. Archivos importantes en `app/Config/`:

- **App.php**: Configuración base (`baseURL`, `indexPage`).
- **Database.php**: Credenciales de conexión.
- **Security.php**: Configuración CSRF y headers de seguridad.
