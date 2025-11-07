# Controlador Gruposdeusuarios

El controlador `Gruposdeusuarios` gestiona las operaciones relacionadas con los grupos de usuarios en el sistema. Extiende de `RDatasetController` y añade configuraciones específicas para este recurso.

## Configuración

- **Grupo restringido**: Solo accesible para el grupo `Administrador`.
- **Título**: `Grupos de Usuarios`.
- **Tabla asociada**: `gen_gruposdeusuarios`.
- **Campo ID**: `gru_id`.
- **Campos adicionales**:
  - `gru_nombre`: Configurado con las reglas `required|is_unique`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y restringe el acceso al grupo `Administrador`.

### \_beforeDelete()

Se ejecuta antes de eliminar un grupo. Verifica:

- Si el grupo es el de administradores (`gru_id = 1`), lanza un error.
- Si el grupo tiene usuarios asociados, lanza un error.

### \_beforeUpdate(&$a)

Se ejecuta antes de actualizar un grupo. Si el grupo es el de administradores (`gru_id = 1`), lanza un error.

### checkAssociatedUsers($groupId)

Verifica si un grupo tiene usuarios asociados. Si es así, lanza un error.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
