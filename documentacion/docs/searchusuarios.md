# Controlador Searchusuarios

El controlador `Searchusuarios` permite realizar búsquedas y gestionar usuarios en el sistema. Extiende de `RQueryController` y configura una consulta específica para obtener datos de usuarios.

## Configuración

- **Grupo restringido**: Solo accesible para el grupo `Administrador`.
- **Título**: `🔎 Usuarios`.
- **Consulta SQL**: `SELECT usu_id, usu_nombre as 'Nombre', usu_login as 'Login', usu_activo as Activo, usu_grupo FROM gen_usuarios`.
- **Campo ID**: `usu_id`.
- **Campos adicionales**:
  - `Activo`: Configurado como un campo desplegable con opciones `S` (Sí) y `N` (No).
  - `usu_grupo`: Configurado con la etiqueta `Grupo`.
- **Búsquedas relacionadas**:
  - `usu_grupo`: Relacionado con el controlador `Gruposdeusuarios`.
- **Campos de tabla**: `Nombre`, `Login`, `Activo`, `usu_grupo`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y restringe el acceso al grupo `Administrador`.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
