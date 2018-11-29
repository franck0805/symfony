<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{

    public function indexAction($page)
    {
        // la page doit être supérieure ou égale à 1
        if ($page < 1) {
            throw new NotFoundHttpException('Page "' . $page . '" inexistante.');
        }

        // Ici on récuperera la liste des annonces, puis on la passera au template
        $listAdverts = array(
            array(
                'title'   => 'Recherche développpeur Symfony',
                'id'      => 1,
                'author'  => 'Franck',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Mission de webmaster',
                'id'      => 2,
                'author'  => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Offre de stage webdesigner',
                'id'      => 3,
                'author'  => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date'    => new \Datetime())
        );

        // Mais pour l'instant on appelle simplemement le template
        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

    public function viewAction($id)
    {

        // On récupère l'entity manager
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce d'id $id
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);

        if(null === $advert){
            throw new NotFoundHttpException("L'annonce d'id " .$id. " n'existe pas!");
        }

        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository("OCPlatformBundle:Application")
            ->findBy(array('advert' => $advert));

        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert,
            'listApplications' => $listApplications
        ));
    }


    public function addAction(Request $request)
    {

        // Création de l'entité Advert
        $advert = new Advert();
        $advert->setTitle('Recherche développeur Symfony');
        $advert->setAuthor('Carter');
        $advert->setContent("Nous recherchons un développeur symfony débutant sur Lyon.");

        // Création d'une première candidature
        $application1 = new Application();
        $application1->setAuthor("Marine");
        $application1->setContent("J'ai toutes les qualités requises");

        // Création d'une dexuième candidature
        $application2 = new Application();
        $application2->setAuthor("Pierre");
        $application2->setContent("Je suis très motivé");

        // On lie les candidatures à l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);

        //On récupère l'entity manager
        $em = $this->getDoctrine()->getManager();

        // Etape 1 : on persiste l'entité
        $em->persist($advert);

        // Etape 2 : on persite egalement les applications
        $em->persist($application1);
        $em->persist($application2);

        // Etape 2 :  on "flush" tout ce qui a été persisté avant
        //$em->clear('OC\PlatformBundle\Entity\Advert');
        $em->flush();

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if($request->isMethod('POST')){
            //Ici on s'occupera de la création et de la gestion du formulaire

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualitsation de cette annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
            'advert' => $advert
        ));

    }

    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }

        $advert = array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => $id,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()
        );

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));
    }

    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id

        // Ici, on gérera la suppression de l'annonce en question

        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 6, 'title' => 'Recherche développeur Symfony'),
            array('id' => 7, 'title' => 'Mission de webmaster'),
            array('id' => 8, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }

    public function editImageAction($advertId)
    {
        $em = $this->getDoctrine()->getManager();

        // On recupère l'annonce
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($advertId);

        // On modifie l'url de l'image
        $advert->getImage()->setUrl("https://demo.phpgang.com/crop-images/demo_files/pool.jpg");

        $em->flush();

        return new Response("ok");
        
    }


}

