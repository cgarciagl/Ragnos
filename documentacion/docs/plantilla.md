# Plantilla oficial de nuevo Dataset

```php
<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class EjemploDataset extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->checklogin();
        $this->setTitle('Ejemplo');
        $this->setTableName('example_table');
        $this->setIdField('id');
        $this->setAutoIncrement(true);

        $this->addField('name', [
            'label' => 'Nombre',
            'rules' => 'required'
        ]);

        $this->setTableFields(['name']);
    }
}
```

Documentación de la plantilla de Dataset

Propósito
Esta plantilla sirve como punto de partida para crear un nuevo Dataset que se integrará en la tienda (store) del proyecto. Proporciona la estructura, convenciones y campos mínimos recomendados para que el Dataset funcione correctamente en el flujo existente.

Instrucciones básicas

- Duplica este archivo y colócalo en la ruta adecuada del repositorio para el nuevo Dataset.
- Ajusta nombres de clases, tablas y campos según tus necesidades (ver "Convenciones").
- Define y registra el Dataset en el módulo/manifest de la tienda para que quede disponible en la interfaz y en la API.

Convenciones de nombres

- Clase/Modelo: PascalCase (por ejemplo, MiDataset).
- Tabla en BD: snake_case plural (por ejemplo, mi_datasets).
- Campos: snake_case (por ejemplo, fecha_creacion, usuario_id).
- Slug/identificador público: kebab-case (por ejemplo, mi-dataset).

Estructura recomendada

- Metadatos: nombre, slug, descripción corta, versión, autor/contacto.
- Esquema de datos: lista de campos con nombre, tipo (string, integer, boolean, date, json, etc.), nullabilidad y valores por defecto.
- Relaciones: definir relaciones belongsTo/hasMany/hasOne con otras tablas (indicando FK).
- Índices: declarar índices para campos de búsqueda/clave foránea.

Migraciones y esquema

- Crear una migración que cree la tabla con todos los campos y constricciones.
- Incluir claves foráneas y ON DELETE/ON UPDATE según la lógica de negocio.
- Añadir índices para consultas frecuentes.

Validaciones y tipos

- Especificar validaciones de servidor (requerido, formato, rango) y tipos esperados.
- Mapear validaciones a los formularios de la UI para feedback consistente.

Registro en la tienda (store)

- Añadir el Dataset al listado/manifest de Datasets para que la tienda lo reconozca.
- Proveer endpoints o adaptadores para CRUD según la arquitectura del proyecto.
- Incluir permisos/roles necesarios para acceder o modificar el Dataset.

Ejemplo de placeholders (sustituir por reales)

- Nombre clase: MiDataset
- Tabla: mi_datasets
- Campos: id (pk), titulo (string), descripcion (text), publicado (boolean), creado_en (datetime), usuario_id (fk)

Pruebas y verificación

- Crear pruebas unitarias para validaciones y modelos.
- Probar migraciones en una copia de la BD y verificar integridad referencial.
- Verificar registro y visibilidad en la interfaz de la tienda.

Buenas prácticas

- Mantener la documentación dentro del propio Dataset (metadatos) para facilitar su descubrimiento.
- Versionar cambios en el esquema y documentar migraciones incompatibles.
- Evitar cambios breaking en campos públicos sin migraciones y comunicaciones a consumidores.

Notas finales
Asegúrate de adaptar los nombres y tipos a la lógica de tu dominio. Esta plantilla es una guía; revisa las políticas de la base de datos, seguridad y permisos de tu proyecto antes de publicar el Dataset.

¡Con esta plantilla y guía, deberías estar listo para crear un nuevo Dataset que se integre perfectamente en tu proyecto!
