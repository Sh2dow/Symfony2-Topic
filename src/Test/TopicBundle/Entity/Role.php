<?php

namespace Test\TopicBundle\Entity;
 
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
 
/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role implements RoleInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer $id
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string $name
     */
    protected $name;


    /**
     * Геттер для id.
     *
     * @return integer The id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Геттер для названия роли.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Сеттер для названия роли.
     *
     * @param string $value The name.
     */
    public function setName($value) {
        $this->name = $value;
    }

    /**
     * Конструктор класса
     */
    public function __construct() {
        
    }

    /**
     * Реализация метода, требуемого интерфейсом RoleInterface.
     * 
     * @return string The role.
     */
    public function getRole() {
        return $this->getName();
    }

    function __toString() {
      return $this->getName();
    }
}