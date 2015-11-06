<?php

//receives a word and search for the synonyms from the synonyms table
function get_synonyms($token){
    global $conn;
    //query to get an array of synonyms
    $sql = "SELECT term_synonyms FROM synonyms WHERE term = '$token'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $words=explode (",",$row['term_synonyms']);

    if (empty($words[0])){
        return NULL;
    }

    return $words;
}

//updates a term to the big index array (posting file)
function save_term_to_index($term,$hits, $docs_to_add,$index) {
    global $conn;

    $docs_old = get_docs_from_index($term, $index);
    $hits += count($docs_old);
    foreach ($docs_to_add as $doc) {    //check if the docs that we found appear already in the posting
        if (!in_array($doc, $docs_old)) {   //if not, edit
            $docs_old[] = $doc;
        }
    }
    $index[$term]["docs"] = $docs_old;
    $index[$term]["hits"] = $hits;
    return $index;
}

//check if a given term exist in the index list
//if true return the docs array (posting)
//if false return a new array
function get_docs_from_index($term, $index) {
    if (array_key_exists($term, $index)) {
        return $index[$term]["docs"];
    } else {
        return array();
    }
}

//gets the index table from the database as an associative array(index) and return it
function get_index() {
    global $conn;
    $index = array();
    $sql = "SELECT * FROM `index_table`";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $index[$row["word"]] = array("hits" => $row["hits"], "docs" => unserialize($row["documents"]));
        }
    }
    return $index;
}

//function that set the index table from a given associative array (index)
//this is useful when updating the database with new documents
function set_index($index) {
    global $conn;
    $values = array();
    foreach ($index as $key => $value) {
        $values[] = "('" . $conn->real_escape_string($key) . "'," . $value["hits"] . ",'" . $conn->real_escape_string(serialize($value["docs"])) . "')";
    }

    $sql = "INSERT"
            . " into `index_table`(`word`,`hits`, `documents`) "
            . "VALUES " . implode(",", $values)
            . " ON DUPLICATE KEY UPDATE hits=VALUES(hits) , documents=VALUES(documents)";

    $conn->query($sql);
}
