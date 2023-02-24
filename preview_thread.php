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
           

         <a href="save_thread.php" id ="get-auth-button" class ="button-look" >
                Save
         </a>

<?php
require "Post.php";

session_start();
$auth_token = $_SESSION['Access_Token'];
$g_userAgent = 'Rsavethread/0.1 by VanillaSnake21';
ini_set('max_execution_time', 0);
$g_postURL = urldecode($_GET['redditLink']);
$g_postID = "";
$g_numCommentsLoaded = 0;


function GetProtectedCurlRequest($protectedURL, $authorizationHeader, &$outHTTPCode = NULL)
{
    global $g_userAgent;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $g_userAgent);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_URL, $protectedURL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $authorizationHeader);
    
    if($outHTTPCode != NULL){
        $outHTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    $result =  curl_exec($ch);
    curl_close($ch);

    return $result;
}

function GetAuthorizationHeader()
{
    //set up the header to contain the access token
    $http_headers['Authorization'] = 'Bearer ' . $_SESSION['Access_Token'];
    $header = array();
    foreach($http_headers as $key => $parsed_urlvalue) 
    {
        $header[] = "$key: $parsed_urlvalue";
    }

    return $header;
}



function Parse_Comment_Tree(&$parentComment, $replies)
{
    
    global $g_postURL, $g_postID, $g_userAgent, $g_numCommentsLoaded;
    

    
    foreach($replies as $reply)
    {
      

        //this is an extension with just user ids given
        if($reply['kind'] == "more")
        {
         
            $commentIDsStr = "";
            foreach($reply['data']['children'] as $commentIDString){
                $commentIDsStr = $commentIDsStr . $commentIDString . ',';
            }
            //remove the last comma
            $commentIDsStr = substr($commentIDsStr, 0, -1);

            $parameters = array("api_type" => "json", "children" => $commentIDsStr, "limit_children" => false, "link_id" => $g_postID);
            $parameters = http_build_query($parameters, "", '&');
            $baseUrl = "https://oauth.reddit.com/api/morechildren?";

            //set up the header to contain the access token
            $http_headers['Authorization'] = 'Bearer ' . $_SESSION['Access_Token'];
            $header = array();
            foreach($http_headers as $key => $parsed_urlvalue) 
            {
                $header[] = "$key: $parsed_urlvalue";
            }


            $fullURL = $baseUrl . $parameters;

            $ch = curl_init();

            
            $curl_options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_URL => $fullURL,
                CURLOPT_USERAGENT => $g_userAgent,
                CURLOPT_HTTPHEADER => $header
            );
            
            //set our option
            curl_setopt_array($ch, $curl_options);
           
            $result = curl_exec($ch);

            $jsonDecode = json_decode($result, true);


            $response = array(
                "result" => ($jsonDecode === null) ? $result : $jsonDecode
            );

            curl_close($ch);
            if(array_key_exists('data', $response['result']['json']))
            {
                //this replies list is unordered, children and parents are mixed
                $replyList = $response['result']['json']['data']['things'];
         
                $tree = array();
                foreach($replyList as &$firstObj)
                {
                    $parentFound = null;
                    foreach($replyList as &$secondObject)
                    {
                        
                        if($firstObj['data']['parent_id'] == $secondObject['data']['name'])
                        {
                        
                            $parentFound = &$secondObject;
                            break;
                        }
                    }
                    

                    if($parentFound)
                    {
                        //if an entry already exists append it, otherwise create it. 

                      

                        if(array_key_exists('replies', $parentFound['data'])){
                            if(is_array($parentFound['data']['replies'])){
                                if(array_key_exists('data', $parentFound['data']['replies'])){
                                    $parentFound['data']['replies']['data']['children'][] = &$firstObj;
                                }
                                else{
                                    $parentFound['data']['replies'] = array("kind"=> "Listing", "data" => array( "children" => array (&$firstObj)));
                                }
                            }
                            else{
                                $parentFound['data']['replies'] = array("kind"=> "Listing", "data" => array( "children" => array (&$firstObj)));
                            }
                        }
                        else{
                            $parentFound['data']['replies'] = array("kind"=> "Listing", "data" => array( "children" => array (&$firstObj)));
                        }
                    }
                    else{
                        $tree[] = &$firstObj;
                    }

                    unset($secondObject);
                    unset($parentFound);
                }
                unset($firstObj);
                
                Parse_Comment_Tree($parentComment, $tree);
            }

        }
        else
        {  

            
            $commentText = $reply['data']['body'];  
            $commentAuthor = $reply['data']['author'];
            $commentScore = $reply['data']['score'];
            $commentDateUTC = $reply['data']['created'];
            $commentID = $reply['data']['name'];
            $commentParentID = $reply['data']['parent_id'];


            $newComment = new Post($commentID, $commentText, $commentAuthor, $commentDateUTC, (int)$commentScore, $commentParentID);
            $parentComment->AddReply($newComment);
            $g_numCommentsLoaded++;
        
            if(is_array($reply['data']['replies']))
            {
                if(count($reply['data']['replies'])!=0){
                    $replyList = $reply['data']['replies']['data']['children'];
                    Parse_Comment_Tree($newComment, $replyList);
                }
            }
        }
    }

}

