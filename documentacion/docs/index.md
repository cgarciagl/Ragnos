# Ragnos Framework

![Image](https://github.com/cgarciagl/Ragnos/blob/main/content/img/logo.webp?raw=true)

Ragnos es un framework moderno y ligero para el desarrollo de aplicaciones web. Su objetivo es proporcionar una base sólida y flexible para construir aplicaciones escalables y de alto rendimiento. Está basado en **CodeIgniter 4**, lo que garantiza un núcleo robusto y probado en el tiempo. Además, utiliza tecnologías como **jQuery**, **DataTables** y **AdminLTE** para ofrecer una experiencia de desarrollo completa y eficiente.

## Características

- **Ligero y rápido**: Diseñado para ser eficiente y minimizar el uso de recursos.
- **Modular**: Estructura modular que permite añadir o quitar componentes según sea necesario.
- **Escalable**: Ideal para proyectos pequeños y grandes, con capacidad de crecer según las necesidades.
- **Fácil de usar**: Sintaxis clara y documentación completa para facilitar el desarrollo.
- **Basado en CodeIgniter 4**: Aprovecha las ventajas de un framework PHP moderno, con soporte para namespaces, controladores, modelos y vistas.
- **Integración con jQuery**: Simplifica la manipulación del DOM, las solicitudes AJAX y la interacción con el usuario.
- **Uso de DataTables**: Permite la creación de tablas dinámicas con funcionalidades como búsqueda, paginación y ordenamiento.
- **Diseño con AdminLTE**: Proporciona una interfaz de usuario moderna y responsiva, ideal para paneles de administración y aplicaciones web.

## Funcionalidades y beneficios

1. **Desarrollo rápido**: Gracias a su estructura basada en CodeIgniter 4, Ragnos permite a los desarrolladores centrarse en la lógica de negocio sin preocuparse por configuraciones complejas.
2. **Interfaz moderna**: AdminLTE ofrece un diseño atractivo y responsivo, asegurando que las aplicaciones se vean bien en cualquier dispositivo.
3. **Gestión de datos eficiente**: DataTables facilita la visualización y manipulación de grandes conjuntos de datos de manera interactiva.
4. **Flexibilidad**: Su arquitectura modular permite personalizar el framework según las necesidades específicas del proyecto.
5. **Comunidad y soporte**: Al estar basado en tecnologías ampliamente utilizadas como CodeIgniter y jQuery, los desarrolladores tienen acceso a una gran cantidad de recursos y soporte en línea.

## Licencia

Ragnos está licenciado bajo la [Licencia MIT](LICENSE).

## Guía rápida de Ragnos

## Crear una aplicación básica con Ragnos — guía paso a paso

1. Diseñe la base de datos primero

- Identifique entidades principales, claves primarias y relaciones (1:N, N:M).
- Modele cada entidad como una tabla clara y normalizada; defina tipos y constraints.
- Cree migrations y seeders (evitan cambios manuales en producción).

2. Mapear cada dataset a una tabla principal

- Un "dataset" en Ragnos representa la tabla principal y su comportamiento CRUD.
- Para tablas relacionadas use claves foráneas y entidades secundarias (detalles/children).

3. Defina campos visibles y buscables con addField / addSearch

- addField: declara columnas, etiquetas, tipo de control y validación para formularios y tablas.
- addSearch: añade filtros que se integran para búsquedas.
- Ejemplo:

```php
 $this->addField('customerNumber', ['label' => 'Cliente', 'rules' => 'required']);

 $this->addSearch('customerNumber', 'Tienda\Clientes');
```

4. Personalice comportamiento con hooks (enganches)

- Hooks permiten inyectar lógica sin modificar el CRUD generado (antes/después de insert/update/delete/render).
- Úselos para: setear valores automáticos (created_by, timestamps), validar o transformar datos, disparar eventos.
- Ejemplo:

```php
  public function _beforeInsert(&$data) {
          $data['created_at'] = date('Y-m-d H:i:s');
          $data['created_by'] = auth()->id();
  }
```

5. Flujo recomendado de trabajo

- 1. Diseñar BD y crear migrations.
- 2. Crear dataset y definir addField/addSearch.
- 3. Añadir hooks para reglas de negocio y seguridad.
- 4. Registrar rutas y permisos.
- 5. Ejecutar migrations, seeders y probar en la UI (DataTables/AdminLTE).

6. Buenas prácticas y consejos rápidos

- Mantenga nombres coherentes (snake_case) y claves foráneas explícitas.
- Valide tanto en frontend (forms) como en backend (reglas del dataset).
- Use transacciones para operaciones compuestas con varios datasets.
- Aproveche seeders para datos de ejemplo y pruebas.
- Revise los documentos enlazados para ejemplos específicos y plantillas.

Ver "Documentación" arriba para ejemplos completos y configuraciones avanzadas.
