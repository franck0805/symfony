<?php

namespace Jiwon\CCCPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AcrlbbDefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        #$all_categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findBy(array(), array("categorie" => "ASC"));
        #$categories_template = array();

        if ($request->isMethod('POST') && $request->request->get('submit') == "filtre") {
            $ne = $em->getRepository('JiwonAdminBundle:Ne')->findOneBy(array("ne" => $request->request->get('fake_ne')));
            $association = $ne->getIdNewAssociation();
            $header = ["Nom de l'equipement","Ligne de template","Resultat","Match\n"];
            @unlink(__DIR__."/../../../../web/".$ne.".csv");
            file_put_contents(__DIR__."/../../../../web/".$ne.".csv", (join(";", $header)), FILE_APPEND | LOCK_EX);
            foreach($all_categories as $categorie) {
                $files = glob("/data/scripts/acer_v2/resultats/".$categorie->getCategorie()."/".$association->getNomExploitation()."_".$ne->getIdModel()->getNomExploitation()."*.csv");
                foreach($files as $filepath) {
                    $fp = fopen($filepath,"r");
                    while($rec = fgets($fp)){
                        if (strpos($rec, strtolower($request->request->get('fake_ne'))) !== false) {
                            file_put_contents(__DIR__."/../../../../web/".$ne.".csv", $rec, FILE_APPEND | LOCK_EX);
                        }
                    }
                    fclose($fp);
                }
            }

            $response = new Response;
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $response->headers->set('Content-Disposition','attachment; filename="'.$ne.'.csv"');
            $response->setContent(file_get_contents(__DIR__."/../../../../web/".$ne.".csv"));

            return $response;
        }

        # TOUS
        $results =
            "<tr class=''>
                <td style='text-align:left;background-color:#660000;color:white;font-size: 1.3em;'>
                    <span onclick='showTable(this)' data-class='Tous'>
                        <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                    </span> Tous
                </td>
            </tr>";

        # Recupere toutes les entités : DSP_ALLIANCE_CONNECTIC, DSP_ALSACE_CONNEXIA, DSP_ARIEGE_TELECOM, DSP_AXIONE, DSP_CAP_CONNEXION...
        $entites = $em->getRepository('JiwonAdminBundle:Entite')->findBy(array(), array("entite" => "ASC"));
        foreach ($entites as $entite) {
            $results = $results."
            <tr class='Tous' style='display:none'>
                <td class='Tous' style='text-align:left;background-color:#7F0000;color:white;font-size: 1.3em;'>
                    <span style='margin-left:20px;'>
                        <span onclick='showTable(this)' data-class='".$entite->getEntite()."'>
                            <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                        </span> ".$entite->getEntite()."
                    </span>
                </td>
            </tr>";

            # Recupere toutes les nouvelles categories : ACCES, COEUR, COLAG, DCN, DEV, MAN, SERVICE
            $categories = $em->getRepository('JiwonAdminBundle:NewCategorie')->findBy(array(), array('categorie' => 'ASC'));
            #ACCES, COEUR, COLAG, DCN, DEV, MAN, SERVICE
            foreach ($categories as $categorie) {
                $results = $results."
                    <tr class='Tous ".$entite->getEntite()."' style='display:none'>
                        <td class='Tous ".$entite->getEntite()."' style='text-align:left;background-color:#990000;color:white;font-size: 1.3em;'>
                            <span style='margin-left:40px'>
                                <span onclick='showTable(this)' data-class='".$entite->getEntite()."_".$categorie->getNomExploitation()."'>
                                    <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                                </span> ".$categorie->getNomExploitation()."
                            </span>
                        </td>
                    </tr>";

                # Recupere toutes les fonctions : GEN, B2B, B2C, DEV
                $fonctions = $em->getRepository('JiwonAdminBundle:Fonction')->findBy(array(), array("fonction" => "ASC"));
                # GEN, B2B, B2C, DEV
                foreach ($fonctions as $fonction) {
                    $results = $results."
                        <tr class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."' style='display:none'>
                            <td class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."' style='text-align:left;background-color:#B20000;color:white;font-size: 1.3em;overflow: hidden;'>
                                <span style='margin-left:60px'>
                                    <span onclick='showTable(this)' data-class='".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."'>
                                        <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                                    </span> ".$fonction->getFonction()."
                                </span>
                            </td>
                        </tr>";

                    # Recupere toutes les associations :
                    $associations = $em->getRepository('JiwonAdminBundle:NewAssociation')->findByEntiteCategorieAndFonction($entite, $categorie, $fonction);
                    #BAS, SCE Mobile
                    foreach($associations as $association) {
                        $results = $results."
                            <tr class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."' style='display:none'>
                                <td class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."' style='text-align:left;background-color:#CC0000;color:white;font-size: 1.3em;'>
                                    <span style='margin-left:80px'>
                                        <span onclick='showTable(this)' data-class='".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."_".$association->getIdReseau()->getNomExploitation()."'>
                                            <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                                        </span> ".$association->getIdReseau()->getReseau()."
                                    </span>
                                </td>
                            </tr>";

                        # Récupère tous les models de l'association
                        $models = $em->getRepository('JiwonAdminBundle:Model')->findByNewAssociation($association);
                        #if($models != null && $models[0]->getIdConstructeur()->getId() != 3){ var_dump($models); die; }
                        foreach ($models as $model) {
                            $results = $results."
                                <tr class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."_".$association->getIdReseau()->getNomExploitation()."' style='display:none'>
                                    <td class='Tous ".$entite->getEntite()." ".$entite->getEntite()."_".$categorie->getNomExploitation()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()." ".$entite->getEntite()."_".$categorie->getNomExploitation()."_".$fonction->getFonction()."_".$association->getIdReseau()->getNomExploitation()."' style='text-align:left;background-color:#E50000;color:white;font-size: 1.3em;'>
                                        <span style='margin-left:120px'>".$model."</span>
                                    </td>
                                </tr>";
                        }
                    }
                }
            }
        }

        return $this->render('JiwonCCCPBundle:AcrlbbDefault:index.html.twig', array(
            'results' => $results,
        ));
    }

    public function auditAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findBy(array(), array("categorie" => "ASC"));
        $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array(), array("nom" => "ASC"));
        if($request->isMethod("POST"))
        {
            if($request->request->get("submit") == "tous")
            {
                shell_exec("perl /data/scripts/acer_v2/audit.pl 2>/dev/null >/dev/null &");
                $this->get('app.insert_log')->InsertLog("Audit lancé pour tous les réseaux", 0);
                return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                    'categories' => $categories,
                    'templates' => $templates,
                    'success' => "Les résultats de l'audit du réseau seront disponibles sous peu."
                ));
            }
            else {
                if($request->request->get("submit") == "categorie") {
                    shell_exec("perl /data/scripts/acer_v2/audit.pl ".$request->request->get("categorie")." 2>/dev/null >/dev/null &");
                    $this->get('app.insert_log')->InsertLog("Audit lancé pour ".$request->request->get("categorie"), 0);
                    return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                        'categories' => $categories,
                        'templates' => $templates,
                        'success' => "Les résultats de l'audit ".$request->request->get("categorie")." seront disponibles sous peu."
                    ));
                }

                else {
                    shell_exec("perl /data/scripts/acer_v2/audit.pl ".$request->request->get("template")." 2>/dev/null >/dev/null &");
                    $this->get('app.insert_log')->InsertLog("Audit lancé pour ".$request->request->get("template"), 0);
                    return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                        'categories' => $categories,
                        'templates' => $templates,
                        'success' => "Les résultats de l'audit ".$request->request->get("template")." seront disponibles sous peu."
                    ));
                }
            }
        }

        return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
            'categories' => $categories,
            'templates' => $templates
        ));
    }
}