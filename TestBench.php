<?php

$mysql_un = 'tim';
$mysql_pw = 'akgayev';
$mysql_db = 'reddit_db';

$mysql_link = new mysqli('localhost', $mysql_un, $mysql_pw, $mysql_db);

if($mysql_link->connect_error){
    die("Connection failed: " . $mysql_link->connect_error);
}
else{
    echo("Connection established.");
}



for($i = 0; $i < count($rootPost); $i++)
{
   
    $mysql_query = $mysql_link->prepare("INSERT INTO Comments (Post_title, Post_link, Comment_author, Comment_date, Likes, Comment_text) VALUES( ?, ?, ?, ?, ?, ?, ?) ");
    
    // Bind the parameters
    $mysql_query->bind_param("ssssii", $postTitle, $postLink, $postAuthor, $postDate, $postLikes, $postDislikes, $postText);

    
    // Execute the statement
    if ($mysql_query->execute() === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $mysql_query->error;
    }

    $mysql_query->close();
    
}
*/
$mysql_link->close();

?>


