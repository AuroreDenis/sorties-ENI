<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Filtre;
use App\Entity\Lieu;
use App\Entity\Participants;
use App\Entity\Sortie;

use App\Entity\Ville;
use App\Form\AnnulerSortieType;
use App\Form\FiltreType;
use App\Form\SortiesType;
use App\Repository\EtatsRepository;
use App\Repository\ParticipantsRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
* @Route ("/profile")
*/
class SortiesController extends AbstractController
{

/********************************************* liste des sorties **********************************************/
    /**
     * @Route("/sorties", name="sorties_list")
     */
    public function list(Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user=$this->getUser()->getActif();// si actif=0 deconnexion
        if (!$user) {
            $this->addFlash('error', "Compte désactivé (veuillez contacter l'administrateur)");
            return $this->redirectToRoute('logout');
        }
        $user=$this->getUser();// si actif=0 deconnexion

        /****************************** récupère les sorties à afficher d'après le filtre **********/


        $today = new \DateTime('now');
        $lastDate = new \DateTime('now');
        $lastDate->add(new DateInterval('P30D'));


        // formulaire - filtres
        $filtre = new Filtre();
      $filtre->setDateDebut($today);
      $filtre->setDateFin($lastDate);
        $filtreForm = $this->createForm(FiltreType::class, $filtre);

        //hydrate le formulaire
        $filtreForm->handleRequest($request);

        // récupère repository
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->filtrer($user, $filtre);

        // date du jour
        $today=new \DateTime('now');

        /*  //récupère toutes les sorties
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();



        //récupère tous les campus
        $campusRepo = $this->getDoctrine()->getRepository(Campus::class);
        $campus = $campusRepo->findAll();

        // formulaire - filtres
        $filtre = new Filtre();
        $filtreForm = $this->createForm(FiltreType::class, $filtre);

        //hydrate le formulaire
        $filtreForm->handleRequest($request);

        // initialiser les values des dates
        $lastDate=new \DateTime('now');
        $lastDate ->add(new DateInterval('P180D'));
        $firstDate=new \DateTime('now');
        $firstDate ->sub(new DateInterval('P31D'));
        $Today=new \DateTime('now');
        //******************************

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) { // si le formulaire est envoyé


            // on récupère les valeurs du filtre
            $campusF=$filtre->getCampus()->getNomCampus();  //campus^

            // filtre mot
            $mot = $filtre->getSearch(); //mot à chercher
            if(empty($mot)){// si null et bien on cherche rien ^
                $mot = '';
            }

            //filtre date
            $firstDate = $filtre->getDateDebut(); // date min
            $lastDate = $filtre->getDateFin(); // date max

            //filtre check box
            $inscrit = $filtre->getInscrit(); // booléen -> affiche les sorties qd on inscrit / pas inscrit
            $organisatrice = $filtre->getOrga();//booléen -> afffiche les sorties dont on est l'orga
            $end = $filtre->getClose(); // booléen -> affiche sorties fermées
            $pasInscrit = $filtre->getPasInscrit(); //booléen -> vrai si on affiche les sorties où on est inscrit
            if ( empty($inscrit) and empty($organisatrice) and empty($pasInscrit)) {
             $Ok = false;
            }
            else{
                $Ok = true;
            }


            return $this->render('sortie/list.html.twig', [
                "today" => $Today,
                "sorties" => $sorties,
                "firstDate" => $firstDate,
                "lastDate" => $lastDate,
                "filtreForm" => $filtreForm->createView(),
                "user" => $user,
                "inscrit" => $inscrit,
                "orga" => $organisatrice,
                "end" => $end,
                "search"=> $mot,
                "Ok" => $Ok,
                "pasInscrit"=> $pasInscrit,
                "campus"=>$campusF
            ]);
        }
        return $this->render('sortie/list.html.twig', [
            "today" => $Today,
            "sorties" => $sorties,
            "lastDate" => $lastDate,
            "firstDate" => $firstDate,
            "filtreForm" => $filtreForm->createView(),
            "user" => $user,
            "inscrit" => true,
            "orga" => true,
            "end" => false,
            "search" => '',
            "Ok" => false,
            "pasInscrit" => false,
            "campus"=> 'Rennes'
        ]);
    */



        return $this->render('sortie/list.html.twig', [
            "sorties" => $sorties,
            "filtreForm" => $filtreForm->createView(),
            "user" => $user,
            "today" => $today
            ]);

    }

/********************************************** Création d'une sortie *****************************************/
    /**
     * @Route("/sorties/add", name="sortie_add")
     */
    public function add(EntityManagerInterface $em, EtatsRepository $etatsRepository,  UserInterface $user, Request $request, ParticipantsRepository $participantsRepository)
    {
        $sortie = new Sortie();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**************************** Récupération de la ville *************************************/

        $villeRepo = $this->getDoctrine()->getRepository(Ville::class);
        $villes = $villeRepo->findAll();

        /******************* récupération de l'orga ********************/
        //l'organisateur est l'utilisateur connecté
//        $user = $this->getUser();
        $orga = $participantsRepository->findOneBy(['username' => $user->getUsername()]);
        $sortie->setOrganisateur($orga);


        $sortieForm = $this->createForm(SortiesType::class, $sortie);

        //hydrate le formulaire
        $sortieForm->handleRequest($request);

        $flashMessage = 'problème ';

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            //sauvegarde en BDD ssi formulaire est renseigné

            /************************* récupération de état *************/
           // $etatRepo = $this->getDoctrine()->getRepository(Etat::class);

            if($sortieForm->getClickedButton() === $sortieForm->get('creer')) {//gestion selon le bouton utilisé
                 $etat = $etatsRepository->findOneBy(['libelle' => 'En création']);
                 $flashMessage = 'nouvelle sortie créée';
             }
             elseif ($sortieForm->getClickedButton() === $sortieForm->get('publier')) {//gestion selon le bouton utilisé
                 $etat = $etatsRepository->findOneBy(['libelle' => 'Ouvert']);
                 $flashMessage = 'nouvelle sortie publiée';
             }
             else{
                 $etat = $etatsRepository->findOneBy(['libelle' => 'Fermé']);
             }
            $sortie->setEtat($etat);
            /*  $lieu = $sortie->getLieu();
              $sortie->setLieu($lieu);
              $em->persist($lieu);*/

            $em->persist($sortie);
            $em->flush();

            //renvoie dans la page de detail en affichant un message flash
            $this->addFlash('success', $flashMessage);


            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }

