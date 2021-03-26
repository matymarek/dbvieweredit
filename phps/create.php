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
    $room = array();
    while(true) {
        if ($state === STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($result === RESULT_SUCCESS) {
                echo("
                <title>Místnost vytvořena</title>
                <div class='alert alert-success mt-3' role='alert'>
                Místnost byla úspěšně vytvořena. Pokračujte na <a href='mistnosti.php'>seznam místností</a>.</div>");
                break;
            } elseif ($result === RESULT_FAIL) {
                echo("
                <title>Vytvoření místnosti selhalo</title>
                <div class='alert alert-danger mt-3' role='alert'>
                Vytvoření místnosti selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte na <a href='mistnosti.php'>seznam místností</a>.
            </div>");
                break;
            }
        } elseif ($state === STATE_FORM_SENT) {
            //načíst data
            $room = readPost();
            //validovat data
            if (isDataValid($room)) {
                //uložit a přesměrovat
                if (insert($room)) {
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
                    <title>Vytvořit místnost : Neplatný formulář</title>
                    <div class='alert alert-danger mt-3' role='alert'>
                        Zkontrolujte, jestli místnost již neexistuje <a href='mistnosti.php'>zde</a>.
                    </div>");
                break;
            }
        } else {
            //přejít na formulář
            echo("<title>Vytvořit místnost</title>");
            echo("<h1>Vytvořit místnost</h1>
                <form method='post'>
                <div class='mb-3'>
                    <label for='name'>Jméno</label>
                    <input type='text' name='name' id='name' required class='form-control'>
                </div>
                <div class='mb-3'>
                    <label for='no'>Číslo</label>
                    <input type='text' name='no' id='no' required class='form-control'>
                </div>
                <div class='mb-3'>
                    <label for='phone'>Phone</label>
                    <input type='text' name='phone' id='phone' class='form-control'>
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
    $room = [];
    $empty = [];
    $room['name'] = filter_input(INPUT_POST, 'name');
    $room['no'] = filter_input(INPUT_POST, 'no');
    $room['phone'] = filter_input(INPUT_POST, 'phone');

    if (!$room['phone'])
        $room['phone'] = null;

    $exist = exist($room);
    if($exist) {
        return $empty;
    }
    return $room;
}

function isDataValid(array $room) : bool {
    if (!$room['name'])
        return false;

    if (!$room['no'])
        return false;

    return true;
}

function insert(array $room) {
    $query = "INSERT INTO room (name, no, phone) VALUES (:name, :no, :phone)";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $room['name']);
    $stmt->bindParam(':no', $room['no']);
    $stmt->bindParam(':phone', $room['phone']);

    return $stmt->execute();
}

function redirect(int $result) : void {
    $location = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$location}?result={$result}");
    exit;
}
function exist(array $room){
    $exist = null;
    $pdo = dbConnect();
    $stmt = $pdo->query("SELECT * FROM `room` 
        WHERE (`no`='" . $room['no'] . "' AND `name`='" . $room['name'] . "') 
        OR `no`='" . $room['no'] . "';");
    foreach ($stmt as $row){
        $exist = $row['room_id'];
    }
    return $exist;
}