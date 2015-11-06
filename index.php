<?php
include_once './files_functions.php';
include_once './index_functions.php';
include_once './search_functions.php';

//connect to db
$servername = "localhost";
$username = "ruben_ben";
$password = "ruben_ben";
$dbname = "information_retrieval";

//array of background images
$bg = array('http://p1.pichost.me/i/74/1990772.jpg',
    'http://androidspin.com/wp-content/uploads/2014/10/bkg_01_january-750x400.jpg',
    'http://www.gizmobolt.com/wp-content/uploads/2014/10/bkg_09_september.jpg',
    'http://www.droid-life.com/wp-content/uploads/2014/10/bkg_04_april.jpg',
    'http://www.arbandroid.com/wp-content/uploads/2014/10/february.jpg',
    'http://paper-leaf.com/wp-content/uploads/2013/08/september-2013-calendar-wallpaper-1440px.png'); // array of backgrounds

$i = rand(0, count($bg)-1); // generate random number size of the array
$selectedBg = "$bg[$i]";

//function to bold the word searched by sending them in a query string
function getWords($words){
    $wordArray = str_word_count($words,1);
    global $flag_q; //flag for quotes

    //to add to the query string synonyms
    $synonyms = get_synonyms($words);

    foreach($synonyms as $synonym){
        $wordArray[] = $synonym;
    }

    if(!$flag_q) {
        //to handle expression with operator and stop word
        $wordArray = array_diff($wordArray, array('and', 'or', 'not', 'And', 'Or', 'Not', 'OR', 'AND', 'NOT'));
    }

    $wordsToSend = "";
    //aggregate all of the word searched for the query string to bold later
    foreach($wordArray as $word){
        $wordsToSend .= "words[]=" . $word . "&";
    }

    $wordsToSend = substr($wordsToSend, 0, -1);
    return $wordsToSend;
}

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$words = "";
global $flag_q;
if (isset($_GET["q"])) {
    //check for quotes
    if(mb_substr($_GET["q"], 0, 1) == '"' && mb_substr($_GET["q"], -1) == '"'){
        $flag_q =true;
    }

    $index = get_index();
    $results = search_words($_GET["q"],$index);
    if($flag_q){
        $_GET["q"] = substr($_GET["q"], 1, -1);
    }
    $words = getWords($_GET["q"]);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Search</title>
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <style>
            body {
                padding: 0px;
                color: #444;
            }


            #search-form, .form-control {
                margin-bottom: 20px;
            }
            .cover {
                width: 300px;
                height: 300px;
                display: inline-block;
                background-size: cover;
            }
            .cover:hover {
                cursor: pointer;
            }
            .cover.playing {
                border: 5px solid #e45343;
            }
            .container{
                margin-left: 80px;
                max-width: 100%;
            }
            .results-container{
                margin-left: 95px;
                max-width: 80%;
            }
            .wrapper{
                background-image: url("<?php echo $selectedBg; ?>");
                background-position: center;

            }
            hr {
                display: block;
                position: relative;
                padding: 0;
                margin: 8px auto;
                height: 0;
                width: 100%;
                max-height: 0;
                font-size: 1px;
                line-height: 0;
                clear: both;
                border: none;
                border-top: 1px solid #aaaaaa;
                border-bottom: 1px solid #ffffff;
            }
        </style>
    </head>
    <body>
<div class="wrapper">
    <div class="container">
        <h1>Song Search</h1>
        Search For Your Favorite Song</p>
        <form action="" method="" id="search-form">
            <input type="text" name="q" style="width: 400px" class="form-control" />
            <button type="submit" class="btn btn-primary" ><span class="glyphicon glyphicon-search"></span> Search</button>
           <a href="admin.php" role="button"  class="btn btn-primary"><span class="glyphicon glyphicon-user"></span> Admin?</a>
            <a href="info.php" role="button"  class="btn btn-primary"><span class="glyphicon glyphicon-info-sign"></span> Info</a>


        </form>
        </div>
</div>
        <div class="results-container">
            <?php

            if ((!empty($results)) || empty($_GET['q'])) {
                foreach ($results as $result) {
                    if ($result["visible"] == 1) {
                        ?>
                        <div>
                            <h3>
                                <a href="document.php?doc=<?php echo $result["id"]. "&". $words ?>"><?php echo $result["name"]?></a>
                            </h3>
                            <h4>
                                <?php echo $result["artist"] ?>
                            </h4>
                            <h5>
                                <?php echo $result["album"] ?>
                            </h5>
                            <p class="excerpt"><?php echo nl2br($result["summery"]) ?></p>

                                <?php
                                $filename = "storage/".$result["id"].".txt";
                                $lines = file($filename);
                                for($i=1; $i<4; $i++){
                                    echo "<h5>" . $lines[$i] . "</h5>";
                                }

                                ?>
<hr>
                        </div>
                        <?php
                    }
                }
            }
            else{
                echo "<h3> Sorry, No Result Found </h3>";
            }
            ?>
        </div>
    </body>
</html>
