<?php
require "include/connect.php";
require_once "loggedIn.php";
echo("<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
         integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
        <body class='container'>");

const STATE_DELETE_REQUESTED = 1;
const STATE_PROCESSED = 2;
const STATE_DELETE_SENT = 3;

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

$employeeId = filter_input(
    INPUT_GET,
    'employeeId',
    FILTER_VALIDATE_INT);
while(true) {
    if ($state === STATE_PROCESSED) {
        //je hotovo, reportujeme
        if ($result === RESULT_SUCCESS) {
            echo("
            <title>Záznam smazán</title>
            <div class='alert alert-success mt-3' role='alert'>
            Záznam byl úspěšně smazán. Pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.</div>");
            break;
        } elseif ($result === RESULT_FAIL) {
            echo("
            <title>Smazání záznamu selhalo</title>
            <div class='alert alert-success mt-3' role='alert'>
            Smazání záznamu selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte na <a href='lide.php'>seznam zaměstnanců</a>.</div>");
            break;
        }
    } elseif ($state === STATE_DELETE_REQUESTED) {
        //načíst data
        //validovat data
        if ($employeeId == null) {
            throw new RequestException(400);
        }
        if (deleteKeyem($employeeId)) {
            if (deleteem($employeeId)) {
                //přesměruj se zprávou "úspěch"
                redirect(RESULT_SUCCESS);
            }
            else {
                //přesměruj se zprávou "neúspěch"
                redirect(RESULT_FAIL);
            }
        }
        else {
            //přesměruj se zprávou "neúspěch"
            redirect(RESULT_FAIL);
        }
    }
    else{
        echo("<title>Opravdu smazat</title>");
        echo("<h1>Opravdu smazat?</h1>
                <form method='post'>
                    <div class='mb-3'>
                        <input type='hidden' name='action' value='delete'>
                        <input type='submit' value='Smazat' class='btn btn-danger'>
                    </div>
                </form>
                <div class='mb-3'>
                    <a href='lide.php' class='btn btn-primary'>Zpět</a>
                </div> ");
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
function getState() : int {
    //rozpoznání processed
    $result = filter_input(INPUT_GET, 'result', FILTER_VALIDATE_INT);

    if ($result === RESULT_SUCCESS) {
        return STATE_PROCESSED;
    } elseif ($result === RESULT_FAIL) {
        return STATE_PROCESSED;
    }
    $action = filter_input(INPUT_POST, 'action');
    if($action === 'delete') {
        return STATE_DELETE_REQUESTED;
    }
    return STATE_DELETE_SENT;
}

function deleteem(int $employee_id) {
    $query = "DELETE FROM employee WHERE employee_id = :employee_id";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':employee_id', $employee_id);
    return $stmt->execute();
}

function deleteKeyem(int $employee_id) {
    $query = "DELETE FROM `key` WHERE employee = :employee_id";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':employee_id', $employee_id);
    return $stmt->execute();
}

function redirect(int $result) : void {
    $location = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$location}?result={$result}");
    exit;
}

