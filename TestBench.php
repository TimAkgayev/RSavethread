<?php


$response = array(
    array( "kind"=> "t1", "data" => array( "id" => 25, "parent_id" => 30 )),
    array("kind"=> "t1", "data" => array( "id" => 30,"parent_id" => 0)),
    array("kind"=> "t1", "data" => array("id" => 32, "parent_id" => 0 )),
    array("kind"=> "t1", "data" => array("id" => 33,"parent_id" => 32)),
    array("kind"=> "t1", "data" => array("id" => 35,"parent_id" => 25))
);

$json_str = json_encode($response);

$tree = array();
foreach($response as &$firstObj)
{
    $parentFound = null;
    foreach($response as &$secondObject)
    {
        if($firstObj['data']['parent_id'] == $secondObject['data']['id'])
        {
            $parentFound = &$secondObject;
            break;
        }
        
    }
    unset($secondObject);

    if($parentFound)
    {
        
    }
    else{
        $tree[] = $firstObj;
    }

}
unset($firstObj);

print_r($tree);


?>


