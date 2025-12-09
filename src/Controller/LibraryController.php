<?php
namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/library')]
class LibraryController extends AbstractController {
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/add-book', methods: ['POST'])]
    public function addBook(Request $req): JsonResponse {
        $data1 = json_decode($req->getContent(), true);
        
        if (!isset($data1['t']) || !isset($data1['a'])) {
            return new JsonResponse(['error' => 'Informations incomplètes'], 400);
        }
        
        $b = new Book();
        $b->b = $data1['t'];
        $b->c = $data1['a'];
        $this->em->persist($b);
        $this->em->flush();
        return new JsonResponse(['m' => 'OK']);
    }

    #[Route('/borrow', methods: ['POST'])]
    public function borrowBook(Request $req): JsonResponse {
        $data1 = json_decode($req->getContent(), true);
        
        if (!isset($data1['t']) || !isset($data1['u'])) {
            return new JsonResponse(['e' => '404'], 400);
        }

        $b = $this->em->getRepository(Book::class)->findOneBy(['b' => $data1['t']]);
        $x = $this->em->getRepository(User::class)->find($data1['u']);

        if (!$b || !$x) {
            return new JsonResponse(['e' => '404'], 400);
        }

        if ($b->d) {
            return new JsonResponse(['e' => 'Déjà pris'], 400);
        }

        $b->d = true;
        $x->z[] = $b;

        $this->em->flush();
        return new JsonResponse(['m' => 'OK']);
    }
}
