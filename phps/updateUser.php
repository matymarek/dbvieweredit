<?php
require_once("loggedIn.php");
require_once("include/connect.php");
echo("
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
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

if($logged){
    $result = filter_input(
        INPUT_GET,
        'result',
        FILTER_VALIDATE_INT);

    $state = getState();
    $pdo = dbConnect();
    $input = array();

    while (true) {
        if ($state === STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($result === RESULT_SUCCESS) {
                echo("
                    <title>Data upravena</title>
                    <div class='alert alert-success mt-3' role='alert'>
                        Data byla úspěšně upravena. Pokračujte zpět <a href='/'>domů</a>.</div>");
                break;
            } elseif ($result === RESULT_FAIL) {
                echo("
                    <title>Upravení dat selhalo</title>
                    <div class='alert alert-danger mt-3' role='alert'>
                        Upravení dat selhalo. Kontaktujte administrátora, případně akci zopakujte nebo pokračujte zpět <a href='/'>domů</a>.
                    </div>");
                break;
            }
        } elseif ($state === STATE_FORM_SENT) {
            //načíst data
            $input = readPost();
            //validovat data
            if (isDataValid($input)) {
                //uložit a přesměrovat
                if (updateem($user['employee_id'], $input['pass'])) {
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
                    <title>Upravit data : Neplatný formulář</title>
                    <div class='alert alert-danger mt-3' role='alert'>
                        Hesla se neshodují, zkuste to znovu.     
                    </div>");
            }
        } else {
            echo("<title>Upravit data</title>");
            //přejít na formulář
            echo("<h1>Upravit data</h1>
                <form method='post'>
                <div class='mb-3'>
                    <label for='pass'>Heslo</label>
                    <input type='text' name='pass' id='pass' required class='form-control'>
                <div class='mb-3'>
                <div class='mb-3'>
                    <label for='pass2'>Heslo znovu</label>
                    <input type='text' name='pass2' id='pass2' required class='form-control'>
                <div class='mb-3'>
                    <input type='hidden' name='action' value='update'>
                    <input type='submit' value='Upravit' class='btn btn-primary'>
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
    if ($action === 'update') {
        return STATE_FORM_SENT;
    }

    return STATE_FORM_REQUESTED;
}
function readPost() : array {
    $user = [];
    $user['pass2'] = filter_input(INPUT_POST, 'pass2');
    $user['pass'] = filter_input(INPUT_POST, 'pass');

    return $user;
}
function isDataValid(array $user) : bool {
    if (!$user['pass2'])
        return false;
    if (!$user['pass'])
        return false;
    if($user['pass'] !== $user['pass2'])
        return false;
    return true;
}
function updateem($user, $input) {
    $query = "UPDATE employee SET pass = :pass WHERE employee_id = :employee_id";
    $pdo = dbConnect();
    $stmt = $pdo->prepare($query);
    $hash = password_hash($input, PASSWORD_DEFAULT);
    $stmt->bindParam(':employee_id', $user);
    $stmt->bindParam(':pass', $hash);

    return $stmt->execute();
}
function redirect(int $result) : void {
    $location = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$location}?result={$result}");
    exit;
}
function exist(array $user){
    $exist = null;
    $pdo = dbConnect();
    $stmt = $pdo->query("SELECT * FROM `employee` 
        WHERE (`login`='" . $user['login'] . "';");
    foreach ($stmt as $row){
        $exist = $row['employee_id'];
    }
    return $exist;
}