<?php
require "include/connect.php";
require_once "loggedIn.php";
echo("<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
          integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
        <body class='container'>");
session_start();
$logged = $_SESSION['logged'] ?: false;
$id['employee_id'] = $_SESSION['employee_id'];
defUser($id);
$user = getUser();
const STATE_FORM_REQUESTED = 1;
const STATE_FORM_SENT = 2;
const STATE_PROCESSED = 3;

const RESULT_SUCCESS = 1;
const RESULT_FAIL = 2;

if($logged) {

    $result = filter_input(
        INPUT_GET,
        'result',
        FILTER_VALIDATE_INT);

    $state = getState();
    $pdo = dbConnect();
    $employee = array();

    $employeeId = filter_input(
        INPUT_GET,
        'employeeId',
        FILTER_VALIDATE_INT);
    if ($employeeId) {
        $stmt = $pdo->query("
        SELECT `employee_id`, `surname`, `employee`.`name`, `job`, `wage`, `login`, `room_id`, `room`.`name` AS `rname` 
        FROM `employee`, `room`
        WHERE `employee_id`= $employeeId AND `room` = `room_id`");
    }
    while (true) {
        if ($state === STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($result === RESULT_SUCCESS) {
                echo("
                    <title>Zaměstnanec upraven</title>
                    <div class='alert alert-success mt-3' role='alert'>
                        Zaměstnanec byl úspěšně upraven. Pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.</div>");
                break;
            } elseif ($result === RESULT_FAIL) {
                echo("
                    <title>Upravení zaměstnance selhalo</title>
                    <div class='alert alert-danger mt-3' role='alert'>
                        Upravení zaměstnance selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.
                    </div>");
                break;
            }
        } elseif ($state === STATE_FORM_SENT) {
            //načíst data
            $employee = readPost();
            //validovat data
            if (isDataValid($employee)) {
                if(!$employee['admin'])
                    $employee['admin'] = 0;
                //uložit a přesměrovat
                if (updateem($employee)) {
                    //přesměruj se zprávou "úspěch"
                    redirect(RESULT_SUCCESS);
                } else {
                    //přesměruj se zprávou "neúspěch"
                    redirect(RESULT_FAIL);
                }
            } else {
                //jít na formulář nebo
                $state = STATE_FORM_REQUESTED;
                echo("
                    <title>Upravit zaměstnance : Neplatný formulář</title>
                    <div class='alert alert-danger mt-3' role='alert'>
                        Uživatel s tímto loginem již existuje, zvolte jiný a akci opakujte.    
                    </div>");
            }
        } else {
            echo("<title>Upravit zaměstnance</title>");
            if (!$employeeId) {
                throw new RequestException(400);
            }
            $employee = readDB($employeeId);
            if (!$employee) {
                throw new RequestException(404);
            }
            if ($stmt->rowCount() == 0) {
                echo "Záznam neobsahuje žádná data";
            } else {
                foreach ($stmt as $row) {
                    //přejít na formulář
                    echo("<h1>Upravit zaměstnance</h1>
                    <form method='post'>
                    <div class='mb-3'>
                        <label for='name'>Jméno</label>
                        <input type='text' name='name' id='name' value='" . $row['name'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='surname'>Příjmení</label>
                        <input type='text' name='surname' id='surname' value='" . $row['surname'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='job'>Pozice</label>
                        <input type='text' name='job' id='job' value='" . $row['job'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='wage'>Plat</label>
                        <input type='text' name='wage' id='wage' value='" . $row['wage'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='rname'>Místnost</label>
                        <input type='text' name='rname' id='rname' value='" . $row['rname'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='login'>Login</label>
                        <input type='text' name='login' id='login' value='" . $row['login'] . "' required class='form-control'>
                    </div>
                    <div class='mb-3'>
                        <label for='pass'>Heslo</label>
                        <input type='text' name='pass' id='pass' required class='form-control'>
                    </div><div class='mb-3'>
                        <label for='admin'>Admin</label>
                        <input type='checkbox' name='admin' id='admin' value='1' class='form-control'>
                    </div>  
                    <div class='mb-3'>
                        <input type='hidden' name='action' value='update'>
                        <input type='hidden' name='employee_id' value='" . $row['employee_id'] . "'>
                        <input type='submit' value='Upravit' class='btn btn-primary'>
                    </div>
                </form> ");
                    break;
                }
                break;
            }
        }
    }
}
echo("</body>");

function getState() : int {
    //rozpoznání processed
    $result = filter_input(INPUT_GET, 'result', FILTER_VALIDATE_INT);

    if ($result === RESULT_SUCCESS) {
        return STATE_PROCESSED;
    } elseif ($result === RESULT_FAIL) {
        return STATE_PROCESSED;
    }

    $action = filter_input(INPUT_POST, 'action');
    if ($action === 'update') {
        return STATE_FORM_SENT;
    }

    return STATE_FORM_REQUESTED;
}

function readPost() : array {
    $employee = [];
    $empty = [];
    $pdo = dbConnect();
    $help =  filter_input(INPUT_POST, 'rname');
    $stmt = $pdo->query("SELECT * FROM `room` WHERE `name` = '" . $help . "'");

    $employee['employee_id'] = filter_input(INPUT_POST, 'employee_id');
    $employee['name'] = filter_input(INPUT_POST, 'name');
    $employee['surname'] = filter_input(INPUT_POST, 'surname');
    $employee['job'] = filter_input(INPUT_POST, 'job');
    $employee['wage'] = filter_input(INPUT_POST, 'wage');
    $employee['login'] = filter_input(INPUT_POST, 'login');
    $employee['pass'] = filter_input(INPUT_POST, 'pass');
    $employee['admin'] = filter_input(INPUT_POST, 'admin');

    foreach ($stmt as $row) {
        $employee['room'] = $row['room_id'];
    }

    if(!$employee['room'])
        $employee['room'] = 7;
    $exist = exist($employee);
    if($exist) {
        return $empty;
    }
    return $employee;
}

function isDataValid(array $employee) : bool {
    if (!$employee['name'])
        return false;
    if (!$employee['surname'])
        return false;
    if (!$employee['job'])
        return false;
    if (!$employee['wage'])
        return false;
    if (!$employee['room'])
        return false;
    if (!$employee['login'])
        return false;
    if (!$employee['pass'])
        return false;

    return true;
}

function readDB(int $employee_id) : array {
    $query = "SELECT * FROM employee WHERE employee_id = :employee_id;";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':employee_id', $employee_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateem(array $employee) {
    $query = "UPDATE employee SET name = :name, surname = :surname, job = :job, wage = :wage, room = :room, 
                    login = :login, pass = :pass, admin = :admin 
        WHERE employee_id = :employee_id";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $hash = password_hash($employee['pass'], PASSWORD_DEFAULT);
    $stmt->bindParam(':employee_id', $employee['employee_id']);
    $stmt->bindParam(':name', $employee['name']);
    $stmt->bindParam(':surname', $employee['surname']);
    $stmt->bindParam(':job', $employee['job']);
    $stmt->bindParam(':wage', $employee['wage']);
    $stmt->bindParam(':room', $employee['room']);
    $stmt->bindParam(':login', $employee['login']);
    $stmt->bindParam(':pass', $hash);
    $stmt->bindParam(':admin', $employee['admin']);
    return $stmt->execute();
}

function redirect(int $result) : void {
    $location = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$location}?result={$result}");
    exit;
}

function exist(array $employee){
    $exist = null;
    $pdo = dbConnect();
    $stmt = $pdo->query("SELECT * FROM `employee` WHERE `login`='". $employee['login'] . "';");
    foreach ($stmt as $row){
        $exist = $row['employee_id'];
    }
    return $exist;
}
