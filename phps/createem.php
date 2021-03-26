<?php
require "include/connect.php";
require_once "loggedIn.php";
echo("<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
          integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
        <body class='container'>");
const STATE_FORM_REQUESTED = 1;
const STATE_FORM_SENT = 2;
const STATE_PROCESSED = 3;

const RESULT_SUCCESS = 1;
const RESULT_FAIL = 2;
session_start();
$logged = $_SESSION['logged'] ?: false;
$id['employee_id'] = $_SESSION['employee_id'];
defUser($id);
$user = getUser();

if($logged) {
$result = filter_input(
    INPUT_GET,
    'result',
    FILTER_VALIDATE_INT);
$state = getState();
$employee = array();
$key = array();
while(true) {
    if ($state === STATE_PROCESSED) {
        //je hotovo, reportujeme
        if ($result === RESULT_SUCCESS) {
            echo("
            <title>Zaměstnanec vytvořen</title>
            <div class='alert alert-success mt-3' role='alert'>
            Zaměstnanec byl úspěšně vytvořen. Pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.</div>");
            break;
        } elseif ($result === RESULT_FAIL) {
            echo("
            <title>Vytvoření zaměstnance selhalo</title>
            <div class='alert alert-danger mt-3' role='alert'>
            Vytvoření zaměstnance selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.
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
            if (insertem($employee)) {
                $key = readPostKey($employee);
                if(isDataValidKey($key)) {
                    if (insertBase($key)) {
                        //přesměruj se zprávou "úspěch"
                        redirect(RESULT_SUCCESS);
                    }
                    else {
                        //přesměruj se zprávou "neúspěch"
                        redirect(RESULT_FAIL);
                    }
                }
                else{
                    $state = STATE_FORM_REQUESTED;
                    echo("
                        <title>Vytvořit zaměstnance : Neplatný formulář</title>");
                }
            } else {
                //přesměruj se zprávou "neúspěch"
                redirect(RESULT_FAIL);
            }
        } else {
            //jít na formulář nebo
            $state = STATE_FORM_REQUESTED;
            echo("
                <title>Vytvořit zaměstnance : Neplatný formulář</title>
                <div class='alert alert-danger mt-3' role='alert'>
                    Uživatel s tímto loginem již existuje, zvolte jiný a akci opakujte.     
                </div>");
        }
    } else {
        //přejít na formulář
        echo("<title>Vytvořit zaměstnance</title>");
        echo("<h1>Vytvořit zaměstnance</h1>
            <form method='post'>
            <div class='mb-3'>
                <label for='name'>Jméno</label>
                <input type='text' name='name' id='name' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='surname'>Příjmení</label>
                <input type='text' name='surname' id='surname' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='job'>Pozice</label>
                <input type='text' name='job' id='job' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='wage'>Plat</label>
                <input type='text' name='wage' id='wage' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='rname'>Místnost</label>
                <input type='text' name='rname' id='rname' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='login'>Login</label>
                <input type='text' name='login' id='login' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='pass'>Heslo</label>
                <input type='text' name='pass' id='pass' required class='form-control'>
            </div>
            <div class='mb-3'>
                <label for='admin'>Admin</label>
                <input type='checkbox' name='admin' id='admin' value='1' class='form-control'>
            </div>            
            <div class='mb-3'>
                <input type='hidden' name='action' value='create'>
                <input type='submit' value='Vytvořit' class='btn btn-primary'>
            </div>
        </form> ");
        break;
    }
}
}
else{
    echo("
            <title>Nepřihlášený uživatel</title>
            <div class='alert alert-danger mt-3' role='alert'>
                Musíš se přihlásit <a href='../index.php'>zde</a>.
            </div>");
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
    if ($action === 'create') {
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

function readPostKey(array $employee) : array {
    $key = [];
    $pdo = dbConnect();
    $name = filter_input(INPUT_POST, 'name');
    $surname = filter_input(INPUT_POST, 'surname');
    $stmt = $pdo->query("SELECT * FROM employee
        WHERE surname = '" . $surname . "' AND name = '" . $name . "';");

    foreach ($stmt as $row) {
        $key['employee'] = $row['employee_id'];
    }
    $key['room'] = $employee['room'];
    return $key;
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
function isDataValidKey(array $key) : bool {
    if (!$key['employee'])
        return false;
    if (!$key['room'])
        return false;

    return true;
}
function insertem(array $employee) {
    $query = "INSERT INTO employee (name, surname, job, wage, room, login, pass, admin) VALUES (:name, :surname, :job, :wage, :room, :login, :pass, :admin)";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $hash = password_hash($employee['pass'], PASSWORD_DEFAULT);
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
function insertBase(array $key){
    $query = "INSERT INTO `key` (employee, room) VALUES (:employee, :room)";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':room', $key['room']);
    $stmt->bindParam(':employee', $key['employee']);

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
