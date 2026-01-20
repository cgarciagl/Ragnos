# Getting Started: Hello World

In this tutorial, we will create a complete functional module (CRUD) to manage a list of "Tasks" in less than 5 minutes.

## 1. Prepare the Database

First, we need a physical table to store the data. Execute this SQL in your database:

```sql
CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 2. Generate the Controller

Open your terminal in the project root folder and use the Ragnos generator:

```bash
php spark ragnos:make Process/Tasks -table tasks
```

This will create the file `app/Controllers/Process/Tasks.php`.

## 3. Configure the Dataset

Open the newly created file `app/Controllers/Process/Tasks.php` and complete the configuration in the constructor. The generator will have created a basic structure; edit it to look like this:

```php
namespace App\Controllers\Process;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Tasks extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        // 1. Title and Security
        $this->checkLogin(); // Requires being logged in
        $this->setTitle('Task Management');

        // 2. Table Configuration
        $this->setTableName('tasks');
        $this->setIdField('task_id');

        // 3. Field Definition
        $this->addField('title', [
            'label' => 'Task Title',
            'rules' => 'required|min_length[5]'
        ]);

        $this->addField('description', [
            'label' => 'Detail',
            'type'  => 'textarea'
        ]);

        $this->addField('due_date', [
            'label' => 'Due Date',
            'type'  => 'date'
        ]);

        $this->addField('status', [
            'label' => 'Current Status',
            'type'  => 'select', // Or 'enum'
            'options' => [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed'
            ]
        ]);

        // 4. Configure Grid (Listing)
        $this->setTableFields(['title', 'status', 'due_date']);
    }
}
```

## 4. Test in Browser

1. Open your browser and log into the system.
2. Manually navigate to your controller's URL. If your base URL is `localhost/ragnos`, go to:
   `http://localhost/ragnos/process/tasks`

Ready! You should see:

- A list of tasks (empty for now).
- A "New" button that opens a form.
- Complete functionality to Save, Edit, and Delete.

## Next Steps

- Add this new module to the side menu (see [UI Customization](../frontend/personalizacion_ui.md)).
- Learn about more field types in [Field Reference](../datasets/campos.md).
