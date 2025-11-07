# Controlador Proceso

El controlador `Proceso` gestiona la ejecución de procesos en segundo plano, como tareas largas o repetitivas.

## Configuración

Este controlador extiende de `RProcessController` y utiliza funciones específicas para manejar el progreso y la finalización de procesos.

## Funciones

### start()

Inicia un proceso que recalcula precios. Realiza las siguientes acciones:

- Llama a `processStart` para inicializar el proceso con el mensaje "Recalculando precios".
- Itera sobre 100 elementos, actualizando el progreso y mostrando el texto correspondiente.
- Llama a `endProcess` al finalizar, indicando que el proceso se completó exitosamente y mostrando el número de elementos procesados.

## Constructor

El constructor de este controlador no tiene configuraciones adicionales específicas.
