<?php
namespace UWTest\Entity;

class User
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="LikeGiven", mappedBy="user")
     */
    protected $like_given;

    /**
     * @OneToMany(targetEntity="Message", mappedBy="user")
     */
    private $messages;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}