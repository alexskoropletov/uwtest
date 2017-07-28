<?php
namespace UWTest\Entity;

class LikeGiven
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $message;

    /**
     * @var int
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}