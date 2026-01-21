# Instalación de Ragnos

Esta guía describe cómo instalar Ragnos Framework a partir de un archivo ZIP distribuido.

## Requisitos del Servidor

Ragnos está basado en **CodeIgniter 4**, por lo que comparte sus requisitos mínimos:

- **PHP**: Versión 7.4 o superior (se recomienda PHP 8.1+).
- **Extensiones PHP**:
  - intl
  - mbstring
  - json
  - mysqlnd (si usas MySQL/MariaDB)
  - curl
  - gd (opcional, para manipulación de imágenes)
- **Base de Datos**: MySQL (5.1+) o MariaDB.

## Pasos de Instalación

### 1. Descarga y Extracción

1. Descarga el archivo `.zip` con la última versión de Ragnos.
2. Extrae el contenido en el directorio de tu servidor web (por ejemplo, `c:\laragon\www\mi-proyecto` o `/var/www/html/mi-proyecto`).
3. Verifique que la carpeta `vendor` existe y contiene las dependencias. Ragnos ya incluye todas las librerías necesarias.

### 2. Configuración del Entorno

1. Ubica el archivo `env` en la raíz del proyecto.
2. Renómbralo o cópialo a `.env`.
3. Abre el archivo `.env` y ajusta las siguientes variables:

!!! note "Ojo con el punto"

    Asegúrate de que el archivo se llame exactamente `.env` (con el punto al inicio) y no solo `env`.

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

### 3. Importar Base de Datos

Ragnos requiere ciertas tablas base para funcionar (usuarios, sesiones, permisos).

1. Crea una base de datos vacía en tu gestor (phpMyAdmin, HeidiSQL, etc.).
2. Importa los archivos SQL ubicados en la carpeta `sampledatabase/`:
   - Ejecuta primero `ragnos_mariadb.sql` (o el dump principal que contenga la estructura base).
   - Ejecuta `ci_sessions.sql` para la tabla de sesiones.

### 4. Verificar Permisos

Asegúrate de que la carpeta `writable/` y sus subcarpetas tengan permisos de escritura por parte del servidor web.

### 5. Ejecutar

Accede a tu navegador en la URL configurada (ej. `http://localhost/mi-proyecto/content` o simplemente `http://mi-proyecto.test` si usas Laragon).

!!! note "Cambio de carpeta pública"

    En el código fuente, la carpeta `public` ha sido renombrada a `content`. Sin embargo, no hay problema en cambiarle el nombre al que mejor convenga para tu servidor.

Deberías ver la pantalla de inicio de sesión.

### 6. Credenciales de Acceso (Demo)

Para acceder al sistema demo por primera vez, utiliza las siguientes credenciales:

- **Usuario:** `admin`
- **Contraseña:** `admin`
