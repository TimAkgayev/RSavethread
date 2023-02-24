<?php
session_start();

$postInfo = $_SESSION['postInfo'];
$postReplies = $_SESSION['postReplies'];

$insertion_success = false;

//save the comments to a database -----------------------------------------------------
$mysql_un = 'tim';
$mysql_pw = 'akgayev';
$mysql_db = 'reddit_db';

$mysql_link = new mysqli('localhost', $mysql_un, $mysql_pw, $mysql_db);
if($mysql_link->connect_error){
    die("Connection failed: " . $mysql_link->connect_error);
}




$rootPostID = $_SESSION['postInfo']['Post_ID'];
$rootPostTitle = $_SESSION['postInfo']['Post_title'];
$rootPostFullURL = $_SESSION['postInfo']['Post_link'];
$rootPostAuthor = $_SESSION['postInfo']['Post_author'];
$rootPostDatePosted = date("Y-m-d H:i:s", $_SESSION['postInfo']['Post_date']);
$rootPostScore = $_SESSION['postInfo']['Score'];
$rootPostText = $_SESSION['postInfo']['Post_text'];
$rootPostSubreddit = $_SESSION['postInfo']['Subreddit'];
$rootPostThumbnail = $_SESSION['postInfo']['Thumbnail_link'];

//check if the post already exists
// Escape any user input to prevent SQL injection
$primaryKeyValue = $mysql_link->real_escape_string($rootPostID);

// Prepare and execute the SELECT statement
$stmt = $mysql_link->prepare("SELECT * FROM post WHERE Post_ID = ?");
$stmt->bind_param("s", $primaryKeyValue);
$stmt->execute();
$result = $stmt->get_result();

// Check if a row with the given primary key already exists
if ($result->num_rows > 0) {
    // A row with the given primary key already exists, handle the error or update the existing row
    $saveURL = "confirmation.html?status=fail";
    header("Location: $saveURL");
    exit();

} else {
    // Insert the new row
    $mysql_query = $mysql_link->prepare("INSERT INTO post ( Post_ID, Post_title, Post_link, Post_author, Post_date, Score, Post_text, Subreddit, Thumbnail_link) VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?) ");


    // Bind the parameters
    $mysql_query->bind_param('sssssisss', 
        $rootPostID, 
        $rootPostTitle, 
        $rootPostFullURL, 
        $rootPostAuthor, 
        $rootPostDatePosted, 
        $rootPostScore,  
        $rootPostText, 
        $rootPostSubreddit,
        $rootPostThumbnail
    );

    // Execute the statement
    if ($mysql_query->execute() === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $mysql_query->error;
    }
}

//save the root post

$count = 0;
//now save all the comments
foreach($_SESSION['postReplies'] as $child)
{
   
    if($child['id'] == "j89vizw")
    {
        echo "break";
    }

    //this is just a regular comment
    $mysql_query = $mysql_link->prepare("INSERT INTO comment (Comment_author, Comment_date, CommentText, Score, Parent_ID, Comment_ID, Post_ID) VALUES( ?, ?, ?, ?, ?, ?, ?)");
    

    $author = $child['author'];
    $datePosted = date("Y-m-d H:i:s", $child['datePostedUTC']);
    $score = $child['score'];
    $text = $child['text'];
    $parentID = $child['parentID'];
    $id = $child['id'];
    

    // Bind the parameters
    $mysql_query->bind_param('sssisss',  $author, $datePosted, $text, $score, $parentID, $id, $rootPostID);



    // Execute the statement
    if($mysql_query->execute() == TRUE)
    {
        $insertion_success = true;
    }
    else{
        $insertion_success = false;
    }

    $mysql_query->close();
    
    $count++;
}


$mysql_link->close();



if ($insertion_success) {
    $status = "pass";
} else {
    $status = "fail";
}

$saveURL = "confirmation.html?status=$status";
header("Location: $saveURL");
exit();

?>