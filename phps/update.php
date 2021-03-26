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
    $pdo = dbConnect();

    $roomId = filter_input(
        INPUT_GET,
        'roomId',
        FILTER_VALIDATE_INT);
    if($roomId) {
        $stmt = $pdo->query("SELECT * FROM `room` WHERE `room_id` = $roomId");
    }
    while(true) {
        if ($state === STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($result === RESULT_SUCCESS) {
                echo("
                <title>Místnost upravena</title>
                <div class='alert alert-success mt-3' role='alert'>
                Místnost byla úspěšně aktualizován. Pokračujte na <a href='mistnosti.php'>seznam místností</a>.</div>");
                break;
            } elseif ($result === RESULT_FAIL) {
                echo("
                <title>Aktualizace místnosti selhala</title>
                <div class='alert alert-danger mt-3' role='alert'>
                Aktualizace místnosti selhala. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte na <a href='mistnosti.php'>seznam místností</a>.
            </div>");
                break;
            }
        } elseif ($state === STATE_FORM_SENT) {
            //načíst data
            $room = readPost();
            //validovat data
            if (isDataValid($room)) {
                //uložit a přesměrovat
                if (update($room)) {
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
                <title>Aktualizovat místnost : Neplatný formulář</title>
                <div class='alert alert-danger mt-3' role='alert'>
                    Tato místnost již existuje. Najdete ji <a href='mistnosti.php'>zde</a>.
                </div>");
            }
        } else {
            //přejít na formulář
            echo("<title>Aktualizovat místnost</title>");
            if (!$roomId) {
                throw new RequestException(400);
            }
            $room = readDB($roomId);
            if (!$room) {
                throw new RequestException(404);
            }
            if ($stmt->rowCount() == 0) {
                echo "Záznam neobsahuje žádná data";
            }
            else {
                foreach ($stmt as $row) {
                    echo("<h1>Upravit místnost</h1>
                        <form method='post'>
                        <div class='mb-3'>
                            <label for='name'>Jméno</label>
                            <input type='text' name='name' id='name' value='" . $row['name'] . "' required class='form-control'>
                        </div>
                        <div class='mb-3'>
                            <label for='no'>Číslo</label>
                            <input type='text' name='no' id='no' value='" . $row['no'] . "' required class='form-control'>
                        </div>
                        <div class='mb-3'>
                            <label for='phone'>Phone</label>
                            <input type='text' name='phone' id='phone' value='" . $row['phone'] . "' class='form-control'>
                        </div>
                        <div class='mb-3'>
                            <input type='hidden' name='action' value='update'>
                            <input type='hidden' name='room_id' value='" . $row['room_id'] . "'>
                            <input type='submit' value='Upravit' class='btn btn-primary'>
                        </div>
                    </form> ");
                }
            break;
            }
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
    if ($action === 'update') {
        return STATE_FORM_SENT;
    }

    return STATE_FORM_REQUESTED;
}
function readPost() : array {
    $room = [];
    $empty = [];
    $room['room_id'] = filter_input(INPUT_POST, 'room_id');
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
function readDB(int $room_id) : array {
    $query = "SELECT room_id, name, no, phone FROM room WHERE room_id = :room_id;";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':room_id', $room_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isDataValid(array $room) : bool {
    if (!$room['name'])
        return false;

    if (!$room['no'])
        return false;

    return true;
}

function update(array $room) {
    $query = "UPDATE room SET name = :name, phone = :phone, no = :no WHERE room_id = :room_id";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':room_id', $room['room_id']);
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