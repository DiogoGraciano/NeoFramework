# NeoFramework

NeoFramework is a modern and robust PHP framework that offers a complete structure for web application development. It provides a clean and organized architecture with advanced features for routing, validation, caching, and much more.

## Main Features

- 🚀 Advanced Routing System with Attribute Support
- 🔒 Integrated Security System
- 📧 Email Management
- 📝 Template System
- 💾 Cache and File Storage
- 📋 Data Validation
- 🔐 Session Management
- 📊 Logging System
- 🎨 Asset Bundler
- 🔄 Dependency Injection Container

## Requirements

- PHP 8.0 or higher
- Composer
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
composer create-project neoframework/neoframework your-project-name
```

2. Install dependencies:
```bash
composer install
```

3. Configure your environment:
- Copy the `.env.example` file to `.env`
- Adjust settings as needed
```bash
cp .env.example .env
```

## Project Structure

```
your-project/
├── App/
│   ├── Controllers/    # Application controllers
│   ├── Models/        # Data models
│   ├── View/          # View templates
│   ├── Services/      # Business logic services
│   ├── Middleware/    # Request/Response middleware
│   ├── Helpers/       # Helper functions
│   └── Enums/         # Enumeration classes
├── Config/            # Configuration files
├── Logs/             # Application logs
├── Resources/        # Frontend assets
├── Cache/           # Cache files
├── public/          # Public directory (web root)
└── vendor/          # Composer dependencies
```

## Basic Usage

### Routing with Attributes

```php
use NeoFramework\Core\Attributes\Route;

class UserController
{
    #[Route("index",['GET','POST'])]
    public function index():Response
    {
        // Controller logic

        return $this->response;
    }

    #[Route("show/{:any}/{:num:optional}")]
    public function show(string $srt,int|float $id):Response
    {
        // Controller logic

        return $this->response;
    }
}
```

#### Rewrite of routes

```
your-project/
├── Config/
│   ├── route_rewrite.config.php    # Here you can configure a rewrite of a route
```

```php
<?php 

return [
    "your-route-first-parmeter-path" => "YourRouteController",
];
```

### Middleware System

#### Middleware Class
```php
    public function __construct(
        private bool $Auth = true,
    ) {
    }

    public function before(Controller $controller): Controller
    {
        $response = $controller->getResponse();

        if($Auth){
            $response->addContent("Hello");
        }else{
            $response->go("login");
            $response->send();
        }

        $controller->setResponse($response);

        return $controller;
    }

    public function after(Response $response): Response
    {
        $response->addContent("Bye");

        return $response;
    }
```

#### Controller
```php
use NeoFramework\Core\Attributes\Route;

class UserController
{
    #[Route("index",['GET','POST'])]
    #[Middleware(new Auth(true))]
    public function index()
    {
        // Controller logic
    }

    #[Route("show/{:any}/{:num:optional}")]
    public function show(string $srt,int|float $id)
    {
        // Controller logic
    }
}
```

### Data Validation

```php
use NeoFramework\Core\Validator;
use Respect\Validation\Validator as v;
use NeoFramework\Core\Message;

$validator = new Validator();

$data = [
    'email' => "test@test.com"
    'phone' => "48554115467"
]

$rules = [
    'email' => v::allOf(v::email(), v::uniqueDb(new User, "email")),
    'phone' => v::allOf(v::phone())
]

$messages = [
            'email' => "Email invalid",
            'phone' =>  "Phone invalid",
        ];

$validator->make($this->getArrayData(), $rules, $messages);

if ($validator->hasError()) {
    //set flash message
    Message::setError(...$validator->getErrors());
}
```

## Dependency Injection in NeoFramework

NeoFramework uses [PHP-DI](https://php-di.org/) to handle dependency injection, providing an elegant way to manage class dependencies and promote cleaner, more testable code.

### Automatic Constructor Injection

The framework automatically resolves and injects dependencies declared in your class constructors:

```php
use NeoFramework\Core\Attributes\Route;
use NeoFramework\Core\Attributes\Middleware;
use App\Models\User;

