# Ragnos CLI Generator

El **Generador CLI de Ragnos** es una herramienta de línea de comandos integrada en Spark (CodeIgniter 4) que permite crear esqueletos de [`RDatasetController`](../datasets/datasets.md) en segundos.

Puedes ver este generador en acción en la guía de [Primeros Pasos](primeros_pasos.md).

---

## 📋 Tabla de Contenidos

1. [Instalación](#instalacion)
2. [Sintaxis del Comando](#sintaxis-del-comando)
3. [Ejemplos de Uso](#ejemplos-de-uso)
4. [Mapeo Inteligente de Tipos](#mapeo-inteligente-de-tipos)
5. [Beneficios](#beneficios)

---

## 🛠 Instalación {: #instalacion }

El Generador CLI de Ragnos viene preinstalado con el paquete Ragnos. Solo asegúrate de tener Ragnos correctamente instalado en tu proyecto CodeIgniter 4.

1. Verifica que la disponibilidad del comando ejecutando:

```bash
php spark list
```

Deberías ver el grupo `Ragnos` y el comando `ragnos:make`.

---

## 💻 Sintaxis del Comando

Desde la raíz de tu proyecto:

```bash
php spark ragnos:make [NombreControlador] [Opciones]
```

### Argumentos

| Argumento           | Descripción                                                |
| :------------------ | :--------------------------------------------------------- |
| `NombreControlador` | La ruta y nombre de la clase (ej. `Inventario/Productos`). |

### Opciones

| Opción   | Descripción                      |
| :------- | :------------------------------- |
| `-table` | Nombre exacto de la tabla en BD. |

---

## 🚀 Ejemplos de Uso

### 1. Uso Básico (Autodetección)

Si tu controlador es `Productos` y tabla `productos`:

```bash
php spark ragnos:make Tienda/Productos
```

### 2. Uso con Tabla Específica

```bash
php spark ragnos:make Admin/Usuarios -table app_users_tbl
```

---

## 🧠 Mapeo Inteligente de Tipos

Ragnos elige el componente según tu BD:

| Tipo BD      | Tipo Ragnos | Reglas Auto-generadas       |
| :----------- | :---------- | :-------------------------- |
| `INT`        | `number`    | `required \| integer`       |
| `DECIMAL`    | `money`     | `required \| decimal`       |
| `DATE`       | `date`      | `required`                  |
| `TEXT`       | `textarea`  | `required`                  |
| `TINYINT(1)` | `checkbox`  | `permit_empty`              |
| `VARCHAR`    | `text`      | `required \| max_length[n]` |

---

## ⭐ Beneficios

1. **Velocidad:** Crea un CRUD en 10 segundos.
2. **Estandarización:** Evita errores de copy-paste y namespaces.
3. **Limpieza:** Genera etiquetas legibles ("Fecha Alta" en lugar de "fecha_alta").

# Plantilla oficial de nuevo Dataset

Si quieres crear un nuevo Dataset desde cero, sin utilizar la línea de comandos, utiliza esta plantilla como guía para definir la estructura y convenciones recomendadas.

```php
<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class EjemploDataset extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->checkLogin();
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

## 📄 Documentación de la plantilla de Dataset

### 🎯 Propósito

Esta plantilla sirve como punto de partida para crear un nuevo Dataset que se integrará en la tienda (store) del proyecto. Proporciona la estructura, convenciones y campos mínimos recomendados para que el Dataset funcione correctamente en el flujo existente.

### 📝 Instrucciones básicas

1. **Duplicar:** Copia este archivo y colócalo en la ruta adecuada del repositorio para el nuevo Dataset.
2. **Ajustar:** Modifica los nombres de clases, tablas y campos según tus necesidades (ver "Convenciones").
3. **Registrar:** Define el Dataset en el módulo/manifest de la tienda para que quede disponible en la interfaz y en la API.

### 🏷 Convenciones de nombres

- **Clase/Modelo:** `PascalCase` (por ejemplo, `MiDataset`).
- **Tabla en BD:** `snake_case` plural (por ejemplo, `mi_datasets`).
- **Campos:** `snake_case` (por ejemplo, `fecha_creacion`, `usuario_id`).
- **Slug/identificador público:** `kebab-case` (por ejemplo, `mi-dataset`).

### 🏗 Estructura recomendada

- **Metadatos:** Nombre, slug, descripción corta, versión, autor/contacto.
- **Esquema de datos:** Lista de campos con nombre, tipo (`string`, `integer`, `boolean`, `date`, `json`, etc.), nulabilidad y valores por defecto.
- **Relaciones:** Definir relaciones `belongsTo`/`hasMany`/`hasOne` con otras tablas (indicando FK).
- **Índices:** Declarar índices para campos de búsqueda y claves foráneas.

### 🗄 Migraciones y esquema

- **Migración:** Crear una migración que genere la tabla con todos los campos y restricciones.
- **Claves foráneas:** Incluir `ON DELETE`/`ON UPDATE` según la lógica de negocio.
- **Optimización:** Añadir índices para consultas frecuentes.

### ✅ Validaciones y tipos

- **Servidor:** Especificar validaciones (requerido, formato, rango) y tipos esperados.
- **UI:** Mapear validaciones a los formularios de la interfaz para ofrecer feedback consistente.

### 🛒 Registro en la tienda (store)

- **Manifest:** Añadir el Dataset al listado de Datasets para que la tienda lo reconozca.
- **API:** Proveer endpoints o adaptadores para CRUD según la arquitectura del proyecto.
- **Seguridad:** Incluir permisos y roles necesarios para acceder o modificar el Dataset.

### 🧩 Ejemplo de placeholders (sustituir por reales)

- **Nombre clase:** `MiDataset`
- **Tabla:** `mi_datasets`
- **Campos:**
  - `id` (PK)
  - `titulo` (string)
  - `descripcion` (text)
  - `publicado` (boolean)
  - `creado_en` (datetime)
  - `usuario_id` (FK)

### 🧪 Pruebas y verificación

1.  Crear **pruebas unitarias** para validaciones y modelos.
2.  Probar **migraciones** en una copia de la BD y verificar la integridad referencial.
3.  Verificar el **registro y visibilidad** en la interfaz de la tienda.

### 💡 Buenas prácticas

- **Autodocumentación:** Mantener la documentación dentro del propio Dataset (metadatos) para facilitar su descubrimiento.
- **Versionado:** Controlar cambios en el esquema y documentar migraciones incompatibles.
- **Compatibilidad:** Evitar cambios _breaking_ en campos públicos sin migraciones previas y comunicación a los consumidores.

> **Notas finales:** Asegúrate de adaptar los nombres y tipos a la lógica de tu dominio. Esta plantilla es una guía; revisa las políticas de la base de datos, seguridad y permisos de tu proyecto antes de publicar el Dataset.

¡Con esta plantilla y guía, deberías estar listo para crear un nuevo Dataset que se integre perfectamente en tu proyecto!
