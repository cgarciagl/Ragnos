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

### Trabajar sin Auto Routing (rutas explícitas)

Si tu política de seguridad exige desactivar el Auto Routing, Ragnos sigue funcionando completo. Basta con cambiar una línea en `app/Config/Routing.php`:

```php
public bool $autoRoute = false;
```

Al detectarlo, `app/Config/Routes.php` llama a `Ragnos\Routing\RagnosRoutes::discover($routes)`, que recorre `app/Controllers` y registra, para cada controlador:

| Tipo de controlador | Rutas que registra |
| :------------------ | :----------------- |
| `RDatasetController` / `RQueryController` | `index` y las acciones que usan las vistas, el JavaScript y el modo API (`getAjaxGridData`, `getFormData`, `formProcess`, `searchByAjax`, `tableByAjax`, `getRecordByAjax`, `ajaxdelete`, `save`, `delete`, `history`, `getFieldsConfig`, `getTableFields`, `genericAdvancedReport`, `search`) |
| `RProcessController` | `showProgress` y `start` |
| Cualquier otro controlador | Sus métodos públicos propios |

En todos los casos se agregan además los métodos públicos que declara la propia clase (por ejemplo, `Ordenes::calculatotal` o `Reportes::ventaspormes`). Las URLs son las mismas que con Auto Routing, así que menús y enlaces no cambian.

!!! tip "Más seguro que el Auto Routing"

    Con el Auto Routing cualquier método público, incluso heredado (como `setSortingField()`), es alcanzable por URL. `RagnosRoutes` solo expone acciones conocidas y los métodos que tú declaraste.

Si prefieres declarar cada controlador a mano, usa los métodos individuales en `Routes.php`:

```php
use Ragnos\Routing\RagnosRoutes;

RagnosRoutes::dataset($routes, 'tienda/productos', \App\Controllers\Tienda\Productos::class);
RagnosRoutes::process($routes, 'proceso', \App\Controllers\Proceso::class);
RagnosRoutes::controller($routes, 'admin', \App\Controllers\Admin::class);
```

## Carpeta pública (`public`)

Por defecto, la carpeta raíz del sitio web se llama `public`, igual que en CodeIgniter 4. Si tu servidor o hosting exige otro nombre (`content`, `public_html`, `www`, …) o simplemente prefieres uno distinto, puedes cambiarlo así:

1. Renombra la carpeta (por ejemplo, de `public` a `content`).
2. Actualiza la ruta en `app/Config/Paths.php`:

    ```php
    public string $publicDirectory = __DIR__ . '/../../content';
    ```

3. Apunta el *Document Root* de tu servidor web a la carpeta renombrada.
4. Ajusta las rutas que mencionen el nombre anterior: la URL de acceso (`app.baseURL` en `.env`), la línea de `.gitignore` que excluye las imágenes subidas por los usuarios (`public/assets/img/usuarios/*`) y, si usas el probador de API, `apitest/config.js` y `apitest/app.js` (apuntan a `../public/index.php`).

El navegador entra por el `index.php` de esa carpeta, que no necesita cambios. `$publicDirectory` lo usan `php spark` (incluido `php spark serve`) y las pruebas automatizadas.

!!! note "Nombres de carpeta en esta documentación"

    En todos los ejemplos de esta documentación la carpeta pública se llama `public` (por ejemplo, `public/assets/js/custom.js`). Si la renombraste, sustituye mentalmente `public` por el nombre que elegiste.

## Otras Configuraciones (CodeIgniter)

Recuerda que Ragnos respeta la configuración nativa de CI4. Archivos importantes en `app/Config/`:

- **App.php**: Configuración base (`baseURL`, `indexPage`).
- **Database.php**: Credenciales de conexión.
- **Security.php**: Configuración CSRF y headers de seguridad.
