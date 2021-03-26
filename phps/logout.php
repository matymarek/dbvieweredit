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
if($logged){
    $_SESSION['logged'] = false;
    $_SESSION['employee_id'] = null;
    echo ("<title>Odhlášen</title>
        <div class='alert alert-success mt-3' role='alert'>
        Úspěšně Odhlášen. Přihlásit <a href='../index.php'>zde</a>
        </div>");
}
else{
    echo("
            <title>Nepřihlášený uživatel</title>
            <div class='alert alert-danger mt-3' role='alert'>
                Musíš se přihlásit <a href='../index.php'>zde</a>.
            </div>");
}
echo("</body>");