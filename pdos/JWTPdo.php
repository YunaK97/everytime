<?php

function isValidUser($id, $password){
    $pdo = pdoSqlConnect();
    $query = "SELECT id, password as hash FROM `USER` WHERE id= ?;";
    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return password_verify($password,$res[0]['hash']);
}
function getUserIdxByID($ID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT idx FROM USER WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$ID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['idx'];
}
function getSchoolIdxByID($ID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT school_idx FROM USER WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$ID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['school_idx'];
}