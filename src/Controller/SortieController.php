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

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
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
                $message = 'La sortie a été créée';
            } else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = 'La sortie a été créée et publiée';
            }
            $sortieRepository->save($sortie, true);

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        $nonAffichable = array('Créée', 'Annulée', 'Historisée');
        if (in_array($sortie->getEtat()->getLibelle(), $nonAffichable)) {
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository,
                         LieuRepository $lieuRepository, EtatRepository $etatRepository): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $lieus = $sortie->getLieu()->getVille()->getLieus();

        //Test sur l'id de l'utilisateur à voir
        if ($sortie->getOrganisateur()->getId() === 0 ||
            $sortie->getEtat()->getLibelle() !== 'Créée') {
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $message = '';
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);
            $sortie->setLieu($lieu);
            if ($form->get('enregistrer')->isClicked()) {
                $message = 'La sortie a été modifiée';
            }
            else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Ouverte')));
                $message = 'La sortie a été publiée';
            }

            $sortieRepository->save($sortie, true);

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
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
            $this->addFlash('notice', 'La sortie a été supprimée');
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
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
}
