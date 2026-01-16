# Registro de Auditoría (Audit Log)

El framework Ragnos incluye un sistema nativo y automático para registrar los cambios realizados en los datos de las entidades. Este sistema permite rastrear quién realizó una acción, cuándo, desde qué dirección IP y qué datos específicos fueron modificados, proporcionando una trazabilidad completa de las operaciones.

## Configuración

### Uso en Datasets (RDatasetController)

!!! success "Activado por defecto"

    Todas las clases que extienden de `RDatasetController` tienen el registro de auditoría **activado automáticamente**.

Si desea **desactivar** la auditoría para un dataset específico (por ejemplo, para tablas temporales o de movimientos masivos donde no se requiere trazabilidad), puede utilizar el método `setEnableAudit(false)` dentro del constructor.

**Ejemplo:**

```php
class LogsTemporales extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->setTableName('logs_temp');
        $this->setIdField('id');

        // ❌ Desactivar auditoría para este controlador
        $this->setEnableAudit(false);

        // ... resto de configuración
    }
}
```

### Uso en Modelos Manuales

Para activar el registro de auditoría en un modelo personalizado (que no es gestionado por un Dataset), debe establecer la propiedad `$enableAudit` en `true`. Este modelo debe estar utilizando el `CrudOperationsTrait` (o heredar de una clase base que lo use, como `RdatasetModel`).

```php
class ClientesModel extends RdatasetModel
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    // Habilitar auditoría automática para esta entidad
    protected $enableAudit = true;

    // ... definición de campos
}
```

Al habilitar esta propiedad, todas las operaciones de inserción, actualización y eliminación realizadas a través del controlador/modelo serán interceptadas y registradas automáticamente sin necesidad de código adicional en sus controladores.

## Funcionamiento Interno

El sistema funciona a través de `App\ThirdParty\Ragnos\Models\Traits\CrudOperationsTrait`. Cuando se ejecuta una acción de escritura, el sistema realiza los siguientes pasos de manera transparente:

### 1. Inserción (INSERT)

- Se ejecuta después de que el registro ha sido guardado exitosamente.
- Guarda en el log todos los datos del nuevo registro bajo la clave `new`.

### 2. Actualización (UPDATE)

- Antes de guardar, el sistema compara los valores actuales en la base de datos con los nuevos valores enviados.
- Solo si hay diferencias reales, se procede a guardar el log.
- **Optimización**: Se registra un JSON que contiene únicamente los campos que cambiaron, con su valor anterior (`old`) y el nuevo (`new`).

  _Ejemplo de JSON guardado:_

  ```json
  {
    "status": {
      "old": "pendiente",
      "new": "activo"
    },
    "limite_credito": {
      "old": "1000.00",
      "new": "2500.00"
    }
  }
  ```

### 3. Eliminación (DELETE)

- Antes de eliminar el registro físico, el sistema realiza una consulta para obtener los datos actuales.
- Guarda una copia completa de los datos del registro eliminado bajo la clave `deleted_data` para permitir una eventual consulta histórica o restauración manual.

## Información Registrada

Para cada evento, el modelo `AuditLogModel` (tabla `gen_audit_logs`) almacena la siguiente información de contexto:

| Campo        | Descripción                                                                                                                                          |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `user_id`    | ID del usuario que realizó la acción. El sistema resuelve automáticamente la identidad ya sea por sesión activa (Web) o por Token Bearer (API).      |
| `table_name` | Nombre de la tabla de la base de datos donde ocurrió el cambio.                                                                                      |
| `record_id`  | ID (Clave primaria) del registro afectado.                                                                                                           |
| `action`     | Tipo de operación: `INSERT`, `UPDATE` o `DELETE`.                                                                                                    |
| `changes`    | Carga útil (Payload) en formato JSON con los detalles del cambio (ver sección anterior). Se almacena con soporte Unicode para caracteres especiales. |
| `ip_address` | Dirección IP desde donde se originó la solicitud.                                                                                                    |
| `user_agent` | Información del navegador o cliente HTTP utilizado.                                                                                                  |

## Detección de Usuario (Web y API)

El mecanismo de auditoría es agnóstico al origen de la petición. El método `getCurrentUserId()` intenta resolver la identidad del actor de la siguiente manera:

1. **Sesión Web:** Verifica si existe una sesión activa de CodeIgniter (`session()->get('usu_id')`).
2. **API Token:** Si es una llamada API, inspecciona el encabezado `Authorization`. Extrae el token Bearer y busca al usuario correspondiente en la tabla `gen_usuarios`.
3. **Fallback:** Si no se identifica al usuario, se registra el ID `0` (Sistema/Anónimo).
