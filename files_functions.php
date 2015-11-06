<?php
//save the documents in the database
function save_doc($doc) {
    global $conn;
    //query to fetch the document
    $sql = "INSERT INTO "
            . "`documents` "
            . "(`name`, `artist`, `album`) "
            . "VALUES"
            . " ('". $conn->real_escape_string($doc[0]) . "','". $conn->real_escape_string($doc[1]) . "','" . $conn->real_escape_string($doc[2]) . "');";

    $conn->query($sql);

    $id = $conn->insert_id;

    $fp = fopen('storage/' . $id . '.txt', 'a');
    fwrite($fp, serialize($doc));
    fclose($fp);
    
    return $id;
}
