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
$key = array();
$employeeId = filter_input(
    INPUT_GET,
    'employeeId',
    FILTER_VALIDATE_INT);

while(true) {
    if ($state === STATE_PROCESSED) {
        //je hotovo, reportujeme
        if ($result === RESULT_SUCCESS) {
            echo("
            <title>Klíč vytvořen</title>
            <div class='alert alert-success mt-3' role='alert'>
            Klíč byl úspěšně vytvořen. Pokračujte <a href='clovek.php?employeeId=" . $employeeId . "'>zpět</a>.</div>");
            break;
        } elseif ($result === RESULT_FAIL) {
            echo("
            <title>Vytvoření klíče selhalo</title>
            <div class='alert alert-success mt-3' role='alert'>
            Vytvoření klíče selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte <a href='clovek.php?employeeId=" . $employeeId . "'>zpět</a>.</div>");
            break;
        }
    } elseif ($state === STATE_FORM_SENT) {
        //načíst data
        $key = readPost($employeeId);
        //validovat data
        if (isDataValid($key)) {
            //uložit a přesměrovat
            if (insertKey($key)) {
                //přesměruj se zprávou "úspěch"
                redirect(RESULT_SUCCESS, $employeeId);
            } else {
                //přesměruj se zprávou "neúspěch"
                redirect(RESULT_FAIL, $employeeId);
            }
        } else {
            //jít na formulář nebo
            $state = STATE_FORM_REQUESTED;
            echo("
            <title>Vytvořit klíč : Neplatný formulář</title>
            <div class='alert alert-danger mt-3' role='alert'>
                Zkontrolujte, jestli klíč již neexistuje <a href='clovek.php?employeeId=" . $employeeId . "'>zde</a>.
            </div>");
            break;
        }
    } else {
        //přejít na formulář
        echo("<title>Vytvořit klíč</title>");
        echo("<h1>Vytvořit klíč</h1>
            <form method='post'>
            <div class='mb-3'>
                <label for='rroom'>Místnost</label>
                <input type='text' name='rroom' id='rroom' required class='form-control'>
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

function readPost(int $employeeId) : array {
    $key = [];
    $empty = [];
    $pdo = dbConnect();
    $room =  filter_input(INPUT_POST, 'rroom');

    $stmt = $pdo->query("SELECT * FROM `room` WHERE `name` = '" . $room . "'");
    foreach ($stmt as $row) {
        $key['room'] = $row['room_id'];
    }
    $key['employee'] = $employeeId;
    $exist = exist($key);
    if($exist) {
        return $empty;
    }
    return $key;
}

function isDataValid(array $key) : bool {
    if (!$key['employee'])
        return false;
    if (!$key['room'])
        return false;

    return true;
}

function insertKey(array $key) {
    $query = "INSERT INTO `key` (employee, room) VALUES (:employee, :room)";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':employee', $key['employee']);
    $stmt->bindParam(':room', $key['room']);

    return $stmt->execute();
}

function redirect(int $result, int $employeeId) : void {
    $location = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$location}?employeeId={$employeeId}&&result={$result}");
    exit;
}
function exist(array $key){
    $exist = null;
    $pdo = dbConnect();
    $stmt = $pdo->query("SELECT * FROM `key` WHERE `employee`='". $key['employee'] . "' AND `room`='" . $key['room'] . "';");
    foreach ($stmt as $row){
        $exist = $row['key_id'];
    }
    return $exist;
}
