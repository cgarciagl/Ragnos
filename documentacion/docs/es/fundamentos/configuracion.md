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

## Enrutamiento (Auto Routing Legacy)

Ragnos viene preconfigurado por defecto para funcionar bajo el modo **Auto Routing (Legacy)** de CodeIgniter 4. Este mecanismo de enrutamiento automático se refiere a la capacidad del framework para mapear directamente las solicitudes HTTP (URLs) hacia las clases y métodos de los controladores basándose en simples convenciones de nomenclatura, sin la necesidad de definir o registrar cada ruta de manera explícita en el archivo de configuración `Routes.php`. Cuando una petición ingresa al sistema, el componente encargado de las rutas analiza los segmentos de la URL: el primer segmento corresponde al nombre del controlador y el segundo segmento invoca al método específico a ejecutar dentro de esa clase.

Las características principales del modo Auto Routing radican en su transparencia y agilidad. Al evitar la labor tediosa de mantener un listado robusto de rutas y cierres (closures) por cada nuevo endpoint o módulo que se crea, el desarrollador puede centrarse inmediatamente en escribir la lógica de negocio y los controladores tipo Dataset que Ragnos facilita. Esto se alinea de forma natural con la filosofía "Low Code" del framework, donde la creación de componentes CRUD completos o reportes avanzados se logra prácticamente al instante, publicando automáticamente los métodos necesarios directamente a partir de su existencia en el archivo. Además, es un modo que ha madurado a lo largo del tiempo e históricamente ha sido la forma clásica de trabajo en versiones previas de CodeIgniter.

Las ventajas más destacables de usar el entorno Legacy Auto Routing en Ragnos incluyen una drástica reducción del tiempo de configuración y un enrutamiento altamente predecible. Esto resulta invaluable para proyectos de gestión, paneles administrativos y utilidades internas interconectadas, en donde la cantidad de controladores tiende a crecer con gran rapidez. Permite también la rápida visualización y prototipado sin tener que alternar constantemente entre archivos de mapeo de rutas y los propios controladores. Es fundamental mencionar que esto no limita la flexibilidad de la plataforma; el desarrollador puede, en un modelo híbrido, seguir definiendo excepciones o rutas seguras de manera convencional en la configuración en los casos donde necesite control estricto sobre URI paramétricas específicas, manteniendo lo mejor de ambos mundos: rapidez productiva y control granular cuando la capa de seguridad o restricciones lo requieran.

## Otras Configuraciones (CodeIgniter)

Recuerda que Ragnos respeta la configuración nativa de CI4. Archivos importantes en `app/Config/`:

- **App.php**: Configuración base (`baseURL`, `indexPage`).
- **Database.php**: Credenciales de conexión.
- **Security.php**: Configuración CSRF y headers de seguridad.
