<?php

namespace Jiwon\CCCPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Jiwon\CCCPBundle\Entity\Template;
use Jiwon\CCCPBundle\Entity\Variable;

class AdminController extends Controller
{
    public function modTemplateAction($id, request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $path = "/data/scripts/acer_v2/templates/";
        $template = $em->getRepository('JiwonCCCPBundle:Template')->findOneBy(array('id' => $id));
        $file = @file_get_contents($path.$template->getNom());
        $associations = $em->getRepository('JiwonAdminBundle:NewAssociation')->findAll();
        $modeles = $em->getRepository('JiwonAdminBundle:Model')->findAll();
        $constructeurs = $em->getRepository('JiwonAdminBundle:Constructeur')->findAll();
        $categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findAll();
        $variables = $em->getRepository('JiwonCCCPBundle:Variable')->findBy(array('id_template' => $id));
        $types = $em->getRepository('JiwonCCCPBundle:Type')->findAll();
        $current_user = $this->get('security.token_storage')->getToken()->getUser();

        if($request->isMethod("POST"))
        {
            if($request->request->get("btnSubmit") == "save")
            {
                $content = $request->request->get("textarea_template");
                $balises = $request->request->get("balise");
                $valeurs = $request->request->get("valeur");
                $categorie = $request->request->get("template_categorie");
                $reseau = $request->request->get("template_association");
                $constructeur = $request->request->get("template_constructeur");
                $modele = $request->request->get("template_modele");
                $type = $request->request->get("template_type");
                $model = $em->getRepository('JiwonAdminBundle:Model')->findOneById($modele);
                $asso = $em->getRepository('JiwonAdminBundle:NewAssociation')->findOneById($reseau);
                $temp = $em->getRepository('JiwonCCCPBundle:Template')->findOneByNom($request->request->get("template_name"));
                $cat = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneById($request->request->get("template_categorie"));
                if($temp != null)
                {
                    if($temp->getId() != $id) {
                        $balises_valeurs = array();
                        for($i = 0; $i < count($balises); $i++) $balises_valeurs[$i] = array($balises[$i], $valeurs[$i]);

                        return $this->render('JiwonCCCPBundle:Admin:template.html.twig', array(
                            'error' => "Erreur : Ce nom de template existe déjà.",
                            'cat' => $categorie,
                            'res' => $reseau,
                            'con' => $constructeur,
                            'mod' => $modele,
                            'typ' => $type,
                            'associations' => $associations,
                            'template_content' => $request->request->get("textarea_template"),
                            'balises_valeurs' => $balises_valeurs,
                            'nom' => $request->request->get("template_name"),
                            'content' => $content,
                            'template' => $template,
                            'file' => $file,
                            'associations' => $associations,
                            'modeles' => $modeles,
                            'constructeurs' => $constructeurs,
                            'categories' => $categories
                        ));
                    }
                }

                $log = "Modification du template id:".$template->getId().", nom:".$template->getNom().", association:".$template->getIdAssociation().", model:".$template->getIdModel().", constructeur:".$template->getIdConstructeur().", categorie:".$template->getIdCategorie().", type:".$template->getIdType();
                $old_name = $template->getNom();
                if($model == null) $template->setNom($cat->getCategorie()."_".$em->getRepository('JiwonAdminBundle:Constructeur')->findOneById($constructeur)->getConstructeur()."_".$asso->getNomExploitation());
                else $template->setNom($cat->getCategorie()."_".$model->getNomExploitation()."_".$asso->getNomExploitation());
                //$template->setNom($request->request->get("template_name"));
                $template->setIdAssociation($em->getRepository('JiwonAdminBundle:NewAssociation')->findOneById($reseau));
                $template->setIdCategorie($em->getRepository('JiwonCCCPBundle:Categorie')->findOneById($categorie));
                $template->setIdModel($em->getRepository('JiwonAdminBundle:Model')->findOneById($modele));
                $template->setIdConstructeur($em->getRepository('JiwonAdminBundle:Constructeur')->findOneById($constructeur));
                $template->setIdType($em->getRepository('JiwonCCCPBundle:Type')->findOneById($type));
                $template->setIdUser($current_user);
                $template->setDate(New \Datetime('now'));
                $em->persist($template);
                $em->flush();
                $ids = array();

                for($i = 0; $i < count($balises); $i++)
                {
                    $variable = $em->getRepository('JiwonCCCPBundle:Variable')->findOneBy(array('id_template' => $template->getId(), 'balise' => $balises[$i], 'valeur' => $valeurs[$i]));
                    if($variable != null) {
                        $ids[] = $variable->getId();
                        continue;
                    }
                    $variable = new Variable();
                    $variable->setIdTemplate($template);
                    $variable->setBalise($balises[$i]);
                    $variable->setValeur($valeurs[$i]);
                    $em->persist($variable);
                    $em->flush();
                    $ids[] = $variable->getId();
                }

                $variables = $em->getRepository('JiwonCCCPBundle:Variable')->findBy(array('id_template' => $template->getId()));
                if(!empty($variables)) {
                    foreach ($variables as $variable) {
                        if(!in_array($variable->getId(), $ids)) {
                            $em->remove($variable);
                            $em->flush();
                        }
                    }
                }

                if($request->request->get("template_name") != $old_name)
                {
                    rename($path.$old_name, $path.$template->getNom());
                    $em->persist($template);
                    $em->flush();
                }

                $this->get('app.insert_log')->InsertLog($log." en nom:".$template->getNom().", association:".$template->getIdAssociation().", model:".$template->getIdModel().", constructeur:".$template->getIdConstructeur().", categorie:".$template->getIdCategorie().", type:".$template->getIdType(), 0);

                file_put_contents($path.$template->getNom(), $request->request->get("textarea_template"));

                return $this->redirectToRoute('jiwon_cccp_template');
            }
        }

        return $this->render('JiwonCCCPBundle:Admin:template.html.twig', array(
            'template' => $template,
            'file' => $file,
            'associations' => $associations,
            'modeles' => $modeles,
            'constructeurs' => $constructeurs,
            'categories' => $categories,
            'variables' => $variables,
            'types' => $types
        ));
    }

