<html>
<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <style>
        body{margin-top:50px;}
        .glyphicon { margin-right:10px; }
        .panel-body { padding:0px; }
        .panel-body table tr td { padding-left: 15px }
        .panel-body .table {margin-bottom: 0px; }
    </style>
</head>
<body>
<div class="container">
<?php

include_once './files_functions.php';
include_once './index_functions.php';

//connections parameters
$servername = "localhost";
$username = "ruben_ben";
$password = "ruben_ben";
$dbname = "information_retrieval";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$index = get_index();

$files = scandir('source/', 1);

$doc_arr = array();

//read the files for each file: save first 3 rows to 3 first spot in array
//and save the rest of the file in the forth place
foreach ($files as $file) {

    if ($file[0] != '.') {  //ignore file '.' and '..' that scandir creates

        $fp = fopen('source/' . $file, "r");
        $doc = array();
        $counter = 0;
        $size = 0;
        while (($line = trim(fgets($fp))) !== false) {
            if ($counter > 2) {
                break;
            }

            $doc[] = $line;
            $size += strlen($line);

            $counter++;
        }

        $doc[] = stream_get_contents($fp, -1, $size);   //the rest of the file
        //add the doc to the doc array
        $doc_arr[] = $doc;
    }
}

//get words (create an array with all the words in all the document, and the id of the doc in which they appear
$words_arr = array();
foreach ($doc_arr as $doc) {
    $id = save_doc($doc);
    $terms = str_word_count($doc[3], 1);
    foreach ($terms as $term) {
        $words_arr[] = array("term" => strtolower($term), "id" => $id);

    }
}

//sort
$term_arr = array();
foreach ($words_arr as $key => $row) {
    $term_arr[$key] = $row['term'];
}
array_multisort($term_arr, SORT_ASC, $words_arr);

//remove duplicates
$temp_index = array();  //will be the final index array (the posting files)
$prev_row["term"] = "";
$prev_row["id"] = "";
foreach ($words_arr as $row) {
    if ($row["term"] != $prev_row["term"]) {
        $temp_index[$row["term"]] = array("hits" => 1, "docs" => array($row["id"]));
    } else if ($row["id"] != $prev_row["id"]) {
        $temp_index[$row["term"]]["docs"][] = $row["id"];
        $temp_index[$row["term"]]["hits"] ++;
    }
    $prev_row = $row;
}

//insert to the index
foreach ($temp_index as $term => $docs) {
    $index = save_term_to_index($term,$docs["hits"],$docs["docs"],$index);
}

set_index($index);  //update the database

//delete files from source folder
$fp = opendir('source/');
while (false !== ($file = readdir($fp))) {
    if (is_file('source/' . $file)) {
        unlink('source/' . $file);
    }
}

?>
<h1> Added </h1>
    <h3><a href="index.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Start Searching</a></h3>
    <h3><a href="admin.php" role="button"  class="btn btn-primary"><span class="glyphicon glyphicon-user"></span> Back To Admin</a></h3>


</div>
</body>
</html>



