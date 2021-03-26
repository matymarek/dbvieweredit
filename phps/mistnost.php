<?php require_once("include/connect.php"); require_once("loggedIn.php");?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Bootstrap-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body class="container">
<?php
session_start();
$logged = $_SESSION['logged'] ?: false;
$id['employee_id'] = $_SESSION['employee_id'];
defUser($id);
$user = getUser();

if($logged) {
    $pdo = dbConnect();
    $roomId = filter_input(
        INPUT_GET,
        'roomId',
        FILTER_VALIDATE_INT);
    $stmt = $pdo->query("SELECT * FROM `room` WHERE `room_id` = $roomId");
    $html = "";
    $count = 0;
    $wage = 0;
    $vysledek = 0;
    if ($stmt->rowCount() == 0) {
        echo "Záznam neobsahuje žádná data";
    }
    else {
        foreach ($stmt as $row) {
            $html .= "
                <title>" . $row['name'] . "</title>    
                <h1>Místnost č. " . $row['no'] . "</h1>
                <dl class='dl-horizontal'>
                <dt>Číslo</dt>
                <dd>" . $row['no'] . "</dd>
                <dt>Název</dt>
                <dd>" . $row['name'] . "</dd>
                <dt>Telefon</dt>
                <dd>" . $row['phone'] . "</dd>";
        }
    }
    $stmt = $pdo->query("
        SELECT `wage` FROM `employee` WHERE `room` = $roomId");
    $html .= "<dt>Průměrná mzda</dt>";
    foreach ($stmt as $row) {
        $count++;
        $wage = $wage + $row['wage'];
    }
    $vysledek = $wage / $count;
    if ($vysledek == 0) {
        $html .= "
                <dd>   </dd>";
    } else {
        $html .= "
                <dd>" . $vysledek . "</dd>";
    }
    $html .= "<dt>Lidé</dt>";
    $stmt = $pdo->query("
        SELECT * FROM `employee` WHERE `room` = $roomId");
    if ($stmt->rowCount() == 0) {
        $html .= "<dd>       </dd> ";
    }
    else {
        foreach ($stmt as $row) {
            if($user['admin']) {
                $html .= "
                <a href='clovek.php?employeeId=" . $row['employee_id'] . "'>
                <dd>" . $row['surname'] . " " . $row['name'] . "</dd>
                </a>
                ";
            }
            else{
                $html .= "
                <a href='clovek.php?employeeId=" . $row['employee_id'] . "'>
                <dd>" . $row['surname'] . " " . $row['name'] . "</dd>
                </a>
                ";
            }
        }
    }

    $stmt = $pdo->query("
        SELECT `employee`, `name`, `surname`, `employee_id`, `key`.`room`
        FROM `key`, `employee` 
        WHERE `key`.`room` = $roomId AND `employee` = `employee_id`;");

    $html .= "<dt>Klíče</dt>";
    foreach ($stmt as $row){
        $html .= "
            <a href='clovek.php?employeeId=" . $row['employee_id'] . "'>
            <dd>" . $row['surname'] . " " . $row['name'] . "</dd>
            </a>
            ";
    }
    $html .= "<a href='mistnosti.php'>◄ Zpět na seznam místností</a>";
    echo $html;
    unset($stmt);
}
else{
    echo("
            <title>Nepřihlášený uživatel</title>
            <div class='alert alert-danger mt-3' role='alert'>
                Musíš se přihlásit <a href='../index.php'>zde</a>.
            </div>");
}
?>
</body>
</html>