    public function delTemplateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('JiwonCCCPBundle:Template')->findOneById($id);
        $nom = $template->getNom();
        $categorie = $template->getIdCategorie()->getCategorie();
        if($template == null)
        {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                'error' => "Le template n'existe pas."
            ));
        }

        $variables = $em->getRepository('JiwonCCCPBundle:Variable')->findBy(array('id_template' => $id));
        $resultats = $em->getRepository('JiwonCCCPBundle:Resultat')->findBy(array('id_template' => $id));
        foreach($variables as $variable) $em->remove($variable);
        foreach($resultats as $resultat) $em->remove($resultat);

        $em->remove($template);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                'error' => "Ce template ne peut pas être supprimé car d'autres éléments de la BDD dépendent de lui."
            ));
        }

        @unlink('/data/scripts/acer_v2/templates/'.$nom);
        $files = glob('/data/scripts/acer_v2/resultats/'.$categorie.'/*-'.$nom.'-*.csv');
        foreach($files as $file) {
            @unlink($file);
        }

        $files = glob('/data/scripts/acer_v2/archives/'.$categorie.'/*-'.$nom.'-*.csv');
        foreach($files as $file) {
            @unlink($file);
        }

        $this->get('app.insert_log')->InsertLog("Suppression du template nom:".$template->getNom().", association:".$template->getIdAssociation().", model:".$template->getIdModel().", constructeur:".$template->getIdConstructeur().", categorie:".$template->getIdCategorie().", type:".$template->getIdType(), 0);

        return $this->redirectToRoute('jiwon_cccp_template');
    }
}
