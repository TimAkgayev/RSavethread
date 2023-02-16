<?php

if (isset($_GET["error"]))
{
    echo("<pre>OAuth Error: " . $_GET["error"]."\n");
    echo('<a href="index.php">Retry</a></pre>');
    die;
}

$authorizeUrl = 'https://ssl.reddit.com/api/v1/authorize';
$accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
$clientId = 'TAyDlGWy2wWphA';
$clientSecret = '3F-KaRUAVWUy-ZYgrg5Hp6vIfgA';
$userAgent = 'Rsavethread/0.1 by VanillaSnake21';

$redirectUrl = "http://localhost:8020/get_authorization_and_token.php";


// if we haven't gotten the user authorization code, then get it now
if (!isset($_GET["code"]))
{
    //defaults to temporary access token
    $parameters = array( 
        'response_type' => 'code', 
        'client_id'=> $clientId, 
        'redirect_uri'=> $redirectUrl, 
        'scope' => "read",  //set this to whatever scope you like
        'state' => "Sjnskqodfmasdfj"); //random string that you create to keep track of the authorization request
    
    //http_build_query concats all the parameters into a string that could be used in the url
    $auth_url = $authorizeUrl . '?' . http_build_query($parameters, "", '&');
    
    // header ("Location: <url>") redirects the page to the url we have generated
    header("Location: " . $auth_url);
    
    //exit the script with a message, just to be safe
    die("Redirected to reddit");
}


//if we now have the user authentication code, we can proceed to getting the access token
else
{
    //build a cURL request
    $parameters = array("code" => $_GET["code"], "redirect_uri" => $redirectUrl, 'grant_type' => "authorization_code");
    $http_headers['Authorization'] = 'Basic ' . base64_encode($clientId .  ':' . $clientSecret);
    $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POST => true,
            CURLOPT_URL => $accessTokenUrl,
            CURLOPT_USERAGENT => $userAgent
        );
    
    //build an html string from the parameters
    $parameters = http_build_query($parameters, "", '&');
    $curl_options[CURLOPT_POSTFIELDS] = $parameters;
   
    //buld the request header
    $header = array();
    foreach($http_headers as $key => $parsed_urlvalue) {
        $header[] = "$key: $parsed_urlvalue";
    }
    $curl_options[CURLOPT_HTTPHEADER] = $header;

       
    
    $ch = curl_init();
    
    //set our option
    curl_setopt_array($ch, $curl_options);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    //make the actual request to the reddit's server             
    $result = curl_exec($ch);
    
    //make sure everything went well with the actual call
    if ($curl_error = curl_error($ch)) 
    {
        //handle it any way you like
        //ex: echo ($curl_error);
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    $json_decode = json_decode($result, true);
    curl_close($ch);
    
    $response = array ( 
        'result' => (null === $json_decode) ? $result : $json_decode,
        'code' => $http_code,
        'content_type' => $content_type);
   
    
    //this is the final result reddit's server sent back, it's an array that contains the access token among other things
    //$accessTokenResult['access_token"] - that's the actual access token
    //$accessTokenResult['token_type'] - the type of token, should be bearer in this case
    //$accessTokenResult['expires_in"] - the amount of time the token will expire in (in Unix Epoch seconds)
    //$accessTokenResult['scope'] - the scope this token has, should be the scope we requested earlier
    $accessTokenResult = $response["result"];
    
    session_start();
    $_SESSION['Access_Token']= $accessTokenResult['access_token'];
  
   
    
    header("Location: save_thread.html");
    die("Redirected to custom request page");

/*
    //now that we have the token, we can make requests of reddit through this channel
    //https://oauth.reddit.com
    
    //create an example request, pull user information
    $protected_resource_url = "https://oauth.reddit.com/api/v1/me.json";

    //set up the header to contain the access token
    $http_headers['Authorization'] = 'Bearer ' . $accessTokenResult['access_token'];
    $http_method = 'GET';
    $form_content_type = 1;
    
   
    $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );
    
    
    $curl_options[CURLOPT_URL] = $protected_resource_url;
    
    if (is_array($http_headers)) 
    {
        $header = array();
        foreach($http_headers as $key => $parsed_urlvalue) 
        {
            $header[] = "$key: $parsed_urlvalue";
        }
        $curl_options[CURLOPT_HTTPHEADER] = $header;
    }

    $ch = curl_init();
    curl_setopt_array($ch, $curl_options);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
   
    
    //make the call
    $result = curl_exec($ch);
    
     //make sure everything was ok with the call
    if ($curl_error = curl_error($ch)) 
    {
        //handle it any way you like
        //ex: echo ($curl_error);
    }
    
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $json_decode = json_decode($result, true);
    
    curl_close($ch);
    
    $response = array(
            'result' => (null === $json_decode) ? $result : $json_decode,
            'code' => $http_code,
            'content_type' => $content_type
        );
    
    
 
    echo('<strong>Response for fetch me.json:</strong><pre>');
    print_r($response);
    echo('</pre>');
    */
}
?>