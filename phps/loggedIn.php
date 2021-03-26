<?php
function defUser(array $user)
{
    $pdo = dbConnect();
    $stmt = $pdo->query("SELECT `employee_id`, `employee`.`name` AS `ename`, `surname`, `job`, `wage`, 
        `room`, `login`, `pass`, `admin`, `room_id`, `room`.`name` AS `rname` FROM `employee`, `room` 
        WHERE (`employee_id`='" . $user['employee_id'] . "' OR `login`='" . $user['login'] . "') AND `room_id`=`room`");
    foreach ($stmt as $row) {
        define("employee_id", $row['employee_id']);
        define("ename", $row['ename']);
        define("surname", $row['surname']);
        define("job", $row['job']);
        define("wage", $row['wage']);
        define("eroom", $row['room']);
        define("login", $row['login']);
        define("password", $row['pass']);
        define("admin", $row['admin']);
        define("room_id", $row['room_id']);
        define("rname", $row['rname']);
    }
}
function getUser(){
    $user = [];
    $user['employee_id'] = employee_id == "employee_id"? null : employee_id;
    $user['ename'] = ename == "ename" ? null : ename;
    $user['surname'] = surname == "surname" ? null : surname;
    $user['job'] = job == "job" ? null : job;
    $user['wage'] = wage == "wage" ? null : wage;
    $user['eroom'] = eroom == "eroom" ? null : eroom;
    $user['login'] = login == "login" ? null : login;
    $user['pass'] = password == "password" ? null : password;
    $user['admin'] = admin == "admin" ? null : admin;
    $user['room_id'] = room_id == "room_id" ? null : room_id;
    $user['rname'] =  rname == "rname" ? null : rname;

    return $user;
}
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
        ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}
function navbar(){
    echo("
        <style>
            #menu{
                margin-top: 20px;
                
            }
            #menu ul{
                list-style-type: none;
            }
            #menu li
            {
                display: inline-block;
            }
        </style>
        <nav id='menu'>
            <ul>
                <li><a href='/' class='btn btn-primary'>Domů</a></li>                
                <li><a href='/phps/mistnosti.php' class='btn btn-primary'>Místnosti</a></li>
                <li><a href='/phps/lide.php' class='btn btn-primary'>Zaměstnanci</a></li>
                <li><a href='/phps/updateUser.php' class='btn btn-primary'>Změnit heslo</a></li>
                <li><a href='/phps/logout.php' class='btn btn-danger'>Odhlásit</a></li>
            </ul>
        </nav>
    ");
}