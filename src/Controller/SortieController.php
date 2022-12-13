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
use App\Service\SortieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    private SortieRepository $sortieRepository;
    private SortieService $service;
    public function __construct(SortieRepository $sortieRepository, SortieService $service)
    {
        $this->sortieRepository = $sortieRepository;
        $this->service = $service;
    }

    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(): Response {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $this->sortieRepository->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantRepository $participantRepository,
                        CampusRepository $campusRepository, LieuRepository $lieuRepository,
                        EtatRepository $etatRepository): Response
    {
        /*
        if (!$this->getUser()) {
            $this->addFlash('warning', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        */
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = '';
            $organisateur = $participantRepository->find(15);
            $campus = $campusRepository->find((int)$request->request->get('sortie')['campus']);
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);

            $sortie->setOrganisateur($organisateur);
            $sortie->addParticipant($organisateur);
            $sortie->setCampus($campus);
            $sortie->setLieu($lieu);

            if ($form->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Créée')));
                $message = $this->service::MESSAGE_CREATION;
            } else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = $this->service::MESSAGE_PUBLICATION;
            }
            $this->sortieRepository->save($sortie, true);

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id'=> '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if (!$this->service->estAffichable($sortie)) {
            $this->addFlash('notice', $this->service::MESSAGE_NON_AFFICHABLE);
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }
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
    public function edit(Request $request, Sortie $sortie, LieuRepository $lieuRepository,
                         EtatRepository $etatRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $lieus = $sortie->getLieu()->getVille()->getLieus();

        if ($this->getUser() === $sortie->getOrganisateur()->getId() ||
            !$this->service->estModifiable($sortie))
        {
            $this->addFlash('warning', $this->service::MESSAGE_NON_MODIFIABLE);
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $message = '';
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);
            $sortie->setLieu($lieu);
            if ($form->get('enregistrer')->isClicked()) {
                $message = $this->service::MESSAGE_MODIFICATION;
            }
            else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = $this->service::MESSAGE_PUBLICATION;
            }

            $this->sortieRepository->save($sortie, true);

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'lieus' => $lieus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token')) &&
            $this->service->estModifiable($sortie))
        {
            $this->sortieRepository->remove($sortie, true);
            $this->addFlash('notice', $this->service::MESSAGE_SUPPRESSION);
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
    public function afficherVille(string $id, SerializerInterface $serializer, VilleRepository $villeRepository): response
    {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $ville = $villeRepository->find((int)$id);
        $jsonContent = $serializer->serialize($ville, 'json', array('ignored_attributes' => ['lieus']));
        return new Response($jsonContent);
    }

    #[Route('/sortie_ville_lieu/{id}', name: 'app_sortie_ville_lieus', methods: ['GET'])]
    public function afficherLieusDeLaVille(string $id, SerializerInterface $serializer, VilleRepository $villeRepository): response
    {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $lieus = $villeRepository->find((int)$id)->getLieus();
        $jsonContent = $serializer->serialize($lieus, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    #[Route('/sortie_lieu/{id}', name: 'app_sortie_lieu', methods: ['GET'])]
    public function afficherLieu(string $id, SerializerInterface $serializer, LieuRepository $lieuRepository): response
    {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $lieu = $lieuRepository->find((int)$id);
        $jsonContent = $serializer->serialize($lieu, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    public function arrayFusion($array1, $array2): array {
        $result = [];
        foreach ($array2 as $sortie) {
            if (in_array($sortie, $array1)) {
                $result[] = $sortie;
            }
        }
        return $result;
    }
}
