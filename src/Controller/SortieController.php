<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie')]
class SortieController extends AbstractController {

    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SortieRepository $sortieRepository): Response {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id'=> '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/accueil', name: 'app_sortie_list', methods: ['GET', 'POST'])]
    public function list(SortieRepository $sortieRepository, CampusRepository $campusRepository): Response {
        $sortie = $sortieRepository->findAllOrder();
        if (!empty($_POST['campus']) || !empty($_POST['sortiename']) || !empty($_POST['datestart']) || !empty($_POST['dateend']) || !empty($_POST['filter'])) {
            if (!empty($_POST['campus'])) {
                $campus = $_POST['campus'];
                    $sortie = $this->arrayFusion($sortie, $sortieRepository->findByCampus($campus));
            }
            if (!empty($_POST['sortiename'])) {
                $keyword = $_POST['sortiename'];
                $sortie = $this->arrayFusion($sortie, $sortieRepository->search($keyword));
            }
            if (!empty($_POST['datestart'])) {
                $datestart = $_POST['datestart'];
//                var_dump($datestart);
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateStart($datestart));
//                var_dump($sortieRepository->findByDateStart($datestart));
            }
            if (!empty($_POST['dateend'])) {
                $dateend = $_POST['dateend'];
//                var_dump($dateend);
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateEnd($dateend));
//                var_dump($sortieRepository->findByDateEnd($dateend));
            }
            if (!empty($_POST['filter'])) {
                if (in_array("organisateur", $_POST['filter'])) {
                    $sortie = $this->arrayFusion($sortie, $sortieRepository->findMySortie($this->getUser()));
                }
                if (in_array("inscrit", $_POST['filter'])) {
                    $sortie = $this->arrayFusion($sortie, $sortieRepository->findInscrit($this->getUser()));
                }
                if (in_array("non-inscrit", $_POST['filter'])) {
                    $sortie = $this->arrayFusion($sortie, $sortieRepository->findNonInscrit($this->getUser()));
                }
                if (in_array("passees", $_POST['filter'])) {
                    $sortie = $this->arrayFusion($sortie, $sortieRepository->findPasse());
                }
                if (in_array("inscrit", $_POST['filter']) && in_array("non-inscrit", $_POST['filter'])) {
                    $sortie = null;
                }
            }
        }
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sortie,
            'campus' => $campusRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $sortieRepository->remove($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    public function arrayFusion($array1, $array2){
        $result = [];
        foreach ($array2 as $sortie) {
            if (in_array($sortie, $array1)) {
                array_push($result, $sortie);
            }
        }
        return $result;
    }
}