class UserController
{
    public function __construct(private User $user)
    {
        // The User model is automatically injected
    }
    
    #[Route("index", ['GET', 'POST'])]
    #[Middleware(new Auth(true))]
    public function index()
    {
        $this->user->get(1);
        // Controller logic here
    }
    
    #[Route("show/{:any}/{:num:optional}")]
    public function show(string $str, int|float $id)
    {
        // Controller logic here
    }
}
```

### Using PHP-DI Attributes

NeoFramework leverages PHP 8's attribute system combined with PHP-DI's attribute support for more flexible dependency injection:

```php
use DI\Attribute\Inject;

class Example
{
    /**
     * Inject dependency based on property type
     */
    #[Inject]
    private Foo $property1;
    
    /**
     * Inject a named dependency
     */
    #[Inject('db.host')]
    private $property2;
    
    /**
     * Alternative named parameter syntax
     */
    #[Inject(name: 'db.host')]
    private $property3;
    
    /**
     * Combined constructor and attribute injection
     */
    public function __construct(Foo $foo, #[Inject('db.host')] $dbHost)
    {
        // $foo is resolved by type
        // $dbHost is injected from the 'db.host' container entry
    }
    
    /**
     * Method injection using type hints
     */
    #[Inject]
    public function method1(Foo $param)
    {
        // $param is automatically injected
    }
    
    /**
     * Method injection with named parameters
     * Note: #[Inject] must be placed on the method too
     */
    #[Inject]
    public function method2(#[Inject('db.host')] $param)
    {
        // $param receives the 'db.host' value
    }
    
    /**
     * Multiple parameter injection using an array
     */
    #[Inject(['db.host', 'db.name'])]
    public function method3($param1, $param2)
    {
        // $param1 receives 'db.host'
        // $param2 receives 'db.name'
    }
    
    /**
     * Mixed injection with named parameters and type hints
     */
    #[Inject(['param2' => 'db.host'])]
    public function method4(Foo $param1, $param2)
    {
        // $param1 is resolved by type
        // $param2 receives 'db.host'
    }
}
```

## Benefits

- **Simplifies code** by removing manual object instantiation
- **Improves testability** through easier dependency mocking
- **Enhances maintainability** by reducing tight coupling between components
- **Supports autowiring** for automatic resolution based on type hints

For more information, visit the [PHP-DI documentation](https://php-di.org/doc/understanding-di.html).

### Cache

```php
class Company extends model
{
    public function get($value = "", string $column = "id", int $limit = 1, bool $cache = true): array|object
    {
        if ($cache === true && $value == 1 && $column == "id") {
            return Cache::get("company_1", function (ItemInterface $item) use ($value, $column, $limit) {
                $item->expiresAfter(3600);
                $item->tag('company');
                return parent::get($value, $column, $limit);
            });
        }

        return parent::get($value, $column, $limit);
    }

    public function set(): self|null
    {
        //logic 

        if ($this->store()) {
            Message::setSuccess("Successfully saved");
            Cache::delete("company_1");
            return $this;
        }

        return null;
    }
}
```

### Templates

```php
namespace App\View\Layout;

use App\Helpers\Functions;
use NeoFramework\Core\Abstract\Layout;
use NeoFramework\Core\Message as CoreMessage;

class Message extends Layout
{
    public function __construct()
    {
        $this->setTemplate("Message.html");
        
        $Messages = [];

        $Messages[] = CoreMessage::getError();
        $Messages[] = CoreMessage::getSuccess();
        $Messages[] = CoreMessage::getMessage();

        $i = 0;

        foreach ($Messages as $Message){
            foreach ($Message as $text){
                if($text){
                    if ($i == 0){
                        $this->tpl->alert = "#f8d7da";
                    }elseif ($i == 1){
                        $this->tpl->alert = "#d1e7dd";
                    }else{
                        $this->tpl->alert = "#fff3cd";
                    }   
                    $this->tpl->message = $text;
                    $this->tpl->block("BLOCK_MESSAGE");
                }
            }
            $i++;
        }
    }
}

```
```html
<div id="message">
    <div class="fixed left-0 top-3 w-full z-[1000]">
    <!-- BEGIN BLOCK_MESSAGE -->
        <div id="alert-{id}" class="alert mx-2 alert-dismissible mt-1 flex justify-between items-center alert_back p-4 text-sm text-gray-800 rounded-[10px]" role="alert" style="background-color: {alert};">   
            <div>{message}</div>   
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <button type="button" class="btn-close d-none" onclick="document.querySelector('#alert-{id}').remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <!-- END BLOCK_MESSAGE -->
    </div>
</div>
```

### Models

#### Selecting Records

##### Select by ID
```php
// Returns an object with all table columns based on the provided $id
$result = (new Scheduling)->get($id);
```

##### Select by Name
```php
// Returns an object with all table columns based on the provided $name
$result = (new Scheduling)->get($name, "name");
```

##### Select All Records
```php
// Returns an array of objects with all columns and table records
$result = (new Scheduling)->getAll();
```

##### Select with Filters
```php
// Returns an array of objects with all table columns based on the provided filters
$db = new Scheduling;
$results = $db->addFilter("dt_ini", ">=", $dt_start)
              ->addFilter("dt_fim", "<=", $dt_end)
              ->addFilter("id_agenda", "=", intval($id_agenda))
              ->addFilter("status", "!=", $status)
              ->selectAll();
```

##### Select with Joins and Filters
```php
// Returns an array of objects with the specified columns, based on added filters and joins
$db = new Scheduling;
$result = $db->addJoin("LEFT", "user", "user.id", "scheduling.id_user")
             ->addJoin("INNER", "schedule", "schedule.id", "scheduling.id_schedule")
             ->addJoin("LEFT", "client", "client.id", "scheduling.id_client")
             ->addJoin("INNER", "employee", "employee.id", "scheduling.id_employee")
             ->addFilter("schedule.id_company", "=", $id_company)
             ->selectColumns("scheduling.id", "user.cpf_cnpj", "client.name as cli_name", "user.name as user_name", "user.email", "user.phone", "schedule.name as schedule_name", "employee.name as employee_name", "dt_ini", "dt_fim");
```

##### Select with Filters and Limit
```php
// Returns an array of objects with specified columns that match the provided values, based on filters and specified limit
$db = new City;
$result = $db->addFilter("name", "LIKE", "%" . $name . "%")
             ->addLimit(1)
             ->selectByValues(["uf"], [$id_uf], true);
```

##### Insert/Update Records

```php
$values = new Employee;

// If $values->id is null, empty, or 0, it will attempt an INSERT command. Otherwise, it will attempt an UPDATE.
$values->id = null; // or "" or 0
$values->id_user = $id_user;
$values->name = $name;
$values->cpf_cnpj = $cpf_cnpj;
$values->email = $email;
$values->phone = $phone;
$values->hour_ini = $hour_ini;
$values->hour_fim = $hour_fim;
$values->lunch_hour_ini = $lunch_hour_ini;
$values->lunch_hour_fim = $lunch_hour_fim;
$values->days = $days;

// Returns false or the record ID
$return = $values->store();
```

#### Deleting Records

##### Delete by Filter
```php
$db = new Employee;

// Returns true or false
$return = $db->addFilter("name", "=", "Diogo")->deleteByFilter();
```

##### Delete by ID
```php
$id = 1;
$db = new employee;

// Returns true or false
$return = $db->delete($id);
```

#### Using Transactions

```php
    try{   
        connection::beginTransaction();

        if ($schedule->set()){ 

            $scheduleUser = new scheduleUser;
            $scheduleUser->id_user = $user->id;
            $scheduleUser->id_schedule = $schedule->id;
            $scheduleUser->set();

            if($schedule->id_employee){
                $scheduleEmployee = new scheduleEmployee;
                $scheduleEmployee->id_employee = $schedule->id_employee;
                $scheduleEmployee->id_schedule = $schedule->id;
                $scheduleEmployee->set();
            }
            connection::commit();
        }
    }catch (\exception $e){
        connection::rollBack();
    }
```

#### Other Examples

##### Using the DB Class Directly
```php
$id = 1;
$db = new db("tb_employee");

// Returns true or false
$return = $db->delete($id);
```

#### Database Creation/Modification

##### Create a Table

Inside the app/models folder, create a class that will represent your database table as in the example below:

```php
<?php
namespace App\Models;

use Diogodg\Neoorm\Abstract\Model;
use Diogodg\Neoorm\Migrations\Table;
use Diogodg\Neoorm\Migrations\Column;

class State extends Model {
    //mandatory parameter that will define the table name in the database
    public const table = "state";

    //mandatory to be in this way
    public function __construct() {
        parent::__construct(self::table);
    }

    //method responsible for creating the table
    public static function table(){
        return (new Table(self::table,comment:"State table"))
                ->addColumn((new Column("id","INT"))->isPrimary()->setComment("City ID"))
                ->addColumn((new Column("name","VARCHAR",120))->isNotNull()->setComment("State name"))
                ->addColumn((new Column("uf","VARCHAR",2))->isNotNull()->setComment("UF name"))
                ->addColumn((new Column("country","INT"))->isNotNull()->setComment("country id of the state"))
                ->addForeignKey(Country::table,column:"country")
                ->addColumn((new Column("ibge","INT"))->isUnique()->setComment("IBGE id of the state"))
                ->addColumn((new Column("ddd","VARCHAR",50))->setComment("DDDs separated by , of the UF"));
    }

    //method responsible for inserting initial data in the table 
    public static function seed(){
        $object = new self;
        if(!$object->addLimit(1)->selectColumns("id")){
            $object->name = "Acre";
            $object->uf = "AC";
            $object->country = 1;
            $object->ibge = 12;
            $object->ddd = "68";
            $object->store();

            $object->name = "Alagoas";
            $object->uf = "AL";
            $object->country = 1;
            $object->ibge = 27;
            $object->ddd = "82";
            $object->store();

            $object->name = "Amapá";
            $object->uf = "AP";
            $object->country = 1;
            $object->ibge = 16;
            $object->ddd = "96";
            $object->store();

            $object->name = "Amazonas";
            $object->uf = "AM";
            $object->country = 1;
            $object->ibge = 13;
            $object->ddd = "92,97";
            $object->store();
      }
  }
}
```

After creating all classes

just call the following command

```bash
php migrate
```

## Bundler

```
your-project/
├── Resources/
│   ├── Js/    # JS files for your project
│   ├── Css/   # CSS files for your project
```

After placing the files in this folder
just call the following command

```bash
php build
```
They will be compiled to the public folder

```
your-project/
├── Config/
│   ├── bundler.config.php    # Here you can configure which files will be compiled
```

```php
<?php 

return [
    "js" => [
        "site" => ["htmx.min.js","swiper-bundle.min.js","zmain.js","aos.js"],
        "admin" => ["htmx.min.js","bootstrap.bundle.min.js","chart.js","choices.min.js","zadmin.js"]
    ],
    "css" => [
        "site" => ["all.min.css","swiper-bundle.min.css","tailwind.css","aos.css"],
        "admin" => ["choices.min.css","bootstrap.min.css","all.min.css","zadmin.css"]
    ]
];
```

## Configuration

The framework uses environment variables for configuration. Configure your `.env` file with the following variables:

```env
ENVIRONMENT=dev
PATH_CONTROLLERS=App/Controllers
```

## Security

- Protection against SQL Injection
- Protection against CSRF
- Protection against XSS
- Data validation
- Secure session management
- Encryption of sensitive data

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
