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

Las demás carpetas (`app`, `system`, `public`) deben ser de solo lectura para el servidor web por motivos de seguridad, a menos que tengas necesidades específicas.

## 3. Base de Datos

Actualiza las credenciales de conexión en el archivo `.env` apuntando a tu base de datos de producción.

Asegúrate de que `app.baseURL` esté configurado correctamente con el dominio real (HTTPS recomendado).

```ini
app.baseURL = 'https://mi-sistema-produccion.com/'
```

## 4. Eliminar archivos de desarrollo

Se recomienda no subir archivos innecesarios al servidor de producción:

- Tests (`tests/`)
- Archivos de git (`.git/`, `.gitignore`)
- Documentación interna (`documentacion/`)
- Archivos SQL de ejemplos (`sampledatabase/`)

## Solución de Problemas Comunes

**Error 404 en todas las páginas excepto el Home:**
Esto suele ser un problema de configuración del servidor web (Apache/Nginx) que no está redirigiendo las peticiones a `index.php`.
Asegúrate de que el archivo `public/.htaccess` esté presente y que el módulo `mod_rewrite` esté activo en Apache.

**Página en blanco o Error 500:**
Verifica los logs en `writable/logs/` para ver el error real, ya que en modo producción no se muestran en pantalla.
