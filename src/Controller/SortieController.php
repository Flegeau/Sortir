<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/sortie')]
class SortieController extends AbstractController {

    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SortieRepository $sortieRepository,
                        ParticipantRepository $participantRepository, CampusRepository $campusRepository,
                        LieuRepository $lieuRepository, EtatRepository $etatRepository): Response {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $participantRepository->find(3933);
            $campus = $campusRepository->find((int)$request->request->get('sortie')['campus']);
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);

            $sortie->setOrganisateur($organisateur);
            $sortie->addParticipant($organisateur);
            $sortie->setCampus($campus);
            $sortie->setLieu($lieu);

            if ($form->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Créée')));
            } else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
            }
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
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
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateStart($datestart));
            }
            if (!empty($_POST['dateend'])) {
                $dateend = $_POST['dateend'];
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateEnd($dateend));
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
            }
        }
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sortie,
            'campus' => $campusRepository->findAll(),
        ]);
    }


    #[Route('/{id}/modifier', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository, LieuRepository $lieuRepository): Response {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $lieus = $sortie->getLieu()->getVille()->getLieus();

        if ($form->isSubmitted() && $form->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'lieus' => $lieus,
            'form' => $form,
        ]);
    }

    #[Route('/accueil', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $sortieRepository->remove($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_publish', methods: ['POST'])]
    public function publish(Request $request, Sortie $sortie, EtatRepository $etatRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_in', methods: ['POST'])]
    public function inscription(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortieRepository->remove($sortie, true);
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_out', methods: ['POST'])]
    public function desitement(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortieRepository->remove($sortie, true);
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    //Utilisé par appel Ajax
    #[Route('/sortie_ville/{id}', name: 'app_sortie_ville', methods: ['GET'])]
    public function afficherVille(int $id, SerializerInterface $serializer, VilleRepository $villeRepository): Response {
        $ville = $villeRepository->find($id);
        $jsonContent = $serializer->serialize($ville, 'json', array('ignored_attributes' => ['lieus']));
        return new Response($jsonContent);
    }

    #[Route('/sortie_ville_lieu/{id}', name: 'app_sortie_ville_lieus', methods: ['GET'])]
    public function afficherLieuDeLaVille(int $id, SerializerInterface $serializer, VilleRepository $villeRepository): Response {
        $lieus = $villeRepository->find($id)->getLieus();
        $jsonContent = $serializer->serialize($lieus, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    #[Route('/sortie_lieu/{id}', name: 'app_sortie_lieu', methods: ['GET'])]
    public function afficherLieu(int $id, SerializerInterface $serializer, LieuRepository $lieuRepository): Response {
        $lieu = $lieuRepository->find($id);
        $jsonContent = $serializer->serialize($lieu, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    public function arrayFusion($array1, $array2){
        $result = [];
        foreach ($array2 as $sortie) {
            if (in_array($sortie, $array1)) {
                $result[] = $sortie;
            }
        }
        return $result;
    }
}
