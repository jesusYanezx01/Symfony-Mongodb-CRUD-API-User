<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "user")]
class User
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: Type::STRING)]
    #[Assert\NotBlank(message: "Nombre no puede estar vacio")]
    private $name;

    #[MongoDB\Field(type: Type::STRING)]
    #[Assert\NotBlank(message: "Apellido no puede estar vacio")]
    private $lastName;

    #[MongoDB\Field(type: Type::INT)]
    #[Assert\NotBlank(message: "Edad no puede estar vacia")]
    #[Assert\GreaterThanOrEqual(
        value: 18, message: ("Tienes que ser +18 para poder registrarte")
    )]
    private $age;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

}