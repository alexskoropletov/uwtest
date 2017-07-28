<?php
namespace UWTest\Entity;

class Message
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $user;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $created_at;

    /**
     * @var int
     */
    protected $like_given;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;
    }

    public function getLikeGiven()
    {
        return $this->like_given;
    }

    public function setLikeGiven($like_given)
    {
        $this->like_given = $like_given;
    }
}