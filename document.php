<html>
<head>
    <meta charset="UTF-8">
    <title>Search</title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <style>
        body{margin-top:50px;
            color: #444;

        }
        .glyphicon { margin-right:10px; }
        .panel-body { padding:0px; }
        .panel-body table tr td { padding-left: 15px }
        .panel-body .table {margin-bottom: 0px; }
        .container{
            margin-left: 80px;
        }
    </style>
</head>
<body>
<div class="container">
    <button  class="btn btn-primary" onClick="javascript:window.print()"><span class="glyphicon glyphicon-print"></span>Print</button>
<?php
//getting the id from the query string
$query_string = $_SERVER['QUERY_STRING'];
$id = $_GET['doc'];

//fetching the documents from the storage folder
$filename = "storage/".$id.".txt";

//unserialize the document and displaying it
$lines = file_get_contents($filename);
$lines = unserialize($lines);
foreach($_GET['words'] as $index => $words)
$lines[3] = str_replace($_GET['words'][$index], "<b>" .$_GET['words'][$index]. "</b>",$lines[3]);



echo "<h2>" .$lines[0] . "</h2> <h3>".$lines[1]."</h3><h4>".$lines[2]."</h4>";

echo nl2br($lines[3]);

?>
</div>
</body>
</html>