        return $this->render('sortie/add.html.twig',[
            "SortiesType"=>  $sortieForm->createView()
        ]);
    }
/********************************** Detail d'une sortie ***************************************/

    /**
     * @Route("/sorties/{id}", name="sortie_detail", requirements={"id": "\d+"})
     */
    public function detail($id): Response
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $sortiesRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortiesRepo->find($id);


        return $this->render('sortie/detail.html.twig', [
            "sortie" => $sortie
        ]);
    }

/***************** MODIF ************************************************/

    /**
     * @Route("/sorties/modif/{id}", name="sortie_modif", requirements={"id": "\d+"} )
     */
    public function modif($id, EntityManagerInterface $em, Request $request)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // récupérer la sortie à modifier
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        // formulaire, update
        $sortieForm = $this->createForm(SortiesType::class, $sortie);

        //hydrate le formulaire
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            //sauvegarde en BDD ssi formulaire est renseigné
            $em->persist($sortie);
            $em->flush();

//renvoie dans la page de detail en affichant un message flash
            $this->addFlash('success', 'la sortie a été modifiée');
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId()
            ]);
        }

        return $this->render('sortie/modif.html.twig',[
            "SortiesType"=>  $sortieForm->createView()
        ]);
    }
/***********************************s s'inscrire à une sortie**********************************************/

    /**
     * @Route("/sorties/s'inscrire/{id}", name="sinscrire_sortie", requirements={"id": "\d+"} )
     */
    public function sinscrire($id, EntityManagerInterface $em, Request $request)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user=$this->getUser();
        // récupérer la sortie à modifier
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //ajoute le participant à la sortie et la sortie au participant
        $sortie->addParticipants($user);
        $user->addSortie($sortie);


        // enregistrement en bdd
        $em->persist($sortie);
        $em->flush();



        return $this->redirectToRoute('sorties_list', [

        ]);

    }

    /*************************************SE DESISTER***********************************************************/

    /**
     * @Route("/sorties/seDesister/{id}", name="seDesister_sortie", requirements={"id": "\d+"} )
     *
     */
    public function seDesister($id, EntityManagerInterface $em, Request $request)
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user=$this->getUser();
        // récupérer la sortie à modifier
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //retirer le participant à la sortie et la sortie au participant

        $sortie->removeParticipants($user);
        $user->removeSortie($sortie);

        //enregister en bdd
        $em->persist($sortie);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('sorties_list', [

        ]);




    }

    /*****************************detail participants********************************************************/

    /**
     * @Route("/sorties/detailsParticipants/{id}", name="detail_participant", requirements={"id": "\d+"} )
     *
     */

    public function detailsparticipants($id, EntityManagerInterface $em, Request $request)
{   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    // récupérer lES PARTICIPANTS à modifier
    $participantRepo = $this->getDoctrine()->getRepository(Participants::class);
    $participant = $participantRepo->find($id);

    return $this->render('sortie/detailParticipant.html.twig', [
        "participant" => $participant
    ]);


}
/*****************************************Annuler une sortie*************************************************/
    /**
     * @Route("/sorties/annulerUneSortie/{id}", name="annuler-sortie", requirements={"id": "\d+"} )
     *
     */
    public function annulerSortie($id, EntityManagerInterface $em, Request $request): Response
    {   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //verifier si organisateur!! verifier si $id existe!!! verifier etat sortie!!!

        // récupérer la sortie à modifier
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);
        $etatsRepository = $this->getDoctrine()->getRepository(Etat::class);
        $etat = $etatsRepository->findOneBy(['libelle' => 'Annulée']);
        $sortie->setEtat($etat);
        $sortie->setDescriptionInfos("");
        //enregister en bdd
        //$em->persist($sortie);
        //$em->persist($user);
        //$em->flush();
        // on récupère l'user
        $user=$this->getUser();
        //creation du formulaire
        $registerForm = $this->createForm(AnnulerSortieType::class, $sortie);
        $registerForm->handleRequest($request);
        //si form ok
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {
            //
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('sorties_list', [

            ]);

        }
        return $this->render('sortie/annulerSortie.html.twig', [
            "sortie" => $sortie,"user" =>$user,"registerForm"=>$registerForm->createView()
        ]);


    }
}
