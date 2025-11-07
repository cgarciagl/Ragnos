# Controlador Usuarios

El controlador `Usuarios` gestiona las operaciones relacionadas con los usuarios del sistema. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Grupo restringido**: Solo accesible para el grupo `Administrador`.
- **Título**: `👨🏻‍💻 Usuarios`.
- **Tabla asociada**: `gen_usuarios`.
- **Campo ID**: `usu_id`.
- **Campos adicionales**:
  - `usu_nombre`: Configurado con la etiqueta `Nombre` y la regla `required`.
  - `usu_login`: Configurado con la etiqueta `Login` y las reglas `required|is_unique`.
  - `usu_pword`: Configurado como un campo de tipo `password` con valor predeterminado `ok` y la regla `required`.
  - `usu_activo`: Configurado como un campo desplegable con opciones `S` (Sí) y `N` (No), valor predeterminado `N` y la regla `required`.
  - `usu_grupo`: Configurado con la etiqueta `Grupo` y la regla `required`.
- **Búsquedas relacionadas**:
  - `usu_grupo`: Relacionado con el controlador `Gruposdeusuarios`.
- **Campos de tabla**: `usu_nombre`, `usu_login`, `usu_grupo`, `usu_activo`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y restringe el acceso al grupo `Administrador`.

### \_beforeDelete()

Se ejecuta antes de eliminar un usuario. Verifica:

- Si el usuario es el superusuario (`usu_id = 1`), lanza un error.
- Si el usuario intenta eliminar su propia cuenta, lanza un error.

### \_beforeUpdate(&$userData)

Se ejecuta antes de actualizar un usuario. Si el campo `usu_pword` ha cambiado, lo encripta utilizando `md5`.

### \_beforeInsert(&$userData)

Se ejecuta antes de insertar un nuevo usuario. Encripta el campo `usu_pword` utilizando `md5`.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
