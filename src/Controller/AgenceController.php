<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AgenceController extends AbstractController
{
    #[Route('/', name: 'app_agence')]
    public function index(VehiculeRepository $repo): Response
    {
$vehicule= $repo->findAll();



        return $this->render('agence/index.html.twig', [
            'vehicule' => $vehicule
        ]);
    }
    #[Route('/commande/{id}', name: 'commande_vehicule')]
    public function commande($id, VehiculeRepository $repo, EntityManagerInterface $manager, Request $globals): Response
    {


        $vehicule = $repo->find($id);
        $commande = new Commande;


        $form= $this->createForm(CommandeType::class, $commande );
        $form->handleRequest($globals);
        // dump($vehicule);

        if($form->isSubmitted() && $form->isValid())
        {
            
            $depart = $commande->getDateHeureDepart();
            $fin = $commande->getDateHeureFin();
            $interval = $depart->diff($fin);
            $days = $interval->days;
            
            $prix = $vehicule->getPrixJournalier();
            $prix = $prix * $days;
            
            $commande->setDateEnregistrement(new \DateTime);
            $commande->setIdMembre($this->getUser());
            $commande->setIdVehicule($vehicule);
            $commande->setPrixTotal($prix);

            $manager->persist($commande);
            $manager->flush();
            $this->addFlash('success', "La commande a bien été enregistré !");

            return $this->redirectToRoute('app_agence');
        }

        return $this->renderForm("agence/commande.html.twig", [
            "formCommande" => $form,
            'commande' => $commande,
            'vehicule' => $vehicule
        ]);
        
    }


}
