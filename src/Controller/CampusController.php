<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus')]
class CampusController extends AbstractController
{
    private const MESSAGE_LOGIN = 'Vous devez d\'abord vous connecter';
    private const MESSAGE_NON_ADMIN = 'Vous n\'avez pas les droits pour accéder à cette page';

    #[Route('/', name: 'app_campus_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CampusRepository $campusRepository): Response
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
        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campusRepository->save($campus, true);
            return $this->redirectToRoute('app_campus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('campus/index.html.twig', [
            'campuses' => $campusRepository->findBy([], array('nom' => 'asc')),
            'campus' => $campus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_campus_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Campus $campus, CampusRepository $campusRepository): Response
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
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campusRepository->save($campus, true);

            return $this->redirectToRoute('app_campus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('campus/edit.html.twig', [
            'campus' => $campus,
            'form' => $form,
        ]);
    }
}
