# ⏳ Long Running Processes with Server-Side Events (SSE)

Ragnos framework includes implementation for handling long-running processes on server, allowing real-time progress updates to user browser using **Server-Side Events (SSE)**.

Ideal for tasks exceeding usual HTTP request timeout or where user needs visual feedback of progress (progress bars).

!!! warning "PHP Time Limits"

    Although SSE keeps connection open, web server or PHP config (`max_execution_time`) might kill process if too long. Ensure `set_time_limit(0)` in logic if expecting very long processes.

## Architecture

System based on two main components:

1.  **`App\ThirdParty\Ragnos\Controllers\RProcessController`**: Base class for controllers executing processes.
2.  **`process_helper.php`**: Helper containing functions managing SSE event flow (headers, sending data, buffer control, etc.).

### Workflow

1.  User accesses controller action rendering progress view (method `showProgress`).
2.  View connects via JavaScript (`EventSource`) to `start` method of same controller.
3.  `start` method executes heavy logic, sending progress events periodically via helper functions.
4.  Upon completion, server sends finish event and closes connection.

---

## RProcessController Class

To create new process, must create controller extending `RProcessController`.

**Location**: `App\Controllers\` or any subfolder, provided namespace correct.

### Main Methods

- **`__construct()`**: Automatically loads `process_helper`.
- **`showProgress()`**: Shows UI with progress bar. Automatically calculates URL of `start` endpoint based on class name.
- **`start()`**: **Must be overridden**. Logic of long running process resides here.

---

## Helper Functions (process_helper)

These functions available automatically inside extended controller.

### `processStart($title = 'Processing...')`

Initializes SSE process.

- Clears output buffers and sets `text/event-stream` headers.
- Disables time limits (`set_time_limit(0)`) and memory in PHP to avoid timeouts.
- Sends process title to client.

### `setProgress($percentage)`

Updates progress bar to indicated percentage.

- `$percentage`: Integer (0-100).

### `setProgressText($text)`

Updates descriptive text below progress bar. Useful to inform user what exactly is being done (e.g. "Processing row 45...").

### `setProgressOf($currentStep, $total)`

Utility function calculating percentage automatically and calling `setProgress`.

- `$currentStep`: Current step (iterator).
- `$total`: Total steps to perform.

### `endProcess($additionalData = null)`

Finalizes process.

- Calculates total execution time.
- Sends finish event to client with optional data.
- Ends script execution (`exit`).

---

## Common Applications

Useful for:

- **Bulk Imports**: Uploading Excel/CSV files with thousands of records to DB.
- **Report Generation**: Creating complex PDFs or large excels.
- **Maintenance**: Cleanup tasks, price recalculation, cache or image regeneration.
- **Mass Emails**: Sending newsletters requiring feedback per sent block.

---

## Implementation Examples

### Example 1: Price Recalculation (Basic)

Simulates process iterating over 100 items.

```php
<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

class RecalculatePrices extends RProcessController
{
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

More complex example simulating import where info validated.

```php
<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

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

use App\ThirdParty\Ragnos\Controllers\RProcessController;

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
