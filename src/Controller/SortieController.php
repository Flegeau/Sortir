<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\SortieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface AS Serializer;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    private SortieService $service;
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;

    public function __construct(SortieService $service, SortieRepository $sortieRepository,
                                EtatRepository $etatRepository)
    {
        $this->service = $service;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
    }

    #[Route('/nouvelle', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CampusRepository $campusRepository, LieuRepository $lieuRepository,
                        EtatRepository $etatRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = '';
            $campus = $campusRepository->find((int)$request->request->get('sortie')['campus']);
            $lieu = $lieuRepository->find((int)$request->request->get('sortie')['lieu']);

            $sortie->setOrganisateur($this->getUser());
            $sortie->addParticipant($this->getUser());
            $sortie->setCampus($campus);
            $sortie->setLieu($lieu);

            if ($form->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatRepository->findSelonLibelle('Créée'));
                $message = $this->service::MESSAGE_CREATION;
            } else if ($form->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findSelonLibelle('Ouverte'));
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
    public function show(Request $request, Sortie $sortie): Response {
        if (!$this->getUser()) {
            $this->addFlash('danger', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('show'.$sortie->getId(), $request->request->get('_token'))) {
            if (!$this->service->estAffichable($sortie)) {
                $this->addFlash('info', $this->service::MESSAGE_NON_AFFICHABLE);
                return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/accueil', name: 'app_sortie_list', methods: ['GET', 'POST'])]
    public function list(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response {
        if (!$this->getUser()) {
            $this->addFlash('danger', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $this->controlerEtatsSorties();
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Filter $filtre */
            $filtre = $form->getData();
        }

        if (isset($filtre)) {
            $sortie = $sortieRepository->findFilterOrder($filtre, $this->getUser());
        } else {
            $sortie = $sortieRepository->findAllOrder();
        }

        return $this->renderForm('sortie/list.html.twig', [
            'sorties' => $sortie,
            'campus' => $campusRepository->findAll(),
            'form' => $form,
        ]);
    }


    #[Route('/{id}/modifier', name: 'app_sortie_edit', requirements: ['id'=> '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, LieuRepository $lieuRepository,
                         EtatRepository $etatRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('edit'.$sortie->getId(), $request->request->get('_token'))) {
            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);
            $lieus = $sortie->getLieu()->getVille()->getLieus();

            if ($this->getUser() != $sortie->getOrganisateur() ||
                !$this->service->estModifiable($sortie)) {
                $this->addFlash('danger', $this->service::MESSAGE_NON_MODIFIABLE);
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
                $sortie->setEtat($etatRepository->findSelonLibelle('Ouverte'));
                $message = $this->service::MESSAGE_PUBLICATION;
            }

                $this->sortieRepository->save($sortie, true);

                $this->addFlash('success', $message);
                return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'lieus' => $lieus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_sortie_delete', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie): Response {
        if (!$this->getUser()) {
            $this->addFlash('danger', $this->service::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token')) &&
            $this->service->estModifiable($sortie))
        {
            $this->sortieRepository->remove($sortie, true);
            $this->addFlash('info', $this->service::MESSAGE_SUPPRESSION);
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'app_sortie_cancel', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function cancel(Request $request, Sortie $sortie, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response {
        if ($this->isCsrfTokenValid('cancel'.$sortie->getId(), $request->request->get('_token'))) {
            if ($this->service->estAnnulable($sortie)) {
                if ($request->request->get('motif') != null) {
                    $sortie->setEtat($etatRepository->findOneBy(array('libelle' => 'Annulée')));
                    $sortie->setMotif($request->request->get('motif'));
                    $sortieRepository->save($sortie, true);
                    $this->addFlash('notice', $this->service::MESSAGE_ANNULATION);
                }
            } else {
                $this->addFlash('danger', $this->service::MESSAGE_NON_ANNULABLE);
            }
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/publish', name: 'app_sortie_publish', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function publish(Request $request, Sortie $sortie, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response {
        if ($this->isCsrfTokenValid('publish'.$sortie->getId(), $request->request->get('_token'))) {
            if ($this->service->estModifiable($sortie)) {
                $sortie->setEtat($etatRepository->findSelonLibelle('Ouverte'));
                $sortieRepository->save($sortie, true);
                $this->addFlash('notice', $this->service::MESSAGE_PUBLICATION);
            } else {
                $this->addFlash('danger', $this->service::MESSAGE_NON_PUBLIABLE);
            }
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/in', name: 'app_sortie_in', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function inscription(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
        if ($this->isCsrfTokenValid('in'.$sortie->getId(), $request->request->get('_token'))) {
            if ($this->service->estInscrivable($sortie)) {
                $sortie->addParticipant($this->getUser());
                $sortieRepository->save($sortie, true);
            } else {
                $this->addFlash('danger', $this->service::MESSAGE_NON_INSCRIVABLE);
            }
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/out', name: 'app_sortie_out', requirements: ['id'=> '\d+'], methods: ['POST'])]
    public function desitement(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response {
        if ($this->isCsrfTokenValid('out'.$sortie->getId(), $request->request->get('_token'))) {
            if ($this->service->estDesistable($sortie)) {
                $sortie->removeParticipant($this->getUser());
                $sortieRepository->save($sortie, true);
            } else {
                $this->addFlash('danger', $this->service::MESSAGE_NON_DESISTABLE);
            }
        }

        return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
    }

    //Utilisé par appel Ajax
    #[Route('/sortie_ville/{id}', name: 'app_sortie_ville', methods: ['GET'])]
    public function afficherVille(string $id, Serializer $serializer, VilleRepository $villeRepository): response {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $ville = $villeRepository->find((int)$id);
        $jsonContent = $serializer->serialize($ville, 'json', array('ignored_attributes' => ['lieus']));
        return new Response($jsonContent);
    }

    //Utilisé par appel Ajax
    #[Route('/sortie_ville_lieu/{id}', name: 'app_sortie_ville_lieus', methods: ['GET'])]
    public function afficherLieusDeLaVille(string $id, Serializer $serializer, VilleRepository $villeRepository): response {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $lieus = $villeRepository->find((int)$id)->getLieus();
        $jsonContent = $serializer->serialize($lieus, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    //Utilisé par appel Ajax
    #[Route('/sortie_lieu/{id}', name: 'app_sortie_lieu', methods: ['GET'])]
    public function afficherLieu(string $id, Serializer $serializer, LieuRepository $lieuRepository): response {
        if ($id == null || $id == 'undefined') {
            return new Response();
        }
        $lieu = $lieuRepository->find((int)$id);
        $jsonContent = $serializer->serialize($lieu, 'json', array('ignored_attributes' => ['ville', 'sorties']));
        return new Response($jsonContent);
    }

    private function controlerEtatsSorties(): void {
        foreach ($this->service->obtenirListeEtatsControlables() as $key => $value) {
            foreach ($this->sortieRepository->findParEtat($value) as $sortie) {
                if ($key === 0 && $this->service->estCloturable($sortie)) {
                    $sortie->setEtat(
                        $this->etatRepository->findSelonLibelle($this->service->obtenirNouvelEtat($key))
                    );
                } elseif ($key === 1 && $this->service->estOuvert($sortie)) {
                    $sortie->setEtat(
                        $this->etatRepository->findOneBy(['libelle'=>'Ouverte'])
                    );
                } elseif ($key === 1 && $this->service->estEnCours($sortie)) {
                    $sortie->setEtat(
                        $this->etatRepository->findSelonLibelle($this->service->obtenirNouvelEtat($key))
                    );
                } elseif ($key === 2 && $this->service->estTerminable($sortie)) {
                    $sortie->setEtat(
                        $this->etatRepository->findSelonLibelle($this->service->obtenirNouvelEtat($key))
                    );
                } elseif (($key === 3 || $key === 4) && $this->service->estHistorisable($sortie)) {
                    $sortie->setEtat(
                        $this->etatRepository->findSelonLibelle($this->service->obtenirNouvelEtat($key))
                    );
                }
                $this->sortieRepository->save($sortie, true);
            }
        }
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
