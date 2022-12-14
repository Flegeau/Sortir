<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\CampusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\throwException;
use App\Repository\ParticipantRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Campus;
/**
 *@IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    private string $dataDirectory;
   // private ObjectManager $manager;

    public function __construct(string $dataDirectory)
    {
       $this->dataDirectory = $dataDirectory;
      // $this->manager = $manager;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $isAdmin = $this->isGranted("ROLE_ADMIN");
        if(!$isAdmin){

            throw new AccessDeniedException("Réservé aux administrateurs !");
        }
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/upload', name: 'app_admin_upload')]
    public function getDataFromFile(Request $request,ParticipantRepository $participantRepository, UserPasswordHasherInterface $participantPasswordHasher,CampusRepository $campusRepository):Response{

        $participant = new Participant();
        $campus = new Campus();

        foreach ($request->files as $file){
            //$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $fileName = uniqid().'.'.$file->guessExtension();

            try {
                $file->move($this->dataDirectory, $fileName);
                $row = 0;
                if (($handle = fopen($this->dataDirectory."/".$fileName, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                        $row++;
                        if($row == 1) continue; // ignore first line
                        $parts = explode(";",$data[0]);
                        if($participantRepository->findOneBy(["email"=>$parts[3]])){
                            $this->addFlash("Warning","Utilisateur {$parts[3]} deja existant");
                            continue;
                        }

                        $participant->setNom($parts[0]);
                        $participant->setPrenom($parts[1]);
                        $participant->setTelephone($parts[2]);
                        $participant->setEmail($parts[3]);

                        $parts[4] = $participantPasswordHasher->hashPassword(
                            $participant,
                            $parts[4]
                        );

                        $participant->setPassword($parts[4]);
                        $participant->setPseudo($parts[5]);
                        $participant->setActif(filter_var($parts[6], FILTER_VALIDATE_BOOLEAN));
                        $participant->setRoles(explode(";",$parts[7]));

                        $campus->setNom($parts[8]);
                        $campusRepository->save($campus,true);
                        $participant->setCampus($campus);

                        $participantRepository->save($participant, true);
                        $row++;
                    }
                    fclose($handle);
                }
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                dump($e->getMessage());
            }
        }
       // dd("fin");
        return $this->redirectToRoute("app_login");
    }

    /*private function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }*/
}
