# Conceptos fundamentales

## ¿Qué es RDatasetController?

RDatasetController es el núcleo de Ragnos para definir datasets de forma declarativa. Un dataset describe la estructura y comportamiento de una entidad sin implementar lógica CRUD explícita: solo metadata que el framework transforma en UI, validación y persistencia.

## Qué define un dataset

- Tabla de base de datos
- Clave primaria
- Campos (persistentes y virtuales)
- Reglas de validación
- Relaciones reutilizables (selectores/búsquedas)
- Hooks del ciclo de vida (antes/después de operaciones)

## Ventajas

- Centraliza metadata: menos código repetido.
- UI y validación generadas automáticamente.
- Reutilización de relaciones con addSearch.
- Hooks para integración (cache, logs, servicios externos).

## Componentes principales

- setTableName, setIdField, setAutoIncrement
- addField(name, options): label, rules, type, query, etc.
- addSearch(localField, 'Namespace\\Dataset')
- setTableFields([...]) para grilla/listado
- Hooks: \_beforeInsert/\_afterInsert, \_beforeUpdate/\_afterUpdate, \_beforeDelete/\_afterDelete

## Ciclo de vida (ejemplo de uso)

- Validar inputs (reglas declaradas)
- Ejecutar \_before\* para transformar/autorizar
- Persistir (insert/update/delete)
- Ejecutar \_after\* para limpiar cache o notificar

## Cómo crear uno (pasos rápidos)

1. Diseñar la tabla en BD.
2. Crear controller que extienda RDatasetController.
3. Configurar tabla, PK y campos en el constructor.
4. Definir relaciones con addSearch.
5. Implementar hooks si necesita lógica adicional fuera del CRUD básico.

## Buenas prácticas

- Un dataset = una tabla principal.
- Usar campos virtuales (query) para mejorar UX sin alterar esquema.
- Centralizar validaciones en rules.
- Evitar lógica compleja en hooks; delegar a servicios si es necesario.

## Ejemplo mínimo

```php
class Clientes extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('customers');
        $this->setIdField('customerNumber');
        $this->addField('customerName',['label'=>'Nombre','rules'=>'required']);
        $this->setTableFields(['customerName','Contacto']);
    }
}
```
