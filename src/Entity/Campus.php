<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"nom_campus"})
 * @ORM\Entity(repositoryClass=CampusRepository::class)
 */
class Campus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $nom_campus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCampus(): ?string
    {
        return $this->nom_campus;
    }

    public function setNomCampus(string $nom_campus): self
    {
        $this->nom_campus = $nom_campus;

        return $this;
    }

    public function __toString(){
        return $this->id.' - '.$this->nom_campus;
    }
}
