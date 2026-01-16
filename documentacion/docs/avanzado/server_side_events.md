# ⏳ Procesos de Larga Duración con Server-Side Events (SSE)

El framework Ragnos incluye una implementación para manejar procesos de larga duración en el servidor, permitiendo enviar actualizaciones de progreso en tiempo real al navegador del usuario utilizando **Server-Side Events (SSE)**.

Esta funcionalidad es ideal para tareas que exceden el tiempo de espera habitual de una petición HTTP o donde el usuario necesita retroalimentación visual del avance (barras de progreso).

!!! warning "Límites de tiempo en PHP"
Aunque SSE permite mantener la conexión abierta, tu servidor web o configuración de PHP (`max_execution_time`) podría matar el proceso si dura demasiado. Asegúrate de configurar `set_time_limit(0)` en tu lógica si esperas procesos muy largos.

## Arquitectura

El sistema se basa en dos componentes principales:

1.  **`App\ThirdParty\Ragnos\Controllers\RProcessController`**: Una clase base para los controladores que ejecutarán procesos.
2.  **`process_helper.php`**: Un helper que contiene las funciones para gestionar el flujo de eventos SSE (cabeceras, envío de datos, control de bufer, etc.).

### Flujo de Trabajo

1.  El usuario accede a una acción del controlador que renderiza la vista de progreso (método `showProgress`).
2.  La vista conecta mediante JavaScript (`EventSource`) al método `start` del mismo controlador.
3.  El método `start` ejecuta la lógica pesada, enviando eventos de progreso periódicamente mediante las funciones del helper.
4.  Al finalizar, el servidor envía un evento de finalización y cierra la conexión.

---

## Clase RProcessController

Para crear un nuevo proceso, debes crear un controlador que extienda de `RProcessController`.

**Ubicación**: `App\Controllers\` o cualquier subcarpeta, siempre que el namespace sea correcto.

### Métodos Principales

- **`__construct()`**: Carga automáticamente el `process_helper`.
- **`showProgress()`**: Muestra la interfaz de usuario con la barra de progreso. Calcula automáticamente la URL del endpoint `start` basándose en el nombre de la clase.
- **`start()`**: **Debe ser sobreescrito**. Aquí reside la lógica de tu proceso larga duración.

---

## Funciones del Helper (process_helper)

Estas funciones están disponibles automáticamente dentro de tu controlador extendido.

### `processStart($title = 'Processing...')`

Inicializa el proceso SSE.

- Limpia buffers de salida y establece las cabeceras `text/event-stream`.
- Deshabilita límites de tiempo (`set_time_limit(0)`) y memoria en PHP para evitar timeouts.
- Envía el título del proceso al cliente.

### `setProgress($percentage)`

Actualiza la barra de progreso al porcentaje indicado.

- `$percentage`: Número entero (0-100).

### `setProgressText($text)`

Actualiza el texto descriptivo debajo de la barra de progreso. Útil para informar al usuario qué se está haciendo exactamente (ej: "Procesando fila 45...").

### `setProgressOf($currentStep, $total)`

Una función de utilidad que calcula el porcentaje automáticamente y llama a `setProgress`.

- `$currentStep`: El paso actual (iterador).
- `$total`: El total de pasos a realizar.

### `endProcess($additionalData = null)`

Finaliza el proceso.

- Calcula el tiempo total de ejecución.
- Envía el evento de finalización al cliente con datos opcionales.
- Termina la ejecución del script (`exit`).

---

## Aplicaciones Comunes

Esta funcionalidad es útil para:

- **Importaciones Masivas**: Carga de archivos Excel/CSV con miles de registros a la base de datos.
- **Generación de Reportes**: Creación de PDFs complejos o excels grandes.
- **Mantenimiento**: Tareas de limpieza, recálculo de precios, regeneración de caché o imágenes.
- **Envío de Correos Masivos**: Envío de newsletters donde se requiere feedback de cada bloque enviado.

---

## Ejemplos de Implementación

### Ejemplo 1: Recálculo de Precios (Básico)

Este ejemplo simula un proceso que itera sobre 100 elementos.

```php
<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

class RecalculoPrecios extends RProcessController
{
    // Método principal que ejecuta la lógica
    public function start()
    {
        // 1. Iniciar el proceso
        processStart('Recalculando precios del catálogo');

        $totalProductos = 100;

        for ($i = 1; $i <= $totalProductos; $i++) {
            // Simulamos trabajo pesado
            usleep(100000); // 0.1 segundos

            // 2. Actualizar progreso (calcula % automáticamente)
            setProgressOf($i, $totalProductos);

            // 3. Actualizar texto informativo
            setProgressText("Actualizando producto ID: #{$i}");
        }

        // 4. Finalizar
        endProcess([
            'mensaje' => 'Se han actualizado los precios correctamente',
            'total'   => $totalProductos
        ]);
    }
}
```

### Ejemplo 2: Importación de Usuarios (Con validación)

Un ejemplo más complejo simulando una importación donde se valida información.

```php
<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

class ImportarUsuarios extends RProcessController
{
    public function start()
    {
        processStart('Importando Base de Datos de Usuarios');

        // Simular obtención de datos (ej. leer un CSV)
        $usuarios = $this->obtenerDatosSimulados(500);
        $total = count($usuarios);
        $importados = 0;
        $errores = 0;

        foreach ($usuarios as $index => $usuario) {
            $paso = $index + 1;

            // Lógica de negocio simulada
            if ($this->guardarUsuario($usuario)) {
                $importados++;
                setProgressText("Usuario {$usuario['nombre']} importado.");
            } else {
                $errores++;
                setProgressText("Error importando {$usuario['nombre']}.");
            }

            // Actualizar barra de progreso cada 5 registros para no saturar la red
            // en procesos muy rápidos
            if ($paso % 5 === 0 || $paso === $total) {
                setProgressOf($paso, $total);
            }

            // Simular tiempo de base de datos
            usleep(20000);
        }

        endProcess([
            'resultado' => "Importación finalizada. Éxito: {$importados}, Errores: {$errores}"
        ]);
    }

    private function obtenerDatosSimulados($qty) { /* ... */ return array_fill(0, $qty, ['nombre' => 'User']); }
    private function guardarUsuario($user) { return rand(0, 10) > 1; }
}
```

### Ejemplo 3: Generación de Copia de Seguridad

```php
<?php

namespace App\Controllers\Admin;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

class BackupSystem extends RProcessController
{
    public function start()
    {
        processStart('Generando Backup del Sistema');

        $steps = [
            'Comprimiendo imágenes...',
            'Exportando base de datos...',
            'Generando archivos de log...',
            'Empaquetando ZIP final...'
        ];

        $totalSteps = count($steps);

        foreach ($steps as $index => $stepName) {
            setProgressText($stepName);

            // Simular tarea larga
            sleep(2);

            setProgressOf($index + 1, $totalSteps);
        }

        endProcess(['file' => 'backup_2023.zip']);
    }
}
```
