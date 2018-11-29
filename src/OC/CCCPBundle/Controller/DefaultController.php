<?php

namespace Jiwon\CCCPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Jiwon\CCCPBundle\Entity\Template;
use Jiwon\CCCPBundle\Entity\Variable;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $all_categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findBy(array(), array("categorie" => "ASC"));
        $categories_template = array();

        $categorie_string = "";

        if ($request->isMethod('POST') && $request->request->get('submit') == "categories") {
            $checkbox = $request->request->get('checkbox');
            if (!empty($checkbox)) {
                foreach ($checkbox as $cat) {
                    $categories_template[] = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneBy(array("categorie" => $cat));
                }
                $categorie_string = join(",", $checkbox);
            }
        } elseif ($request->isMethod('POST') && $request->request->get('submit') == "filtre") {
            $ne = $em->getRepository('JiwonAdminBundle:Ne')->findOneBy(array("ne" => $request->request->get('fake_ne')));
            $association = $ne->getIdNewAssociation();
            $header = ["Nom de l'equipement", "Ligne de template", "Resultat", "Match\n"];
            @unlink(__DIR__ . "/../../../../web/" . $ne . ".csv");
            file_put_contents(__DIR__ . "/../../../../web/" . $ne . ".csv", (join(";", $header)), FILE_APPEND | LOCK_EX);
            foreach ($all_categories as $categorie) {
                $files = glob("/data/scripts/acer_v2/resultats/" . $categorie->getCategorie() . "/" . $association->getNomExploitation() . "_" . $ne->getIdModel()->getNomExploitation() . "*.csv");
                foreach ($files as $filepath) {
                    $fp = fopen($filepath, "r");
                    while ($rec = fgets($fp)) {
                        if (strpos($rec, strtolower($request->request->get('fake_ne'))) !== false) {
                            file_put_contents(__DIR__ . "/../../../../web/" . $ne . ".csv", $rec, FILE_APPEND | LOCK_EX);
                        }
                    }
                    fclose($fp);
                }
            }

            $response = new Response;
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $ne . '.csv"');
            $response->setContent(file_get_contents(__DIR__ . "/../../../../web/" . $ne . ".csv"));

            return $response;
        }

        # TOUS
        $resultHTML = "";
        foreach ($categories_template as $cat) {
            $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array("id_categorie" => $cat), array("nom" => "ASC"));
            if ($templates != null) {
                $success = 0;
                $failed = 0;
                foreach ($templates as $template) {
                    $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->countResult($template);
                    $success = $success + $resultat['success'];
                    $failed = $failed + $resultat['failed'];
                }
                if ($success == 0 && $failed == 0) {
                    $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span></td>";
                } else {
                    $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-Tous' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-Tous'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                }
            } else {
                $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
            }
        }
        $results = "<tr class=''>
            <td style='text-align:left;background-color:#660000;color:white;font-size: 1.3em;'><span onclick='showTable(this)' data-class='Tous'><i class='fa fa-plus-square-o' aria-hidden='true'></i></span> Tous<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-Tous'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-Tous'><i class='fa fa-download' aria-hidden='true'></i></a></td>" . $resultHTML . "</tr>";
        #SFR, SRR
        $entites = $em->getRepository('JiwonAdminBundle:Entite')->findBy(array(), array("entite" => "ASC"));
        foreach ($entites as $entite) {
            $resultHTML = "";
            foreach ($categories_template as $cat) {
                $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array("id_categorie" => $cat), array("nom" => "ASC"));
                if ($templates != null) {
                    $success = 0;
                    $failed = 0;
                    foreach ($templates as $template) {
                        $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->countResult($template, $entite);
                        $success = $success + $resultat['success'];
                        $failed = $failed + $resultat['failed'];
                    }
                    if ($success == 0 && $failed == 0) {
                        $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span></td>";
                    } else {
                        $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-" . $entite->getEntite() . "' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-" . $entite->getEntite() . "'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                    }
                } else {
                    $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                }
            }
            $results = $results . "<tr class='Tous' style='display:none'>
                    <td class='Tous' style='text-align:left;background-color:#7F0000;color:white;font-size: 1.3em;'><span style='margin-left:20px;'><span onclick='showTable(this)' data-class='" . $entite->getEntite() . "'><i class='fa fa-plus-square-o' aria-hidden='true'></i></span> " . $entite->getEntite() . "<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-" . $entite->getEntite() . "'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-" . $entite->getEntite() . "'><i class='fa fa-download' aria-hidden='true'></i></a></span></td>" . $resultHTML . "</tr>";
            $categories = $em->getRepository('JiwonAdminBundle:NewCategorie')->findBy(array(), array('categorie' => 'ASC'));
            #SERVICE, MAN, COEUR...
            foreach ($categories as $categorie) {
                $resultHTML = "";
                foreach ($categories_template as $cat) {
                    $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array("id_categorie" => $cat), array("nom" => "ASC"));
                    if ($templates != null) {
                        $success = 0;
                        $failed = 0;
                        foreach ($templates as $template) {
                            $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->countResult($template, $entite, $categorie);
                            $success = $success + $resultat['success'];
                            $failed = $failed + $resultat['failed'];
                        }
                        if ($success == 0 && $failed == 0) {
                            $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span></td>";
                        } else {
                            $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                        }
                    } else {
                        $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                    }
                }
                $results = $results . "
                    <tr class='Tous " . $entite->getEntite() . "' style='display:none'>
                        <td class='Tous " . $entite->getEntite() . "' style='text-align:left;background-color:#990000;color:white;font-size: 1.3em;'><span style='margin-left:40px'><span onclick='showTable(this)' data-class='" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "'><i class='fa fa-plus-square-o' aria-hidden='true'></i></span> " . $categorie->getNomExploitation() . "<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></span></td>" . $resultHTML . "</tr>";
                $fonctions = $em->getRepository('JiwonAdminBundle:Fonction')->findBy(array(), array("fonction" => "ASC"));
                #B2B, B2C, GEN
                foreach ($fonctions as $fonction) {
                    $resultHTML = "";
                    foreach ($categories_template as $cat) {
                        $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array("id_categorie" => $cat), array("nom" => "ASC"));
                        if ($templates != null) {
                            $success = 0;
                            $failed = 0;
                            foreach ($templates as $template) {
                                $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->countResult($template, $entite, $categorie, $fonction);
                                $success = $success + $resultat['success'];
                                $failed = $failed + $resultat['failed'];
                            }
                            if ($success == 0 && $failed == 0) {
                                $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span></td>";
                            } else {
                                $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                            }
                        } else {
                            $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                        }
                    }
                    $results = $results . "
                        <tr class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "' style='display:none'>
                            <td class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "' style='text-align:left;background-color:#B20000;color:white;font-size: 1.3em;overflow: hidden;'><span style='margin-left:60px'><span onclick='showTable(this)' data-class='" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "'><i class='fa fa-plus-square-o' aria-hidden='true'></i></span> " . $fonction->getFonction() . "<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "'><i class='fa fa-download' aria-hidden='true'></i></a></span></td>" . $resultHTML . "</tr>";
                    $associations = $em->getRepository('JiwonAdminBundle:NewAssociation')->findByEntiteCategorieAndFonction($entite, $categorie, $fonction);
                    #BAS, SCE Mobile
                    foreach ($associations as $association) {
                        $resultHTML = "";
                        foreach ($categories_template as $cat) {
                            $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array("id_categorie" => $cat), array("nom" => "ASC"));
                            if ($templates != null) {
                                $success = 0;
                                $failed = 0;
                                foreach ($templates as $template) {
                                    $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->countResult($template, $entite, $categorie, $fonction, $association->getIdReseau());
                                    $success = $success + $resultat['success'];
                                    $failed = $failed + $resultat['failed'];
                                }
                                if ($success == 0 && $failed == 0) {
                                    $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span></td>";
                                } else {
                                    $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $success . "</span> / <span style='color:#e51c23'>" . $failed . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                                }
                            } else {
                                $resultHTML = $resultHTML . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                            }
                        }
                        $results = $results . "
                            <tr class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "' style='display:none'>
                                <td class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "' style='text-align:left;background-color:#CC0000;color:white;font-size: 1.3em;'><span style='margin-left:80px'><span onclick='showTable(this)' data-class='" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "'><i class='fa fa-plus-square-o' aria-hidden='true'></i></span> " . $association->getIdReseau()->getReseau() . "<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></span></td>" . $resultHTML . "</tr>";
                        $models = $em->getRepository('JiwonAdminBundle:Model')->findByNewAssociation($association);
                        foreach ($models as $model) {
                            $resultHTML1 = "";
                            foreach ($categories_template as $cat) {
                                $template = $em->getRepository('JiwonCCCPBundle:Template')->findOneBy(array("id_categorie" => $cat, "id_model" => $model->getId(), "id_association" => $association->getId()), array("nom" => "ASC"));
                                if ($template == null) {
                                    $template = $em->getRepository('JiwonCCCPBundle:Template')->findOneBy(array("id_categorie" => $cat, "id_constructeur" => $model->getIdConstructeur()), array("nom" => "ASC"));
                                }
                                if ($template != null) {
                                    $resultat = $em->getRepository('JiwonCCCPBundle:Resultat')->findOneBy(array("id_template" => $template, "id_association" => $association, "id_model" => $model), array("date" => "DESC"));
                                    if ($resultat != null) {
                                        $resultHTML1 = $resultHTML1 . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>" . $resultat->getSuccess() . "</span> / <span style='color:#e51c23'>" . $resultat->getFailed() . "</span><a class='btn btn-xs btn-success' href='/audit-cccp/show/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "_" . $model->getNomExploitation() . "' style='margin-left:10px;'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a class='btn btn-xs btn-info' href='/audit-cccp/download/" . $cat->getCategorie() . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "_" . $model->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></td>";
                                    } else {
                                        $resultHTML1 = $resultHTML1 . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                                    }
                                } else {
                                    $resultHTML1 = $resultHTML1 . "<td style='background-color:#dedcdc;border:1px solid #ffffff;'><span style='color:#4caf50'>0</span> / <span style='color:#e51c23'>0</span></td>";
                                }
                            }
                            $results = $results . "
                                <tr class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "' style='display:none'>
                                    <td class='Tous " . $entite->getEntite() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . " " . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "' style='text-align:left;background-color:#E50000;color:white;font-size: 1.3em;'><span style='margin-left:120px'>" . $model . "<a style='margin-left:10px;margin-bottom:3px;' class='btn btn-xs btn-success' href='/audit-cccp/show/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "_" . $model->getNomExploitation() . "'><i class='fa fa-file-excel-o' aria-hidden='true'></i></a> <a style='margin-bottom:3px;' class='btn btn-xs btn-info' href='/audit-cccp/download/" . $categorie_string . "-" . $entite->getEntite() . "_" . $categorie->getNomExploitation() . "_" . $fonction->getFonction() . "_" . $association->getIdReseau()->getNomExploitation() . "_" . $model->getNomExploitation() . "'><i class='fa fa-download' aria-hidden='true'></i></a></span></td>" . $resultHTML1 . "</tr>";
                        }
                    }
                }
            }
        }

        return $this->render('JiwonCCCPBundle:Default:index.html.twig', array(
                    'results' => $results,
                    'templates_filtered' => $categories_template,
                    'templates' => $all_categories
        ));
    }

    public function auditAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findBy(array(), array("categorie" => "ASC"));
        $templates = $em->getRepository('JiwonCCCPBundle:Template')->findBy(array(), array("nom" => "ASC"));
        if ($request->isMethod("POST")) {
            if ($this->get('security.context')->getToken()->getUser() === 'anon.') {
                return $this->render('JiwonCCCPBundle:Default:success.html.twig', array(
                            'error' => 'Vous devez vous connecter pour lancer des audits'
                ));
            }
            if ($request->request->get("submit") == "tous") {
                shell_exec("perl /data/scripts/acer_v2/audit.pl 2>/dev/null >/dev/null &");
                $this->get('app.insert_log')->InsertLog("Audit lancé pour tous les réseaux", 0);
                return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                            'categories' => $categories,
                            'templates' => $templates,
                            'success' => "Les résultats de l'audit du réseau seront disponibles sous peu."
                ));
            } else {
                if ($request->request->get("submit") == "categorie") {
                    shell_exec("perl /data/scripts/acer_v2/audit.pl " . $request->request->get("categorie") . " 2>/dev/null >/dev/null &");
                    $this->get('app.insert_log')->InsertLog("Audit lancé pour " . $request->request->get("categorie"), 0);
                    return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                                'categories' => $categories,
                                'templates' => $templates,
                                'success' => "Les résultats de l'audit " . $request->request->get("categorie") . " seront disponibles sous peu."
                    ));
                } else {
                    shell_exec("perl /data/scripts/acer_v2/audit.pl " . $request->request->get("template") . " 2>/dev/null >/dev/null &");
                    $this->get('app.insert_log')->InsertLog("Audit lancé pour " . $request->request->get("template"), 0);
                    return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                                'categories' => $categories,
                                'templates' => $templates,
                                'success' => "Les résultats de l'audit " . $request->request->get("template") . " seront disponibles sous peu."
                    ));
                }
            }
        }

        return $this->render('JiwonCCCPBundle:Default:audit.html.twig', array(
                    'categories' => $categories,
                    'templates' => $templates
        ));
    }

    public function templateAction(request $request) {
        $em = $this->getDoctrine()->getManager();
        $path = "/data/scripts/acer_v2/templates/";
        $search = $request->query->get('search');
        if ($search != null) {
            $dql = "SELECT a, c FROM JiwonCCCPBundle:Template a JOIN a.id_categorie c JOIN a.id_association asso JOIN asso.id_reseau reseau WHERE a.nom LIKE :search OR c.categorie LIKE :search OR reseau.reseau LIKE :search ORDER BY a.nom ASC, c.categorie ASC";
        } else {
            $dql = "SELECT a, c FROM JiwonCCCPBundle:Template a JOIN a.id_categorie c ORDER BY a.nom ASC, c.categorie ASC";
        }
        #$templates = $em->getRepository('JiwonCCCPBundle:Template')->findAll();
        $files = array();
        $query = $em->createQuery($dql);
        if ($search != null) {
            $query->setParameter('search', '%' . $search . '%');
        }
        $paginator = $this->get('knp_paginator');
        $templates = $paginator->paginate(
                $query, /* query NOT result */ $request->query->getInt('page', 1)/* page number */, 20/* limit per page */
        );
        foreach ($templates as $template) {
            $file = @file_get_contents($path . $template->getNom());
            $files[$template->getNom()] = $file;
        }

        return $this->render('JiwonCCCPBundle:Default:template.html.twig', array(
                    'templates' => $templates,
                    'files' => $files
        ));
    }

    public function newTemplateAction(request $request) {
        $em = $this->getDoctrine()->getManager();
        $path = "/data/scripts/acer_v2/templates/";
        $associations = $em->getRepository('JiwonAdminBundle:NewAssociation')->findAll();
        $modeles = $em->getRepository('JiwonAdminBundle:Model')->findAll();
        $constructeurs = $em->getRepository('JiwonAdminBundle:Constructeur')->findAll();
        $categories = $em->getRepository('JiwonCCCPBundle:Categorie')->findAll();
        $types = $em->getRepository('JiwonCCCPBundle:Type')->findAll();
        $current_user = $this->get('security.token_storage')->getToken()->getUser();

        if ($request->isMethod("POST")) {
            if ($request->request->get("btnSubmit") == "save") {
                $template = new Template();
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
                $cat = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneById($categorie);
                $cons = $em->getRepository('JiwonAdminBundle:Constructeur')->findOneById($constructeur);
                if ($model != null)
                    $template->setNom($cat->getCategorie() . "_" . $model->getNomExploitation() . "_" . $asso->getNomExploitation());
                else
                    $template->setNom($cat->getCategorie() . "_" . $cons->getConstructeur() . "_" . $asso->getNomExploitation());
                //$template->setNom($request->request->get("template_name"));
                $template->setIdAssociation($asso);
                $template->setIdCategorie($em->getRepository('JiwonCCCPBundle:Categorie')->findOneById($categorie));
                $template->setIdModel($em->getRepository('JiwonAdminBundle:Model')->findOneById($modele));
                $template->setIdConstructeur($em->getRepository('JiwonAdminBundle:Constructeur')->findOneById($constructeur));
                $template->setIdType($em->getRepository('JiwonCCCPBundle:Type')->findOneById($type));
                $template->setIdUser($current_user);
                $template->setDate(New \Datetime('now'));
                $em->persist($template);

                try {
                    $em->flush();
                } catch (\Exception $e) {
                    return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                                'error' => "Impossible de créer ce template car un autre existe avec des paramètres similaires."
                    ));
                }

                for ($i = 0; $i < count($balises); $i++) {
                    $variable = new Variable();
                    $variable->setIdTemplate($template);
                    $variable->setBalise($balises[$i]);
                    $variable->setValeur($valeurs[$i]);
                    $em->persist($variable);
                    $em->flush();
                }

                file_put_contents($path . $template->getNom(), $request->request->get("textarea_template"));

                $this->get('app.insert_log')->InsertLog("Création d'un template id:" . $template->getId() . ", nom:" . $template->getNom() . ", association:" . $template->getIdAssociation() . ", model:" . $template->getIdModel() . ", constructeur:" . $template->getIdConstructeur() . ", categorie:" . $template->getIdCategorie() . ", type:" . $template->getIdType(), 0);

                return $this->redirectToRoute('jiwon_cccp_template');
            }
        }

        return $this->render('JiwonCCCPBundle:Admin:new_template.html.twig', array(
                    'associations' => $associations,
                    'modeles' => $modeles,
                    'constructeurs' => $constructeurs,
                    'categories' => $categories,
                    'types' => $types
        ));
    }

    public function downloadCSVAction($path) {
        $em = $this->getDoctrine()->getManager();
        $header = ["Nom de l'equipement", "Ligne de template", "Resultat", "Match\n"];
        #array[0] = SNMP ou SNMP,BGP,ISIS...
        #array[1] = Début du nom du fichier de résultats CSV
        $array = split("-", $path);
        @unlink(__DIR__ . "/../../../../web/" . $array[1] . ".csv");
        if (strpos($array[0], ',') !== false) { #Bouton à gauche pour télécharger toutes les catégories
            file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", (join(";", $header)), FILE_APPEND | LOCK_EX);
            $categories_name = split(",", $array[0]);
            foreach ($categories_name as $cat) {
                $categorie = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneByCategorie($cat);
                $basepath = "/data/scripts/acer_v2/resultats/";
                if ($categorie != null) {
                    $basepath = $basepath . $categorie->getCategorie() . "/";
                    if ($array[1] == "Tous") {
                        $files = glob($basepath . "*.csv");
                    } else {
                        $files = glob($basepath . $array[1] . "*.csv");
                    }
                    foreach ($files as $filepath) {
                        file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", file_get_contents($filepath), FILE_APPEND | LOCK_EX);
                    }
                }
            }
        } else {
            $categorie = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneByCategorie($array[0]);
            $basepath = "/data/scripts/acer_v2/resultats/";
            file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", (join(";", $header)), FILE_APPEND | LOCK_EX);
            if ($categorie != null) {
                $basepath = $basepath . $categorie->getCategorie() . "/";
                if ($array[1] == "Tous") {
                    $files = glob($basepath . "*.csv");
                } else {
                    $files = glob($basepath . $array[1] . "*.csv");
                }
                foreach ($files as $filepath) {
                    file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", file_get_contents($filepath), FILE_APPEND | LOCK_EX);
                }
            }
        }

        $response = new Response;
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $array[1] . '.csv"');
        $response->setContent(file_get_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv"));

        return $response;
    }

    public function showCSVAction(Request $request, $path) {
        $em = $this->getDoctrine()->getManager();
        $array = split("-", $path);

        @unlink(__DIR__ . "/../../../../web/" . $array[1] . ".csv");

        if (strpos($array[0], ',') !== false) { #Bouton à gauche pour télécharger toutes les catégories
            $categories_name = split(",", $array[0]);
            foreach ($categories_name as $cat) {
                $categorie = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneByCategorie($cat);
                $basepath = "/data/scripts/acer_v2/resultats/";
                if ($categorie != null) {
                    $basepath = $basepath . $categorie->getCategorie() . "/";
                    if ($array[1] == "Tous") {
                        $files = glob($basepath . "*.csv");
                    } else {
                        $files = glob($basepath . $array[1] . "*.csv");
                    }
                    foreach ($files as $filepath) {
                        file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", file_get_contents($filepath), FILE_APPEND | LOCK_EX);
                    }
                }
            }
        } else {
            $categorie = $em->getRepository('JiwonCCCPBundle:Categorie')->findOneByCategorie($array[0]);
            $basepath = "/data/scripts/acer_v2/resultats/";
            if ($categorie != null) {
                $basepath = $basepath . $categorie->getCategorie() . "/";
                if ($array[1] == "Tous") {
                    $files = glob($basepath . "*.csv");
                } else {
                    $files = glob($basepath . $array[1] . "*.csv");
                }
                foreach ($files as $filepath) {
                    file_put_contents(__DIR__ . "/../../../../web/" . $array[1] . ".csv", file_get_contents($filepath), FILE_APPEND | LOCK_EX);
                }
            }
        }

        $contents = array();
        if (($handle = fopen(__DIR__ . "/../../../../web/" . $array[1] . ".csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                array_push($contents, $data);
            }
            fclose($handle);
        }
        return $this->render('JiwonCCCPBundle:Default:preview.html.twig', array(
                    'data' => $contents,
                    'title' => "Résultat de " . $path
        ));
    }

}
