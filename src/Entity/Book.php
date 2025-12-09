<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Book {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    public $a; // ID

    #[ORM\Column(type: "string", length: 255)]
    public $b; // Titre

    #[ORM\Column(type: "string", length: 255)]
    public $c; // Auteur

    #[ORM\Column(type: "boolean")]
    public $d = false; // Statut d'emprunt

    #[ORM\Column(type: "datetime", nullable: true)]
    public $e; // Date d'emprunt

    #[ORM\Column(type: "datetime", nullable: true)]
    public $f; // Date de retour

    public function g() { // Emprunter un livre
        if ($this->d) {
            return "Déjà pris.";
        }
        $this->d = true;
        $this->e = new \DateTime();
        return "Pris.";
    }

    public function h() { // Retourner un livre
        if (!$this->d) {
            return "Non emprunté.";
        }
        $this->d = false;
        $this->f = new \DateTime();
        return "Retourné.";
    }
}
