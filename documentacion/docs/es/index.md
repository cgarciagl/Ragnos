# Ragnos Framework

![Image](https://github.com/cgarciagl/Ragnos/blob/main/content/img/logo.webp?raw=true){ width="300" }

Ragnos es un framework moderno y ligero para el desarrollo de aplicaciones web. Su objetivo es proporcionar una base sólida y flexible para construir aplicaciones escalables y de alto rendimiento. Está basado en **CodeIgniter 4**, lo que garantiza un núcleo robusto y probado en el tiempo. Su interfaz utiliza **JavaScript nativo**, **DataTables 3** sin jQuery y **AdminLTE** para ofrecer una experiencia de desarrollo completa y eficiente.

!!! tip "Prerrequisitos Recomendados"

    Aunque Ragnos facilita mucho el desarrollo, **es muy recomendable** familiarizarse con **[CodeIgniter 4](https://codeigniter.com/user_guide/index.html)** y tener conocimientos básicos de **SQL** antes de iniciar. Esto te permitirá aprovechar al máximo la flexibilidad del framework y entender mejor lo que ocurre "bajo el capó".

## Características

- **Ligero y rápido**: Diseñado para ser eficiente y minimizar el uso de recursos.
- **Modular**: Estructura modular que permite añadir o quitar componentes según sea necesario.
- **Escalable**: Ideal para proyectos pequeños y grandes, con capacidad de crecer según las necesidades.
- **Fácil de usar**: Sintaxis clara y documentación completa para facilitar el desarrollo.
- **Basado en CodeIgniter 4**: Aprovecha las ventajas de un framework PHP moderno, con soporte para namespaces, controladores, modelos y vistas.
- **JavaScript nativo**: Manipula el DOM, realiza solicitudes asíncronas y gestiona eventos sin depender de jQuery.
- **Uso de DataTables 3**: Permite crear tablas dinámicas sin jQuery, con búsqueda global y por campo, paginación, ordenamiento y selección individual.
- **Diseño con AdminLTE**: Proporciona una interfaz de usuario moderna y responsiva, ideal para paneles de administración y aplicaciones web.

!!! check "Flexibilidad Frontend"

    Ragnos no requiere **jQuery**. Su núcleo frontend utiliza APIs estándar del navegador y puede convivir con bibliotecas reactivas como **Alpine.js**, **Vue.js** o **React**. También puedes construir interfaces totalmente personalizadas consumiendo los datos mediante el **Modo API** de Ragnos.

## Funcionalidades y beneficios

1. **Desarrollo rápido**: Gracias a su estructura basada en CodeIgniter 4, Ragnos permite a los desarrolladores centrarse en la lógica de negocio sin preocuparse por configuraciones complejas.
2. **Interfaz moderna**: AdminLTE ofrece un diseño atractivo y responsivo, asegurando que las aplicaciones se vean bien en cualquier dispositivo.
3. **Gestión de datos eficiente**: DataTables facilita la visualización y manipulación de grandes conjuntos de datos de manera interactiva.
4. **Flexibilidad**: Su arquitectura modular permite personalizar el framework según las necesidades específicas del proyecto.
5. **Comunidad y soporte**: Al estar basado en tecnologías ampliamente utilizadas como CodeIgniter, Bootstrap y JavaScript estándar, los desarrolladores tienen acceso a una gran cantidad de recursos y soporte en línea.

## Organización de la Documentación

Esta documentación está dividida en cuatro secciones principales para facilitar tu aprendizaje:

**🔰 Fundamentos**
Todo lo necesario para empezar. Comienza con la [Instalación](fundamentos/instalacion.md), revisa la [Configuración](fundamentos/configuracion.md) y crea tu primer módulo en [Primeros Pasos](fundamentos/primeros_pasos.md).

**💾 Datasets y Modelos**
El núcleo de Ragnos. Aprende a declarar [Datasets](datasets/datasets.md), definir [Campos](datasets/campos.md) y manejar relaciones [Maestro-Detalle](datasets/maestro-detalle.md).

**🚀 Funcionalidades Avanzadas**
Para usuarios intermedios. Profundiza en el [Modo API](avanzado/guia_modo_api.md), sistema de [Hooks](avanzado/hooks.md) y [Despliegue en Producción](avanzado/despliegue.md).

**🎨 Frontend y UI**
Personalización de la interfaz. Aprende a modificar [Menús o Temas](frontend/personalizacion_ui.md) y generar [Reportes](frontend/reportes_simples.md).

!!! info "Demo en vivo"

    Para ver Ragnos en acción con la base de datos de ejemplo **Classicmodels**, visita la sección [Base de Datos Demo](fundamentos/base_de_datos_demo.md) donde encontrarás credenciales de acceso y una explicación detallada de los datos.
