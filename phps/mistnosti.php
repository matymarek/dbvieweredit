<?php
require_once "include/connect.php";
require "sort.php";
require_once "loggedIn.php";
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Bootstrap-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Mistnosti</title>
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
    if ($sortCol != null && $sortDir != null) {
        $stmt = sortR($sortCol, $sortDir);
    }
    else {
        $stmt = $pdo->query("SELECT * FROM `room`");
    }
    navbar();
    $html = "<h1>Seznam místností</h1> ";
    if ($stmt->rowCount() == 0) {
        echo "Záznam neobsahuje žádná data";
    }
    else {
        if($user['admin']){ $html .="<a href='create.php' class='btn btn-primary mt-3'>Nová místnost</a>";}
        $html .= "
            <table class='table table-striped roomList'>
            <th>Název
                <a href='?sortCol=name&sortDir=asc'>▼</a>
                <a href='?sortCol=name&sortDir=desc'>▲</a>
            </th>
            <th>Číslo
                <a href='?sortCol=no&sortDir=asc'>▼</a>
                <a href='?sortCol=no&sortDir=desc'>▲</a>
            </th>
            <th>Telefon
                <a href='?sortCol=rphone&sortDir=asc'>▼</a>
                <a href='?sortCol=rphone&sortDir=desc'>▲</a>
            </th>";
        if ($user['admin']) {
          $html .= "<th>Editace</th>";
        }
        foreach ($stmt as $row) {
            $html .= "<tr>
            <td><a href='mistnost.php?roomId=" . $row['room_id'] . "'>" . $row['name'] . "</a></td>";
            $html .= "
                <td>" . $row['no'] . "</td>
                <td>" . $row['phone'] . "</td>";
            if ($user['admin']) {
                $html .= "
            <td>
                <a href='update.php?roomId=" . $row['room_id'] . "' class='btn btn-primary'>Update</a>
                <a href='delete.php?roomId=" . $row['room_id'] . "' class='btn btn-danger' onsubmit='return confirm(`Opravdu smazat? Akce je nevratná!`);'>Smazat</a>
            </td>
            ";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        echo $html;
        //▲
        //▼
    }
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
