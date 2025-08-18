<?php
require_once("defines.php");
require_once("pomm_conf.php");
require_once("func.php");
require_once("map_english.php");

$characters_db = new DBLayer($host, $user, $password, $db);
if (!$characters_db->isValid()) {
    header('Content-Type: application/json');
    echo json_encode([]); // Return empty array on error
    exit();
}
$characters_db->query("SET NAMES $database_encoding");

$query = $characters_db->query("SELECT `name`, `class`, `position_x`, `position_y`, `map` FROM `characters` WHERE `online` = 1");

$players = [];
if ($query) {
    while ($result = $characters_db->fetch_assoc($query)) {
        $players[] = $result;
    }
}

$characters_db->close();

header('Content-Type: application/json');
echo json_encode($players);