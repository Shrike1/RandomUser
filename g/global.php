<?php
function database() {
    $db = new PDO("mysql:host=localhost;port=3306;dbname=random_api", "random_api", "********");
    return $db;
}

function getFirstName($id) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM first_name WHERE id = ?");
    $statement->execute(array($id));

    $info = $statement->FetchObject();

    return $info->name;
}

function getRandomFirstName($gender, $seed = null) {
    if ($gender < 1 || $gender > 2 || !is_numeric($gender)) {
        error("Supplied gender value is invalid");
    }

    $db = database();
    $statement = $db->prepare("SELECT * FROM first_name WHERE gender = ? ORDER BY RAND($seed) LIMIT 1");
    $statement->execute(array($gender));

    $info = $statement->FetchObject();

    return $info->id;
}

function getLastName($id) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM last_name WHERE id = ?");
    $statement->execute(array($id));

    $info = $statement->FetchObject();

    return $info->name;
}

function getRandomLastName($seed = null) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM last_name ORDER BY RAND($seed) LIMIT 1");
    $statement->execute();

    $info = $statement->FetchObject();

    return $info->id;
}

function getDomain($id) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM domain WHERE id = ?");
    $statement->execute(array($id));

    $info = $statement->FetchObject();
    return $info->domain;
}

function getRandomDomain($seed = null) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM domain ORDER BY RAND($seed) LIMIT 1");
    $statement->execute();

    $info = $statement->FetchObject();
    return $info->id;
}

function doesExist($table, $fieldname, $value) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM $table WHERE $fieldname = ?");
    $statement->execute(array($value));
    $info = $statement->FetchObject();
    
    if ($info != null) {
        return 1;
    } else {
        return 0;
    }
}

function numberOfEntries($table, $fieldname = null, $value = null) {
    $db = database();
    if ($fieldname == null) {
        $statement = $db->prepare("SELECT * FROM $table");
        $statement->execute();
    } else {
        $statement = $db->prepare("SELECT * FROM $table WHERE $fieldname = ?");
        $statement->execute(array($value));
    }

    $num_rows = $statement->rowCount();
    return $num_rows;
}

function error($reason) {
    trigger_error($reason, E_USER_ERROR);
}
?>