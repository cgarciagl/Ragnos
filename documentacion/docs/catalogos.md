# Controlador Catalogos

El controlador `Catalogos` gestiona las vistas de catálogos del sistema. Utiliza una función privada para renderizar vistas específicas según el catálogo solicitado.

## Configuración

Este controlador extiende de `BaseController` y utiliza las siguientes características:

- Función privada `renderCatalogoView`: Renderiza la vista de un catálogo específico.

## Funciones

### renderCatalogoView(string $controller)

Renderiza la vista del catálogo correspondiente al controlador especificado. Lanza una excepción si el nombre del controlador está vacío.

### usuarios()

Renderiza la vista del catálogo de usuarios.

### gruposdeusuarios()

Renderiza la vista del catálogo de grupos de usuarios.

### canciones()

Renderiza la vista del catálogo de canciones.

### artistas()

Renderiza la vista del catálogo de artistas.

### categorias()

Renderiza la vista del catálogo de categorías.

### oficinas()

Renderiza la vista del catálogo de oficinas.

### empleados()

Renderiza la vista del catálogo de empleados.

### lineas()

Renderiza la vista del catálogo de líneas.

### productos()

Renderiza la vista del catálogo de productos.

### clientes()

Renderiza la vista del catálogo de clientes.

### ordenes()

Renderiza la vista del catálogo de órdenes.

### pagos()

Renderiza la vista del catálogo de pagos.

## Constructor

El constructor de este controlador no tiene configuraciones adicionales específicas.
