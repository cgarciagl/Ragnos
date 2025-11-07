# Controlador BaseController

El controlador `BaseController` sirve como base para todos los controladores del sistema. Proporciona una estructura común y facilita la carga de componentes compartidos.

## Configuración

Este controlador extiende de `CodeIgniter\Controller` y utiliza las siguientes propiedades:

- `$request`: Instancia del objeto de solicitud principal.
- `$helpers`: Lista de helpers que se cargarán automáticamente.

## Funciones

### initController()

Inicializa el controlador con los siguientes parámetros:

- `$request`: La solicitud entrante.
- `$response`: La respuesta saliente.
- `$logger`: El registrador de logs.

Esta función es ideal para precargar modelos, bibliotecas u otros componentes necesarios.

## Constructor

El constructor de este controlador no tiene configuraciones adicionales específicas.
