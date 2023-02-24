<!DOCTYPE html>
<html> 
    <head>
        <title>Rsavethread</title>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' type='text/css' href='css/styles.css' />

        <script>

            window.onload = function() 
                        {
                            var tree = document.querySelectorAll('ul.tree a:not(:last-child)');
                            for(var i = 0; i < tree.length; i++)
                            {
                                tree[i].addEventListener('click', function(e) {
                                    var parent = e.target.parentElement;
                                    var classList = parent.classList;
                                    if(classList.contains("open")) 
                                    {
                                        classList.remove('open');
                                        var opensubs = parent.querySelectorAll(':scope .open');
                                        for(var i = 0; i < opensubs.length; i++)
                                        {
                                            opensubs[i].classList.remove('open');
                                        }
                                    } 
                                    else 
                                    {
                                        classList.add('open');
                                    }
                                    e.preventDefault();
                                });
                            }
                        };
        </script>
    </head>

    <body>
        <header class ="center">
            <nav>
                <a href = "index.php" class = "nav-link"> Archive </a>
                <a href = "new_request.html" class = "nav-link"> Request </a>
            </nav>
        </header>
        <main>


<?php 
session_start();

function compareByScore($a, $b) {
    return $b['Score'] - $a['Score'];
}

function Print_Tree($parent, $commentList)
{
    if(array_key_exists('root', $parent)){
        //this is a root post, skip it
        echo("<ul class ='tree'>");

        //sort the list by descending score
        usort($commentList, 'compareByScore');

        //go through each top level comment
        foreach($commentList as $comment){

            $text = $comment['CommentText'];
            echo ("<li class = 'reply'><a href='#'> $text </a>");

            if(array_key_exists('Replies', $comment)){
                echo("<ul> ");
                Print_Tree($comment, $comment['Replies']);
                echo("</ul>");
            }

            echo ("</li>");
        }

        echo("</ul>");

    }
    else{
        foreach($commentList as $comment){

            $text = $comment['CommentText'];
            echo ("<li class = 'reply'><a href='#'> $text </a>");

            if(array_key_exists('Replies', $comment)){
                echo("<ul> ");
                $replies = $comment['Replies'];
                usort($replies, 'compareByScore');
                Print_Tree($comment, $replies);
                echo("</ul>");
            }
               
            echo ("</li>");
            
        }
    }
}


function CreateCommentTree(&$outTree, $commentList)
{   
    foreach($commentList as &$firstObj)
    {
        $parentFound = null;
        foreach($commentList as &$secondObject)
        {
            
            if($firstObj['Parent_ID'] == $secondObject['Comment_ID'])
            {
              
                $parentFound = &$secondObject;
                break;
            }
        }
        

        if($parentFound)
        {
            //if an entry already exists append it, otherwise create it. 
            if(array_key_exists('Replies', $parentFound)){
                $parentFound['Replies'][] = &$firstObj;
            }
            else{
                $parentFound['Replies'] = array(&$firstObj);
            }
        }
        else{
            $outTree[] = &$firstObj;
        }

        unset($secondObject);
        unset($parentFound);
    }
    unset($firstObj);
}

$g_postID = urldecode($_GET['Post_ID']);

$mysql_un = 'tim';
$mysql_pw = 'akgayev';
$mysql_db = 'reddit_db';

$mysql_link = new mysqli('localhost', $mysql_un, $mysql_pw, $mysql_db);

if($mysql_link->connect_error){
    die("Connection failed: " . $mysql_link->connect_error);
}

// Query the database
$sqlPost = "SELECT * FROM post WHERE Post_ID = '" . $g_postID ."'";
$sqlComment = "SELECT * FROM comment WHERE Post_ID = '" . $g_postID . "'";
$sqlCommentCount = "SELECT COUNT(*) FROM comment WHERE Post_ID = '" . $g_postID ."'";

$resultPost = mysqli_query($mysql_link, $sqlPost);
$resultComment = mysqli_query($mysql_link, $sqlComment);
$resultCommentCount = mysqli_query($mysql_link, $sqlCommentCount);
if (!$resultPost || !$resultComment) {
    echo "Error: " . mysqli_error($conn);
    exit();
}

$rowPost = mysqli_fetch_assoc($resultPost);
$commentCount = mysqli_fetch_row($resultCommentCount);

$postTitle = $rowPost['Post_title'];
$postSubreddit = $rowPost['Subreddit'];
$postAuthor = $rowPost['Post_author'];
$fullURL = $rowPost['Post_link'];
$postDate = $rowPost['Post_date'];
$numCommentsLoaded = $commentCount[0];
$postThumbnailURL = $rowPost['Thumbnail_link'];

$htmlPostInfo = "
<div id = 'post-info'>
    <div style='margin:0em auto; text-align:center; display:block' class = 'post-info-section'>
        <img style ='display: inline-block; max-width:100%; height: auto' src='$postThumbnailURL'>
    </div>
    <div class = 'post-info-section'>
        Title: $postTitle
    </div>
    <div class = 'post-info-section'>
        Subreddit: $postSubreddit
    </div>
    <div class = 'post-info-section'>
        Author: $postAuthor
    </div>
    <div class = 'post-info-section'>
        Full Url: $fullURL
    </div>
    <div class = 'post-info-section'>
        Date Posted: $postDate
    </div>
    <div class = 'post-info-section'>
        Number of comments: $numCommentsLoaded
    </div>
</div>
";

echo($htmlPostInfo);

$commentList = array();

while($row = mysqli_fetch_assoc($resultComment)){
    $commentList[] = $row;
}


$commentTree = array();
CreateCommentTree($commentTree, $commentList);
$rootTree = array("root"=> true, "Replies" => $commentTree);

Print_Tree($rootTree, $rootTree['Replies']);

?>