# Formfy Laravel Form Generator

Formfy is a laravel package for easy form creation. It allows developers to build forms quickly, bind 
models, handle errors, and customize fields effortlessly.

---

## Installation

You can install the package via Composer:

```bash
composer require formfy/easy-laravel-form
```

## Create your first form
Import the package for the form database builder.

```php
use Kian\EasyLaravelForm\DBFormBuilder
```

**StudentForm.php**

This student extends the DBFormBuilder class that generate the forms.

```php
class StudentForm extends DBFormBuilder
{
    public function __construct(string $action = '', ?Model $model = null, $method = 'POST', $errors = [])
    {

        if (session()->has('errors') && session('errors') instanceof MessageBag) {
            $this->errors = session('errors')->getBag('default')->getMessages();
        }

        parent::__construct($action, $method, $errors, $model);
        $this->studentForm();
    }


    public function studentForm(): void
    {

        $this->addField('text', 'firstname', 'Firstname', ['placeholder' => 'Enter your firstname'])
            ->addField('text', 'middlename', 'Middlename', ['placeholder' => 'Enter your middlename'])
            ->addField('text', 'lastname', 'Lastname', ['placeholder' => 'Enter your lastname'])
            ->addField('text', 'age', 'Age', ['placeholder' => 'Enter your age'])
            ->addField('text', 'address', 'Permanent Address', ['placeholder' => 'Enter your permanent address'])
            ->addField('select', 'gender', 'Gender', ['options' => ['Male' => 'Male', 'Female' => 'Female']])
            ->addField('select', 'extension', 'Extension', ['options' => ['' => 'None', 'Jr' => 'Jr.', 'Sr' => 'Sr.']])
            ->addField('text', 'student_id', 'Student ID', ['placeholder' => 'Enter your student id'])
            ->setSubmitLabel('Submit');
    }
}
?>
```


**StudentController.php**

```php
<?php
    public function create(): View
    {
        $form = new StudentForm(route('students.store'));
        $studentForm = $form->render();
        return view('student.create',compact('studentForm'));
    }
?>
```

**create.blade.php**

```php
{!! $studentForm !!}
```
