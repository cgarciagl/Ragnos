# Controlador Admin

El controlador `Admin` es responsable de manejar las operaciones administrativas del sistema. A continuación, se describen sus funciones y configuraciones principales.

## Configuración

Este controlador extiende de `BaseController` y utiliza los siguientes helpers y modelos:

- Helper: `App\ThirdParty\Ragnos\Helpers\ragnos_helper`
- Modelo: `App\Models\Dashboard`

## Funciones

### index()

Carga el dashboard administrativo con datos como:

- Ventas de los últimos 12 meses.
- Estados de cuenta.
- Ventas por línea.

### perfil()

Muestra la vista del perfil del administrador.

### login()

Gestiona el inicio de sesión del administrador. Valida las credenciales y redirige al dashboard o muestra errores.

### busqueda()

Realiza búsquedas dinámicas y devuelve resultados en formato JSON.

### testusuarios()

Busca usuarios en la base de datos según un término de búsqueda, con paginación.

### logout()

Cierra la sesión del administrador y redirige a la página de inicio de sesión.

### sess()

Devuelve los datos de la sesión actual en formato JSON.

## Constructor

El constructor de este controlador no tiene configuraciones adicionales específicas.
