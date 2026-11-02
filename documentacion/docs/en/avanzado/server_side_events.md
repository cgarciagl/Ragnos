# ⏳ Long Running Processes with Server-Side Events (SSE)

The Ragnos framework includes an implementation for handling long-running processes on the server, sending real-time progress updates to the user's browser through **Server-Side Events (SSE)**.

This is ideal for tasks that exceed the usual HTTP request timeout, or where the user needs visual feedback on progress (progress bars).

!!! warning "PHP Time Limits"

    Although SSE keeps the connection open, the web server or the PHP configuration (`max_execution_time`) might kill the process if it runs too long. Make sure your logic calls `set_time_limit(0)` if you expect very long processes.

## Architecture

The system is based on two main components:

1.  **`Ragnos\Controllers\RProcessController`**: A base class for the controllers that run processes.
2.  **`process_helper.php`**: A helper with the functions that manage the SSE event flow (headers, sending data, buffer control, etc.).

### Workflow

1.  The user opens the controller action that renders the progress view (the `showProgress` method).
2.  The view connects through JavaScript (`EventSource`) to the `start` method of the same controller.
3.  The `start` method runs the heavy logic, periodically sending progress events through the helper functions.
4.  When it finishes, the server sends a completion event and closes the connection.

---

## RProcessController Class

To create a new process, create a controller that extends `RProcessController`.

**Location**: `App\Controllers\` or any subfolder, as long as the namespace is correct.

### Main Methods and Properties

- **`$requireConfirmation`**: Boolean property (default `false`). If set to `true`, a confirmation dialog will appear before the process starts, warning the user.
- **`$confirmationMessage`**: String property to set a custom message for the confirmation dialog if `$requireConfirmation` is `true`. By default, it uses a generic message defined in the framework's language files.
- **`__construct()`**: Loads `process_helper` automatically.
- **`showProgress()`**: Shows the user interface with the progress bar. It works out the URL of the `start` endpoint automatically from the class name.
- **`start()`**: **Must be overridden**. This is where the logic of the long-running process lives.

---

## Helper Functions (process_helper)

These functions are available automatically inside any controller that extends `RProcessController`.

### `processStart($title = 'Processing...')`

Initializes the SSE process.

- Clears the output buffers and sets the `text/event-stream` headers.
- Disables PHP's time limit (`set_time_limit(0)`) and adjusts the memory settings to avoid timeouts.
- Sends the process title to the client.

### `setProgress($percentage)`

Moves the progress bar to the given percentage.

- `$percentage`: An integer (0-100).

### `setProgressText($text)`

Updates the descriptive text below the progress bar. It is useful for telling the user exactly what is happening (e.g. "Processing row 45...").

### `setProgressOf($currentStep, $total)`

A utility function that calculates the percentage automatically and calls `setProgress`.

- `$currentStep`: The current step (iterator).
- `$total`: The total number of steps.

### `endProcess($additionalData = null)`

Ends the process.

- Calculates the total execution time.
- Sends the completion event to the client, with optional data.
- Ends the script execution (`exit`).

---

## Common Applications

This functionality is useful for:

- **Bulk Imports**: Loading Excel/CSV files with thousands of records into the database.
- **Report Generation**: Creating complex PDFs or large Excel files.
- **Maintenance**: Cleanup tasks, price recalculation, or regenerating caches and images.
- **Mass Emails**: Sending newsletters that report progress for each block sent.

---

## Implementation Examples

### Example 1: Price Recalculation (Basic)

This example simulates a process that iterates over 100 items.

```php
<?php

namespace App\Controllers;

use Ragnos\Controllers\RProcessController;

class RecalculatePrices extends RProcessController
{
    // Define if the process needs a previous confirmation prompt (Optional)
    public $requireConfirmation = true;
    public $confirmationMessage = 'Are you totally sure you want to recalculate prices? This action cannot be undone.';

    // Main method executing logic
    public function start()
    {
        // 1. Start process
        processStart('Recalculating catalog prices');

        $totalProducts = 100;

        for ($i = 1; $i <= $totalProducts; $i++) {
            // Simulate heavy work
            usleep(100000); // 0.1 seconds

            // 2. Update progress (calculates % automatically)
            setProgressOf($i, $totalProducts);

            // 3. Update info text
            setProgressText("Updating product ID: #{$i}");
        }

        // 4. Finish
        endProcess([
            'message' => 'Prices updated successfully',
            'total'   => $totalProducts
        ]);
    }
}
```

### Example 2: User Import (With Validation)

A more complex example that simulates an import where the data is validated.

```php
<?php

namespace App\Controllers;

use Ragnos\Controllers\RProcessController;

class ImportUsers extends RProcessController
{
    public function start()
    {
        processStart('Importing User Database');

        // Simulate data fetching (e.g. read CSV)
        $users = $this->getSimulatedData(500);
        $total = count($users);
        $imported = 0;
        $errors = 0;

        foreach ($users as $index => $user) {
            $step = $index + 1;

            // Simulated business logic
            if ($this->saveUser($user)) {
                $imported++;
                setProgressText("User {$user['name']} imported.");
            } else {
                $errors++;
                setProgressText("Error importing {$user['name']}.");
            }

            // Update progress bar every 5 records to not saturate network
            // in very fast processes
            if ($step % 5 === 0 || $step === $total) {
                setProgressOf($step, $total);
            }

            // Simulate DB time
            usleep(20000);
        }

        endProcess([
            'result' => "Import finished. Success: {$imported}, Errors: {$errors}"
        ]);
    }

    private function getSimulatedData($qty) { /* ... */ return array_fill(0, $qty, ['name' => 'User']); }
    private function saveUser($user) { return rand(0, 10) > 1; }
}
```

### Example 3: Backup Generation

```php
<?php

namespace App\Controllers\Admin;

use Ragnos\Controllers\RProcessController;

class BackupSystem extends RProcessController
{
    public function start()
    {
        processStart('Generating System Backup');

        $steps = [
            'Compressing images...',
            'Exporting database...',
            'Generating log files...',
            'Packaging final ZIP...'
        ];

        $totalSteps = count($steps);

        foreach ($steps as $index => $stepName) {
            setProgressText($stepName);

            // Simulate long task
            sleep(2);

            setProgressOf($index + 1, $totalSteps);
        }

        endProcess(['file' => 'backup_2023.zip']);
    }
}
```
