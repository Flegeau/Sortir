<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\ControleSortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    private const MESSAGE_CREATION = 'La sortie a été créée';
    private const MESSAGE_PUBLICATION = 'La sortie a été publiée';
    private const MESSAGE_MODIFICATION = 'La sortie a été modifiée';
    private const MESSAGE_SUPPRESSION = 'La sortie a été supprimée';

    private ControleSortie $service;
    public function __construct(ControleSortie $service)
    {
        $this->service = $service;
    }

    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAllOrder(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SortieRepository $sortieRepository,
                        ParticipantRepository $participantRepository, CampusRepository $campusRepository,
                        LieuRepository $lieuRepository, EtatRepository $etatRepository): Response
    {
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
                $message = self::MESSAGE_CREATION;
            } else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = self::MESSAGE_PUBLICATION;
            }
            $sortieRepository->save($sortie, true);

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        if (!$this->service->estAffichable($sortie)) {
            return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/accueil', name: 'app_sortie_list', methods: ['GET', 'POST'])]
    public function list(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response {
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        $sortie = $sortieRepository->findAllOrder();
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Filter $filtre */
            $filtre = $form->getData();
        }

        if (isset($filtre)) {
            if ($filtre->getCampus() != null) {
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByCampus($filtre->getCampus()->getNom()));
            }
            if ($filtre->getNom() != null) {
                $sortie = $this->arrayFusion($sortie, $sortieRepository->search($filtre->getNom()));
            }
            if ($filtre->getDateStart() != null) {
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateStart($filtre->getDateStart()));
            }
            if ($filtre->getDateEnd() != null) {
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findByDateEnd($filtre->getDateEnd()));
            }
            if ($filtre->getOrganisateur()) {
                var_dump($filtre->getOrganisateur());
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findMySortie($this->getUser()));
            }
            if ($filtre->getInscrit()) {
                var_dump($filtre->getInscrit());
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findInscrit($this->getUser()));
            }
            if ($filtre->getNonInscrit()) {
                var_dump($filtre->getNonInscrit());
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findNonInscrit($this->getUser()));
            }
            if ($filtre->getPassees()) {
                var_dump($filtre->getPassees());
                $sortie = $this->arrayFusion($sortie, $sortieRepository->findPasse());
            }
        }

        return $this->renderForm('sortie/list.html.twig', [
            'sorties' => $sortie,
            'campus' => $campusRepository->findAll(),
            'form' => $form,
        ]);
    }


    #[Route('/{id}/modifier', name: 'app_sortie_edit', requirements: ['id'=> '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository, LieuRepository $lieuRepository): Response {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $lieus = $sortie->getLieu()->getVille()->getLieus();

        //Test sur l'id de l'utilisateur à voir
        if ($sortie->getOrganisateur()->getId() === 0 ||
            !$this->service->estModifiable($sortie))
        {
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $message = '';
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);
            $sortie->setLieu($lieu);
            if ($form->get('enregistrer')->isClicked()) {
                $message = self::MESSAGE_MODIFICATION;
            }
            else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = self::MESSAGE_PUBLICATION;
            }

            $sortieRepository->save($sortie, true);

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
    public function delete(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $sortieRepository->remove($sortie, true);
            $this->addFlash('notice', self::MESSAGE_SUPPRESSION);
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_cancel', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function cancel(Request $request, Sortie $sortie, EtatRepository $etatRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_publish', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function publish(Request $request, Sortie $sortie, EtatRepository $etatRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_in', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function inscription(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
//        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//            $sortieRepository->remove($sortie, true);
//        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accueil', name: 'app_sortie_out', requirements: ['id'=> '\d+'], methods: ['POST'])]
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
