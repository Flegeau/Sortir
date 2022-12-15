<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville')]
class VilleController extends AbstractController
{
    private const MESSAGE_LOGIN = 'Vous devez d\'abord vous connecter';
    private const MESSAGE_NON_ADMIN = 'Vous n\'avez pas les droits pour accéder à cette page';

    #[Route('/', name: 'app_ville_index', methods: ['GET', 'POST'])]
    public function index(Request $request, VilleRepository $villeRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', self::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        } else {
            if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                $this->addFlash('danger', self::MESSAGE_NON_ADMIN);
                return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
            }
        }
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $villeRepository->save($ville, true);
            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ville/index.html.twig', [
            'villes' => $villeRepository->findBy([], array('nom' => 'asc')),
            'ville' => $ville,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ville_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville, VilleRepository $villeRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', self::MESSAGE_LOGIN);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        } else {
            if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                $this->addFlash('danger', self::MESSAGE_NON_ADMIN);
                return $this->redirectToRoute('app_sortie_list', [], Response::HTTP_SEE_OTHER);
            }
        }
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $villeRepository->save($ville, true);

            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ville/edit.html.twig', [
            'ville' => $ville,
            'form' => $form,
        ]);
    }
}
