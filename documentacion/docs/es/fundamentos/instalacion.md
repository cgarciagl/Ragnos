# Instalación de Ragnos

Esta guía describe cómo instalar Ragnos Framework clonando el repositorio (o descargando el ZIP) e instalando sus dependencias con Composer.

## Requisitos del Servidor

Ragnos está basado en **CodeIgniter 4**, por lo que comparte sus requisitos mínimos:

- **PHP**: Versión 8.1 o superior.
- **Composer** 2.x.
- **Extensiones PHP**:
  - intl
  - mbstring
  - json
  - mysqlnd (si usas MySQL/MariaDB)
  - curl
  - gd (opcional, para manipulación de imágenes)
- **Base de Datos**: MySQL (5.1+) o MariaDB.

## Pasos de Instalación

### 1. Obtener el código e instalar dependencias

1. Clona el repositorio en el directorio de tu servidor web (por ejemplo, `c:\laragon\www\mi-proyecto` o `/var/www/html/mi-proyecto`):

    ```bash
    git clone https://github.com/cgarciagl/Ragnos.git mi-proyecto
    cd mi-proyecto
    ```

    Si prefieres no usar Git, descarga el [.zip](https://github.com/cgarciagl/Ragnos/archive/refs/heads/main.zip) y extráelo en la misma ubicación.

2. Instala las dependencias (incluido CodeIgniter 4) con Composer:

    ```bash
    composer install
    ```

    Este paso crea la carpeta `vendor/`. CodeIgniter ya no se incluye copiado dentro del repositorio; se instala como dependencia, así que recibes sus parches de seguridad con un simple `composer update`.

!!! note "¿Dónde está el núcleo de Ragnos?"

    El código del framework vive en la carpeta `src/` bajo el namespace `Ragnos\` (por ejemplo, `Ragnos\Controllers\RDatasetController`). Tu aplicación sigue en `app/`.

### 2. Configuración del Entorno

1. Copia la plantilla `.env.example` de la raíz del proyecto a `.env`:

    ```bash
    cp .env.example .env
    ```

2. Abre el archivo `.env` y ajusta las siguientes variables:

!!! warning "No subas tu `.env` al repositorio"

    El archivo `.env` contiene credenciales y está excluido en `.gitignore`. Versiona solo `.env.example`, sin contraseñas reales.

**Entorno:**

```ini
CI_ENVIRONMENT = development
```

**URL Base:**

```ini
app.baseURL = 'http://localhost/mi-proyecto/'
```

**Base de Datos:**
Descomenta y configura las credenciales de tu base de datos:

```ini
database.default.hostname = localhost
database.default.database = nombre_de_tu_bd
database.default.username = tu_usuario
database.default.password = tu_contraseña
database.default.DBDriver = MySQLi
```

### 3. Crear la Base de Datos

Ragnos separa sus tablas en dos scripts dentro de `sampledatabase/`:

| Script | Contenido | ¿Obligatorio? |
| :----- | :-------- | :-----------: |
| `ragnos_base.sql` | Tablas del sistema: usuarios, grupos, bitácora de auditoría y sesiones. Incluye el usuario `admin`. | ✅ |
| `demo_classicmodels.sql` | Demo de la tienda de modelos a escala (**Classicmodels**): catálogos, reportes y panel de ventas. | Opcional |

1. Crea una base de datos vacía con el mismo nombre que pusiste en `database.default.database` (por defecto, `ragnos`):

    ```sql
    CREATE DATABASE ragnos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

2. Importa las tablas del sistema:

    ```bash
    mysql -u root -p ragnos < sampledatabase/ragnos_base.sql
    ```

3. (Opcional) Importa la demo para explorar Ragnos con datos reales. Consulta [Base de Datos Demo](base_de_datos_demo.md) para conocer su estructura.

    ```bash
    mysql -u root -p ragnos < sampledatabase/demo_classicmodels.sql
    ```

!!! success "Recomendado: PyroStudio, del mismo autor de Ragnos"

    [**PyroStudio**](https://github.com/cgarciagl/PyroStudio) es el cliente de escritorio para **MariaDB, MySQL y SQLite** creado por el autor de Ragnos, pensado para trabajar de la mano con el framework. Es libre (MIT), nativo y ultraligero (Tauri + Rust, sin Electron ni Java) y funciona en Windows, macOS y Linux.

    Para cargar los scripts: haz clic derecho sobre la base `ragnos` → *Respaldar / Restaurar* → pestaña *Restaurar* → *Restaurar en BD Existente*, y elige primero `ragnos_base.sql` y después `demo_classicmodels.sql`.

    Además incluye un **túnel HTTP en PHP** para administrar la base de un hosting compartido que no expone el puerto de MySQL (justo donde suele desplegarse Ragnos), **Safe Mode** que pide confirmación ante sentencias destructivas, y credenciales cifradas.

    Descárgalo desde [Releases](https://github.com/cgarciagl/PyroStudio/releases).

!!! tip "¿Usas HeidiSQL o phpMyAdmin?"

    Selecciona la base de datos y usa *Archivo → Cargar archivo SQL* (HeidiSQL) o la pestaña *Importar* (phpMyAdmin). Los scripts no fijan el nombre de la base, así que se importan en la que tengas seleccionada.

!!! tip "¿Usas Laragon en Windows?"

    Abre la terminal desde el botón *Terminal* de Laragon: ya incluye `mysql` en el PATH (el usuario `root` no tiene contraseña; puedes omitir `-p`).

!!! warning "Scripts de instalación"

    Ambos scripts eliminan y vuelven a crear sus tablas. Úsalos para instalar, nunca sobre una base con datos que quieras conservar.

!!! info "Con o sin demo"

    Sin la demo, Ragnos arranca con un panel de inicio del sistema (usuarios, grupos y actividad reciente) y el menú solo muestra *Inicio* y *Administración*. Al importar `demo_classicmodels.sql` aparecen automáticamente los menús *Catálogos*, *Reportes* y *Procesos*, y el panel de ventas de la tienda. No hace falta cambiar ninguna configuración ni reiniciar: puedes cargar la demo en cualquier momento.

### 4. Verificar Permisos

Asegúrate de que la carpeta `writable/` y sus subcarpetas tengan permisos de escritura por parte del servidor web.

### 5. Ejecutar

Accede a tu navegador en la URL configurada (ej. `http://localhost/mi-proyecto/content` o simplemente `http://mi-proyecto.test` si usas Laragon).

!!! note "Cambio de carpeta pública"

    En el código fuente, la carpeta `public` ha sido renombrada a `content`. Sin embargo, no hay problema en cambiarle el nombre al que mejor convenga para tu servidor.

Deberías ver la pantalla de inicio de sesión.

### 6. Acceso y Datos Demo

Para obtener las credenciales de acceso predeterminadas y conocer más sobre los datos de ejemplo incluidos (**Classicmodels**), consulta la sección [Base de Datos Demo](base_de_datos_demo.md).

## Migrar desde versiones anteriores (`app/ThirdParty/Ragnos`)

Si tu proyecto se creó con una versión anterior de Ragnos, el núcleo vivía en `app/ThirdParty/Ragnos` con el namespace `App\ThirdParty\Ragnos`, y CodeIgniter venía copiado en `system/`. Para actualizar:

1. Reemplaza la carpeta `app/ThirdParty/Ragnos` por la nueva carpeta `src/` y elimina `system/`.
2. Copia los cambios de `composer.json` (dependencia `codeigniter4/framework` y autoload `"Ragnos\\": "src/"`), de `app/Config/Paths.php` y de `app/Config/Autoload.php`, y ejecuta `composer install`.
3. Busca y reemplaza en tu código `App\ThirdParty\Ragnos\` por `Ragnos\`.
4. Ejecuta `php spark migrate` (amplía la columna de contraseñas; las claves md5 existentes se actualizan solas en el siguiente inicio de sesión).
5. Si tenías `.env` versionado, quítalo del repositorio con `git rm --cached .env` y cambia cualquier contraseña que haya llegado a subirse.

!!! info "Compatibilidad temporal"

    Ragnos incluye un cargador de alias (`src/Compat/legacy_namespace.php`) para que los `use App\ThirdParty\Ragnos\...` existentes sigan funcionando mientras migras. Está marcado como obsoleto y se eliminará en una versión mayor futura.
