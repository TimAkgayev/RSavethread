<!DOCTYPE html>
<html> 
    <head>
        <title>Rsavethread</title>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' type='text/css' href='css/styles.css' />
    </head>

    <body>
        <header class ="center">
            <nav>
                <a href = "index.php" class = "nav-link"> Archive </a>
                <a href = "new_request.html" class = "nav-link"> Request </a>
            </nav>
        </header>
        <main id ="index-main">



<?php

$mysql_un = 'tim';
$mysql_pw = 'akgayev';
$mysql_db = 'reddit_db';

$mysql_link = new mysqli('localhost', $mysql_un, $mysql_pw, $mysql_db);

if($mysql_link->connect_error){
    die("Connection failed: " . $mysql_link->connect_error);
}

// Query the database
$sql = "SELECT * FROM post";
$result = mysqli_query($mysql_link, $sql);

if (!$result) {
    echo "Error: " . mysqli_error($conn);
    exit();
}



echo "<table cellspacing='10'>";
echo "<tr><th></th></th></th><th>Title</th><th>Date</th><th>Author</th><th>Subreddit</th><th>Link</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td><img src ='" . $row['Thumbnail_link'] . "'></td><td><a href='individual_post.php?Post_ID=" . urlencode($row['Post_ID']) . "' class = 'title-link'>" . $row['Post_title'] . "</a></td><td>" . $row["Post_date"] . "</td><td>" . $row["Post_author"] . "</td> <td>" . $row["Subreddit"] . "</td><td> <a target='blank' rel='noopener noreferrer' href= '" . $row["Post_link"] . "'> Link </a></td></tr>";
}
echo "</table>";


?>



        </main>
        <footer>
        </footer>

    </body>

</html>