function Print_Tree($comment)
{

    if(!is_array($comment))
    {
        $replies = $comment->GetReplies();
        if(count($replies)!=0){
            //this is the root post, skip it
            echo("<ul class ='tree'>");
            Print_Tree($replies);
            echo("</ul");
        }
        
    }
    else
    { 
        foreach($comment as $reply)
        {
            $text = $reply->GetText();
            echo ("<li class = 'reply'><a href='#'> $text </a>");
            if(count($reply->GetReplies()) != 0)
            {
                echo("<ul> ");
                Print_Tree($reply->GetReplies());
                echo("</ul>");
            }
            else{
            echo ("</li>");
            }
        }
    }

}



//now that we have the token, we can make requests of reddit through this channel
//https://oauth.reddit.com

//original link
$unprotected_resource_url = urldecode($_GET['redditLink']);

//modified link to use the oauth portal 
$protected_resource_url = str_replace("https://www", "https://oauth", $unprotected_resource_url);

$httpCode = 0;
$header = GetAuthorizationHeader(); 
$result = GetProtectedCurlRequest($protected_resource_url, $header, $httpCode);
$json_decode = json_decode($result, true);


$response = array(
        'result' => (null === $json_decode) ? $result : $json_decode,
        'code' => $httpCode
    );


//echo('<strong>Response for fetch comments.json:</strong><pre>');
//print_r($response);
//echo('</pre>');


$postTitle = $response['result'][0]['data']['children'][0]['data']['title'];
$postAuthor =    $response['result'][0]['data']['children'][0]['data']['author'];
$fullUrl =   "https://reddit.com" . $response['result'][0]['data']['children'][0]['data']['permalink'];
$postDateUTC =  $response['result'][0]['data']['children'][0]['data']['created_utc'];
$postText =  $response['result'][0]['data']['children'][0]['data']['selftext'];
$postID = $response['result'][0]['data']['children'][0]['data']['name'];
global $g_postID;
$g_postID = $postID;
$postScore =  $response['result'][0]['data']['children'][0]['data']['score'];
$postSubreddit = $response['result'][0]['data']['children'][0]['data']['subreddit_name_prefixed'];
$postThumbnailURL = $response['result'][0]['data']['children'][0]['data']['thumbnail'];

$rootPost = new Post($postID, $postText, $postAuthor, $postDateUTC, $postScore, 0, $postSubreddit, $fullUrl, $postTitle, $postThumbnailURL);

$replies = $response['result'][1]['data']['children'];
Parse_Comment_Tree($rootPost, $replies);

$postDate =  date("Y-m-d H:i:s", $response['result'][0]['data']['children'][0]['data']['created_utc']);

$htmlThreadInfo = "
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
        Full Url: $fullUrl
    </div>
    <div class = 'post-info-section'>
        Date Posted: $postDate
    </div>
    <div class = 'post-info-section'>
        Number of comments: $g_numCommentsLoaded
    </div>
</div>
";

echo($htmlThreadInfo);
Print_Tree($rootPost);


$postInfo = array(
    'Post_title' => $postTitle,
    'Post_author' => $postAuthor,
    'Post_link' => $fullUrl,
    'Post_date' => $postDateUTC,
    'Post_text' => $postText,
    'Post_ID' => $postID,
    'Score' => $postScore,
    'Subreddit' => $postSubreddit,
    'Thumbnail_link' => $postThumbnailURL
);


$allPostChildrenAsArray = array();
$rootPost->GetAllChildrenBelowAsAssociativeArray($allPostChildrenAsArray, $rootPost);

$allPostChildren = array();
$rootPost->GetAllChildrenBelow($allPostChildren, $rootPost);

//save the variables for the next session
$_SESSION['postInfo'] = $postInfo;
$_SESSION['postReplies'] = $allPostChildrenAsArray;

?>

    

</main>
        <footer>
        </footer>

    </body>

</html>