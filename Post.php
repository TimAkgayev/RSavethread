

<?php

class Post
{
    public function __construct($id, $text, $author, $dateUTC, $likes, $dislikes, $fullURL = NULL, $title = NULL)
    {
        $this->children = array();
        $this->text = $text;
        $this->author = $author;
        $this->datePostedUTC = $dateUTC;
        $this->likes = $likes;
        $this->dislikes = $dislikes;
        $this->title = $title;
        $this->fullURL = $fullURL;
        $this->id = $id;
    }

    private $children;
    private $text;
    private $author;
    private $datePostedUTC;
    private $likes;
    private $dislikes;
    private $id;

    //only applicable to root post (ie the OP)
    private $title; 
    private $fullURL;

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

    public function GetLikes()
    {
        return $this->likes;
    }

    public function GetDislikes()
    {
        return $this->dislikes;
    }

    public function GetTitle()
    {
        return $this->title;
    }

    public function GetFullURL()
    {
        return $this->fullURL;
    }
    
}


?>