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
    $employeeId = filter_input(
        INPUT_GET,
        'employeeId',
        FILTER_VALIDATE_INT);
    $html = "";

    $stmt = $pdo->query("
    SELECT `employee_id`, `surname`, `employee`.`name`, `job`, `wage`,`room_id`, `room`.`name` AS `rname` 
    FROM `employee`, `room`
    WHERE `employee_id`= $employeeId AND `room` = `room_id`");

    foreach ($stmt as $row){
        $html = "
            <title>" . $row['surname'] . " " . $row['name'] . "</title>
            <h1>Zaměstnanec: ". $row['name'] . " " . $row['surname'] . "</h1>
            <dl class='dl-horizontal'>
            <dt>Jméno</dt>
            <dd>" . $row['name'] . "</dd>
            <dt>Příjmení</dt>
            <dd>" . $row['surname'] . "</dd>
            <dt>Pozice</dt>
            <dd>" . $row['job'] . "</dd>
            <dt>Plat</dt>
            <dd>" . $row['wage'] . "</dd>
            <dt>Místnost</dt>";
            $html .= "<a href='mistnost.php?roomId=" . $row['room_id']. "'><dd>" . $row['rname'] . "</dd></a>";
    }

    $stmt = $pdo->query("
        SELECT `employee`, `name`, `room_id`, `employee`, `key_id`
        FROM `key`, `room` 
        WHERE `employee` = $employeeId AND `room` = `room_id`");
    $html .= "<dt>Klíče</dt>";
    foreach ($stmt as $row){
        $html .= "
            <dd>
                <a href='mistnost.php?roomId=" . $row['room_id']. "'>". $row['name'] . "</a>
            </dd>";
        if($user['admin']){
            $html .= "
            <dd>
                <a href='deleteKey.php?employeeId=" . $employeeId . "&keyId=" . $row['key_id'] . "' class='btn btn-danger' onsubmit='return confirm(`Opravdu smazat? Akce je nevratná!`);'>Smazat</a>
            </dd>
            ";
        }
    }
    if($user['admin']){
        $html .= "
            <dd><a href='createKey.php?employeeId=" . $employeeId . "' class='btn btn-primary'>Vytvořit</a></dd>";
    }
    $html .= "</dl><a href='lide.php'>◄ Zpět na seznam zaměstnanců</a>";
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
