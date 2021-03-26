<?php require_once("include/connect.php"); require("sort.php"); require_once("loggedIn.php");?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Bootstrap-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Lide</title>
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

    $sortCol = filter_input(INPUT_GET, 'sortCol', FILTER_DEFAULT);
    $sortDir = filter_input(INPUT_GET, 'sortDir', FILTER_DEFAULT);

    if($sortCol != null && $sortDir != null)
        $stmt = sortE($sortCol, $sortDir);
    else
        $stmt = $pdo->query("
            SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
            FROM `employee`, `room`
            WHERE `room_id`= `employee`.`room`
            ");
    navbar();
    $html = "<h1>Seznam zaměstnanců</h1>";
    if ($user['admin']) $html .= "<a href='createem.php' class='btn btn-primary mt-3'>Nový zaměstnanec</a>";
    $html .="
        <table class='table table-striped'>
        <th>Jméno
            <a href='?sortCol=surname&sortDir=desc'>▼</a>
            <a href='?sortCol=surname&sortDir=asc'>▲</a>
        </th>
        <th>Místnost
            <a href='?sortCol=room&sortDir=desc'>▼</a>
            <a href='?sortCol=room&sortDir=asc'>▲</a>
        </th>
        <th>Telefon
            <a href='?sortCol=phone&sortDir=desc'>▼</a>
            <a href='?sortCol=phone&sortDir=asc'>▲</a>
        </th>
        <th>Pozice
            <a href='?sortCol=job&sortDir=desc'>▼</a>
            <a href='?sortCol=job&sortDir=asc'>▲</a>
        </th>";
    if ($user['admin']) $html .= "<th>Editace</th>";
    foreach ($stmt as $row) {
        $html .= "<tr>
        <td><a href='clovek.php?employeeId=" . $row['employee_id'] . "'>" . $row['surname'] . " " . $row['name'] . "</a></td>";
        $html .= "
            <td>" . $row['rname'] . "</td>
            <td>" . $row['phone'] . "</td>
            <td>" . $row['job'] . "</td>";
        if ($user['admin']) {
            $html .= "<td>
                <a href='updateem.php?employeeId=" . $row['employee_id'] . "' class='btn btn-primary'>Update</a>
                <a href='deleteem.php?employeeId=" . $row['employee_id'] . "' class='btn btn-danger' onsubmit='return confirm(`Opravdu smazat? Akce je nevratná!`);'>Smazat</a>
            </td>
            </tr>";
         }
    }
    $html .= "</table>";
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
