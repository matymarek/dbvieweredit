<?php
require_once ("phps/include/connect.php");
require "phps/loggedIn.php";
echo("
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
          integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
    <body class='container'>");
const STATE_FORM_REQUESTED = 1;
const STATE_FORM_SENT = 2;
const STATE_PROCESSED = 3;

const RESULT_SUCCESS = 1;
session_start();
$logged = $_SESSION['logged'] ?: false;
$id['employee_id'] = $_SESSION['employee_id'];
defUser($id);
$user = getUser();

$result = $logged ? 1 : filter_input(
    INPUT_GET,
    'result',
    FILTER_VALIDATE_INT);
$login = array();
$state = getState($logged);

while (true) {
    if ($state === STATE_PROCESSED) {
        //je hotovo, reportujeme
        if ($result === RESULT_SUCCESS) {
            navbar();
            echo("
        <title>Přihlášen</title>
        <div class='alert alert-success mt-3' role='alert'>
        Úspěšně přihlášen.</div>
        <h1>Rozcestník</h1>
        <form method='post' action='phps/mistnosti.php?'>
            <div class='mb-3'>
                <input type='submit' value='Seznam místností' class='btn btn-primary' >
            </div>
        </form>
        <form method='get' action='phps/lide.php?'>       
            <div class='mb-3'>
                <input type='submit' value='Seznam zaměstnanců' class='btn btn-primary' >
            </div>       
        </form>     
        </body>");
            break;
        }
    } elseif ($state === STATE_FORM_SENT) {
        //načíst data
        //validovat data
        $data = [];
        $data['login'] = filter_input(INPUT_POST, 'login');
        $data['pass'] = filter_input(INPUT_POST, 'pass');
        defUser($data);
        $user = getUser();
        if (isDataValid($user) && password_verify($data['pass'], $user['pass'])) {
            //přesměruj se zprávou "úspěch"
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['logged'] = true;
            redirect(RESULT_SUCCESS);
        } else {
            //jít na formulář nebo
            $state = STATE_FORM_REQUESTED;
            echo("
        <title>Aktualizovat záznam : Neplatný formulář</title>
        <title>Přihlášení se nezdařilo</title>
        <div class='alert alert-danger mt-3' role='alert'>
        Přihlášení se nezdařilo, zkuste to prosím znovu.
        </div>");
        }
    } else {
        //přejít na formulář
        echo("<title>Přihlášení</title>");
        echo("<h1>Přihlášení</h1>
        <form method='post'>
        <div class='mb-3'>
            <label for='login'>Jméno</label>
            <input type='text' name='login' id='login' required class='form-control'>
        </div>
        <div class='mb-3'>
            <label for='pass'>Heslo</label>
            <input type='password' name='pass' id='pass' required class='form-control'>
        </div>
            <input type='hidden' name='action' value='login'>
            <input type='submit' value='Přihlásit' class='btn btn-primary'>
        </div>
    </form>
    </body> ");
        break;
    }
}
    function getState($logged) : int {
        //rozpoznání processed
        $result = $logged ? 1 : filter_input(INPUT_GET, 'result', FILTER_VALIDATE_INT);
        if(!$result) {
            $result = 0;
        }
        if ($result === RESULT_SUCCESS) {
            return STATE_PROCESSED;
        }

        $action = filter_input(INPUT_POST, 'action');
        if ($action === 'login') {
            return STATE_FORM_SENT;
        }

        return STATE_FORM_REQUESTED;
    }

    function isDataValid(array $user) : bool {
        if(!$user['employee_id']) {
            return false;
        }
        if(!$user['ename']) {
            return false;
        }
        if(!$user['surname']) {
            return false;
        }
        if(!$user['job']) {
            return false;
        }
        if(!$user['wage']) {
            return false;
        }
        if(!$user['eroom']) {
            return false;
        }
        if (!$user['login']) {
            return false;
        }
        if (!$user['pass']) {
            return false;
        }
        if(!$user['room_id']) {
            return false;
        }
        if(!$user['rname']) {
            return false;
        }

        return true;
    }

    function redirect(int $result) : void {
        $location = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: {$location}?result={$result}");
        exit;
    }

