# Guía de Despliegue (Production)

Esta guía cubre los pasos esenciales para llevar tu aplicación Ragnos de un entorno de desarrollo local a un servidor de producción.

## 1. Configuración de Entorno

!!! danger "Importante"

    En producción, **nunca** debes ejecutar la aplicación en modo `development`. Esto expondría información sensible y ralentizaría la aplicación.

1. Edita el archivo `.env` en tu servidor.
2. Establece la variable de entorno:

```ini
CI_ENVIRONMENT = production
```

Esto desactivará la barra de depuración (debug toolbar), ocultará los errores detallados de PHP en pantalla y activará el caché de configuraciones.

## 2. Permisos del Sistema de Archivos

Asegúrate de que el usuario del servidor web (ej. `www-data` en Apache/Nginx) tenga permisos de **escritura** en la carpeta `writable` y todas sus subcarpetas.

```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

Las demás carpetas (`app`, `src`, `vendor` y la carpeta pública `public`) deben ser de solo lectura para el servidor web por motivos de seguridad, a menos que tengas necesidades específicas.

## 3. Base de Datos

Actualiza las credenciales de conexión en el archivo `.env` apuntando a tu base de datos de producción.

Asegúrate de que `app.baseURL` esté configurado correctamente con el dominio real (HTTPS recomendado).

```ini
app.baseURL = 'https://mi-sistema-produccion.com/'
```

### Migraciones y credenciales

Ejecuta las migraciones pendientes en cada despliegue. Entre otras cosas, amplían la columna de contraseñas para usar `password_hash()`:

```bash
php spark migrate
```

Antes de abrir el sistema, cambia la contraseña de `admin` desde el menú de usuario → *Cambiar contraseña* (pide la actual y al menos 8 caracteres).

!!! info "Contraseñas de versiones anteriores"

    Si tu base viene de una versión de Ragnos que guardaba contraseñas con md5, no hace falta restablecerlas: cada usuario sigue entrando con su clave y Ragnos actualiza su hash a `password_hash()` en ese primer inicio de sesión (requiere haber ejecutado `php spark migrate`).

!!! success "Administra la base de producción con PyroStudio"

    En un hosting compartido el puerto de MySQL casi nunca está abierto. [**PyroStudio**](https://github.com/cgarciagl/PyroStudio), el cliente de escritorio del mismo autor de Ragnos, se conecta mediante un **túnel HTTP**: subes `tunnel/pyro_tunnel.php` junto a tu aplicación y administras la base por HTTPS, sin abrir puertos. El túnel exige un token secreto y solo se conecta a bases en `localhost`. Marca la conexión como *Production* para que su Safe Mode pida confirmación ante sentencias destructivas, y usa *Respaldar / Restaurar* para respaldar antes de cada despliegue.

## 4. Carpeta pública y servidor web

Apunta el *Document Root* del servidor a la carpeta pública de Ragnos (`public`). Si tu hosting exige otro nombre (`content`, `public_html`, `www`), renómbrala y actualiza `$publicDirectory` en `app/Config/Paths.php`; consulta [Configuración](../fundamentos/configuracion.md#carpeta-publica-public).

Para mayor seguridad puedes desactivar el Auto Routing (`$autoRoute = false`): Ragnos registrará rutas explícitas solo para sus acciones. Consulta [Trabajar sin Auto Routing](../fundamentos/configuracion.md#trabajar-sin-auto-routing-rutas-explicitas).

## 5. Eliminar archivos de desarrollo

Se recomienda no subir archivos innecesarios al servidor de producción:

- Tests (`tests/`)
- Archivos de git (`.git/`, `.gitignore`)
- Documentación interna (`documentacion/`)
- Archivos SQL de ejemplos (`sampledatabase/`)

## Solución de Problemas Comunes

**Error 404 en todas las páginas excepto el Home:**
Esto suele ser un problema de configuración del servidor web (Apache/Nginx) que no está redirigiendo las peticiones a `index.php`.
Asegúrate de que el archivo `.htaccess` de la carpeta pública (`public/.htaccess`) esté presente y que el módulo `mod_rewrite` esté activo en Apache.

**Página en blanco o Error 500:**
Verifica los logs en `writable/logs/` para ver el error real, ya que en modo producción no se muestran en pantalla.
