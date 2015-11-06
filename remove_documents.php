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
$servername = "localhost";
$username = "ruben_ben";
$password = "ruben_ben";
$dbname = "information_retrieval";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//removes documents by changing the visible field to 0
if (isset($_POST["remove"])) {
    
    $sql = "UPDATE `documents` SET `visible`= 0 WHERE `id` IN ( " . implode(",", $_POST["remove"]) . " )";
    $conn->query($sql);
    echo '<h3>removed!</h3>';
}

//presenting all of the visible files to the user
$sql = "SELECT * FROM `documents` WHERE `visible` = 1";

$results = $conn->query($sql);
?>

<h1>Remove Results</h1>
<?php if ($results->num_rows > 0): ?>
    <form action="" method="post">
        <table  class="table table-striped">
            <thead>
                <tr>
                    <th align="left">Name</th>
                    <th align="left">Artist</th>
                    <th align="left">Album</th>
                    <th align="left">Remove</th>
                </tr>
            </thead>

            <tbody>
    <?php while ($row = $results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row["name"] ?></td>
                        <td><?php echo $row["artist"] ?></td>
                        <td><?php echo $row["album"] ?></td>
                        <td><input type="checkbox" name="remove[]" value="<?php echo $row["id"] ?>"/></td>
                    </tr>
    <?php endwhile; ?>
            </tbody>
        </table>
        <input type="submit" value="Remove This Items"  class="btn btn-primary"/>
    </form>
    <h3><a href="index.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Start Searching</a></h3>
    <h3><a href="admin.php" role="button"  class="btn btn-primary"><span class="glyphicon glyphicon-user"></span> Back To Admin</a></h3>


<?php else: ?>
    <p>no documents</p>
<?php endif;
?>
</div>
    </body>
</html>



