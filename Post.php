

<?php


class Post
{
    public function __construct($id, $text, $author, $dateUTC, $score, $parentID, $subreddit = NULL, $fullURL = NULL, $title = NULL, $thumbnailURL = NULL)
    {
        $this->children = array();
        $this->text = $text;
        $this->author = $author;
        $this->datePostedUTC = $dateUTC;
        $this->score = $score;
        $this->title = $title;
        $this->fullURL = $fullURL;
        $this->id = $id;
        $this->subreddit = $subreddit;
        $this->parentID = $parentID;
        $this->thumbnailURL = $thumbnailURL;
    }

    private $children;
    private $text;
    private $author;
    private $datePostedUTC;
    private $score;
    private $id;
    private $parentID;

    //only applicable to root post (ie the OP)
    private $title; 
    private $fullURL;
    private $subreddit;
    private $thumbnailURL; 

    public function GetAllChildrenBelow(&$outChildrenArray, $post)
    {
        $currentChildren = $post->GetReplies();

        foreach($currentChildren as $child)
        {
            $outChildrenArray[] = $child;
            
            if(count($child->GetReplies())){
                $this->GetAllChildrenBelow($outChildrenArray, $child);
            }
        }
    }

    public function GetAllChildrenBelowAsAssociativeArray(&$outChildrenArray, $post)
    {
        $currentChildren = $post->GetReplies();

        foreach($currentChildren as $child)
        {

            $outChildrenArray[] = array(
                'text' => $child->GetText(),
                'author' => $child->GetAuthor(),
                'datePostedUTC' => $child->GetDatePostedUTC(),
                'score' => $child->GetScore(),
                'title' => $child->GetTitle(),
                'fullURL' => $child->GetFullURL(),
                'parentID' => $child->GetParentID(),
                'id' => $child->GetID(),
                'subreddit' => $child->GetSubreddit(),
                'thumbnail' => $child->GetThumbnailURL()
            );
            
            
            if(count($child->GetReplies())){
                $this->GetAllChildrenBelowAsAssociativeArray($outChildrenArray, $child);
            }
        }
    }

    public function GetThumbnailURL()
    {
        return $this->thumbnailURL;
    }
 
    public function GetReplies()
    {
        return $this->children;
    }

    public function AddReply($reply)
    {
        $this->children[] = $reply;
    }

    public function GetText()
    {
        return $this->text;
    }

    public function GetAuthor()
    {
        return $this->author;
    }

    public function GetDatePostedUTC()
    {
        return $this->datePostedUTC;
    }

    public function GetDatePosted()
    {
        return date("Y-m-d H:i:s", $this->datePostedUTC);
    }

    public function GetScore()
    {
        return (int)$this->score;
    }

    public function GetTitle()
    {
        return $this->title;
    }

    public function GetFullURL()
    {
        return $this->fullURL;
    }

    public function GetParentID()
    {
        return $this->parentID;
    }

    public function GetID()
    {
        return $this->id;
    }
    
    public function GetSubreddit()
    {
        return $this->subreddit;
    }
}


?>
