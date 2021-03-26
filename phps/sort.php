<?php
require_once("include/connect.php");
function sortR($sortCol, $sortDir)
{
    $lcol = ["name", "no", "rphone"];
    $ldir = ["asc", "desc"];
    $pdo = dbConnect();

    if ($sortDir == '' || $sortCol == '') return "no data";
    else if (in_array($sortDir, $ldir) == true && $sortDir == 'asc') {
        if (in_array($sortCol, $lcol) == true && $sortCol == 'name') {
            return $pdo->query('SELECT * FROM `room` ORDER BY `name`');
        } elseif (in_array($sortCol, $lcol) == true && $sortCol == 'no')
            return $pdo->query('SELECT * FROM `room` ORDER BY `no`');
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'rphone')
            return $pdo->query('SELECT * FROM `room` ORDER BY `phone`');
    }
    elseif (in_array($sortDir, $ldir) == true && $sortDir == 'desc') {
        if (in_array($sortCol, $lcol) == true && $sortCol == 'name')
            return $pdo->query('SELECT * FROM `room` ORDER BY `name` DESC');
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'no')
            return $pdo->query('SELECT * FROM `room` ORDER BY `no` DESC');
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'rphone')
            return $pdo->query('SELECT * FROM `room` ORDER BY `phone` DESC');
    }
}
function sortE($sortCol, $sortDir)
{
    $lcol = ["surname", "job", "name", "room", "phone"];
    $ldir = ["asc", "desc"];
    $pdo = dbConnect();

    if ($sortDir == '' || $sortCol == '') return "no data";
    else if (in_array($sortDir, $ldir) == true && $sortDir == 'asc') {
        if (in_array($sortCol, $lcol) == true && $sortCol == 'surname')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `surname`");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'room')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `employee`.`room`");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'phone')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `phone`");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'job')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `job`");
    }
    elseif (in_array($sortDir, $ldir) == true && $sortDir == 'desc') {
        if (in_array($sortCol, $lcol) == true && $sortCol == 'surname')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `surname` DESC");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'room')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `room` DESC");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'phone')
            return $pdo->query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `phone` DESC");
        elseif (in_array($sortCol, $lcol) == true && $sortCol == 'job')
            return $pdo-> query("
                SELECT `employee_id`, `surname`, `employee`.`name`, `phone`, `job`, `room`.`name` AS `rname` 
                FROM `employee`, `room`
                WHERE `room_id`= `employee`.`room`
                ORDER BY `job` DESC");
    }
}
