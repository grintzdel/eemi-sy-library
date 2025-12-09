<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    public $x; // ID de l'utilisateur

    #[ORM\Column(type: "string", length: 255)]
    public $y; // Nom de l'utilisateur

    #[ORM\Column(type: "array")]
    public $z = []; // Liste des livres empruntés

    public function i($b) {
        if (count($this->z) >= 3) {
            return "Trop de livres.";
        }
        $this->z[] = $b;
        return "OK.";
    }

    public function j($b) {
        $n = array_search($b, $this->z);
        if ($n !== false) {
            unset($this->z[$n]);
            return "Livre rendu.";
        }
        return "Pas trouvé.";
    }
}
