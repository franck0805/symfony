<?php

namespace Jiwon\AuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Jiwon\AuditBundle\Entity\Template;
use Jiwon\AuditBundle\Entity\Crontab;
use Jiwon\AuditBundle\Entity\Variable;
use Jiwon\AuditBundle\Repository\CrontabRepository;
use Jiwon\AuditBundle\Repository\TemplateRepository;
use Jiwon\AuditBundle\Repository\VariableRepository;
use Jiwon\AuditBundle\Form\CrontabType;
use \DateTime;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $associations = $em->getRepository('JiwonAdminBundle:Association')->findAllOrdered();
        $templates = $em->getRepository('JiwonAuditBundle:Template')->findAllOrdered();
        $template = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get('template'));
        $path = "/data/scripts/acer/templates/";
        $date = date("dmY-His");
        $session = $this->get(s'session');

        if ($request->isMethod('POST')) {
            if ($request->request->get('hand') != null) {
                $text = trim($request->request->get('hand'));
                $filecsv = @fopen("/data/scripts/acer/ne_list/" . $template->getNom() . "." . $date . ".csv", 'w');
                @fwrite($filecsv, $text);
                @chmod("/data/scripts/acer/ne_list/" . $template->getNom() . "." . $date . ".csv", 0644);
                @fclose($filecsv);
                shell_exec("perl /data/scripts/acer/audit.pl " . $template->getNom() . " " . $request->request->get("conf") . " " . $template->getNom() . "." . $date . ".csv 2>/dev/null >/dev/null &");
            } elseif ($request->request->get('ne') != null) {
                $filecsv = @fopen("/data/scripts/acer/ne_list/" . $template->getNom() . "." . $date . ".csv", 'w');
                foreach ($request->request->get('ne') as $i) {
                    $ne = $em->getRepository('JiwonAdminBundle:Ne')->findOneById($i);
                    @fwrite($filecsv, $ne->getNe() . ";" . $ne->getIp() . "\n");
                }
                shell_exec("perl /data/scripts/acer/audit.pl " . $template->getNom() . " " . $request->request->get("conf") . " " . $template->getNom() . "." . $date . ".csv 2>/dev/null >/dev/null &");
            } else {
                shell_exec("perl /data/scripts/acer/audit.pl " . $template->getNom() . " " . $request->request->get("conf") . " 2>/dev/null >/dev/null &");
            }
            $reseau = $request->request->get("reseau");
            $model = $request->request->get("model");
            $constructeur = $request->request->get("constructeur");
            $template = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get("template"));

            $this->get('session')->set('reseau', $reseau);
            $this->get('session')->set('model', $model);
            $this->get('session')->set('constructeur', $constructeur);

            return $this->render('JiwonAuditBundle:Default:index.html.twig', array(
                        'associations' => $associations,
                        'reseau' => $reseau,
                        'modele' => $model,
                        'construc' => $constructeur,
                        'templates' => $templates,
                        'conf' => $request->request->get("conf"),
                        'success' => "Audit lancé avec succès. Comptez quelques secondes de traitement pour chaque équipement concerné."
            ));
        }

        return $this->render('JiwonAuditBundle:Default:index.html.twig', array(
                    'associations' => $associations,
                    'templates' => $templates,
                    'reseau' => $session->get('reseau'),
                    'modele' => $session->get('model'),
                    'construc' => $session->get('constructeur')
        ));
    }

    public function scheduleAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $associations = $em->getRepository('JiwonAdminBundle:Association')->findAllOrdered();
        $templates = $em->getRepository('JiwonAuditBundle:Template')->findAllOrdered();
        $exports = $em->getRepository('JiwonAuditBundle:Export')->findAllOrdered();
        $session = $this->get('session');

        if ($request->isMethod('POST')) {
            $crontab = new Crontab();
            $template = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get('template'));
            $crontab->setIdTemplate($template);
            $crontab->setRecurrence($request->request->get('recurrence'));
            $crontab->setType($request->request->get('type'));
            $date = $request->request->get('time');
            $datetime = date("dmY-His");
            $crontab->setHeure($date);
            if ($request->request->get('ne') != null) {
                $filecsv = fopen("/data/scripts/acer/ne_list/" . $template->getNom() . "." . $datetime . ".csv", 'w');
                foreach ($request->request->get('ne') as $i) {
                    $ne = $em->getRepository('JiwonAdminBundle:Ne')->findOneById($i);
                    fwrite($filecsv, $ne->getNe() . ";" . $ne->getIp() . "\n");
                }
                $crontab->setCsv($template->getNom() . "." . $datetime . ".csv");
            }
            if ($request->request->get('export') != null) {
                foreach ($request->request->get('export') as $export) {
                    $res = $em->getRepository('JiwonAuditBundle:Export')->findOneById($export);
                    $crontab->addExport($res);
                }
            }
            if ($request->request->get('enable') == null)
                $crontab->setEnable(0);
            else
                $crontab->setEnable(1);
            $em->persist($crontab);
            $em->flush();

            shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');
            sleep(5);

            $this->get('session')->set('reseau', $template->getIdAssociation()->getId());
            $this->get('session')->set('model', $request->request->get('model'));
            $this->get('session')->set('constructeur', $request->request->get('constructeur'));

            return $this->render('JiwonAuditBundle:Default:schedule.html.twig', array(
                        'associations' => $associations,
                        'exports' => $exports,
                        'reseau' => $template->getIdAssociation()->getId(),
                        'construc' => $request->request->get('constructeur'),
                        'modele' => $request->request->get("model"),
                        'templates' => $templates
            ));
        }

        return $this->render('JiwonAuditBundle:Default:schedule.html.twig', array(
                    'associations' => $associations,
                    'exports' => $exports,
                    'templates' => $templates,
                    'reseau' => $session->get('reseau'),
                    'modele' => $session->get('model'),
                    'construc' => $session->get('constructeur')
        ));
    }

    public function adminscheduleAction() {
        $em = $this->getDoctrine()->getManager();
        $schedules = $em->getRepository('JiwonAuditBundle:Crontab')->findAllOrdered();

        return $this->render('JiwonAuditBundle:Admin:adminschedule.html.twig', array(
                    'schedules' => $schedules
        ));
    }

    public function disableadminscheduleAction($id) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->findOneById($id);
        $schedule->setEnable(0);
        $em->persist($schedule);
        $em->flush();
        shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');
        return $this->redirectToRoute('jiwon_audit_schedule_admin');
    }

    public function disablescheduleAction($id) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->findOneById($id);
        $schedule->setEnable(0);
        $em->persist($schedule);
        $em->flush();
        shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');
        return $this->redirectToRoute('jiwon_audit_schedule');
    }

    public function enableadminscheduleAction($id) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->findOneById($id);
        $schedule->setEnable(1);
        $em->persist($schedule);
        $em->flush();
        shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');
        return $this->redirectToRoute('jiwon_audit_schedule_admin');
    }

    public function templateAction(request $request) {
        $em = $this->getDoctrine()->getManager();
        $associations = $em->getRepository('JiwonAdminBundle:Association')->findAllOrdered();
        $path = "/data/scripts/acer/templates/";
        $templates = $em->getRepository('JiwonAuditBundle:Template')->findAllOrdered();
        $session = $this->get('session');

        if ($request->isMethod("POST")) {
            $template = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get("template"));
            $this->get('session')->set('reseau', $request->request->get('reseau'));
            $this->get('session')->set('model', $request->request->get('model'));
            $this->get('session')->set('constructeur', $request->request->get('constructeur'));
            $this->get('session')->set('template', $request->request->get('template'));

            if ($request->request->get("btnSubmit") == "save") {
                $content = $request->request->get("textarea_template");
                $balises = $request->request->get("balise");
                $valeurs = $request->request->get("valeur");
                $main = $request->request->get("main") . "\n";

                if ($request->request->get("template") == null) {
                    $reseau = $request->request->get("reseau");
                    $constructeur = $request->request->get("constructeur");
                    $model = $request->request->get("model");
                    if ($em->getRepository('JiwonAuditBundle:Template')->findOneByNom($request->request->get("template_name"))) {
                        if ($request->request->get("source") == "DB") {
                            $balises_valeurs = array();
                            for ($i = 0; $i < count($balises); $i++)
                                $balises_valeurs[$i] = array($balises[$i], $valeurs[$i]);

                            return $this->render('JiwonAuditBundle:Default:template.html.twig', array(
                                        'error' => "Erreur : Ce nom de template existe déjà.",
                                        'reseau' => $reseau,
                                        'construc' => $constructeur,
                                        'modele' => $model,
                                        'associations' => $associations,
                                        'template_content' => $request->request->get("textarea_template"),
                                        'balises_valeurs' => $balises_valeurs,
                                        'nom' => $request->request->get("template_name"),
                                        'templates' => $templates,
                                        'commentaire' => $request->request->get("template_commentaire")
                            ));
                        } else {
                            return $this->render('JiwonAuditBundle:Default:template.html.twig', array(
                                        'error' => "Erreur : Ce nom de template existe déjà.",
                                        'reseau' => $template->getIdAssociation()->getId(),
                                        'construc' => $constructeur,
                                        'modele' => $model,
                                        'associations' => $associations,
                                        'template_content' => $request->request->get("textarea_template"),
                                        'main' => $main,
                                        'nom' => $request->request->get("template_name"),
                                        'templates' => $templates,
                                        'commentaire' => $request->request->get("template_commentaire")
                            ));
                        }
                    }
                    $template = New Template();
                    $template->setNom($request->request->get("template_name"));
                    $template->setVersion($request->request->get("template_version"));
                    $template->setType("Type");
                    $template->setIdAssociation($em->getRepository('JiwonAdminBundle:Association')->findOneById($reseau));
                    $template->setIdModele($em->getRepository('JiwonAdminBundle:Model')->findOneById($model));
                    $template->setIdConstructeur($em->getRepository('JiwonAdminBundle:Constructeur')->findOneById($constructeur));
                    $template->setCommentaire($request->request->get("template_commentaire"));
                    $em->persist($template);
                    $em->flush();
                } else {
                    $template = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get("template"));
                    $template->setCommentaire($request->request->get("template_commentaire"));
                    $variables = $em->getRepository('JiwonAuditBundle:Variable')->findBy(array("id_template" => $template));
                    if ($variables != null) {
                        foreach ($variables as $var) {
                            $em->remove($var);
                            $em->flush();
                        }
                    }
                }
                if ($request->request->get("source") == "DB") {
                    for ($i = 0; $i < count($balises); $i++) {
                        $variable = new Variable();
                        $variable->setIdTemplate($template);
                        $variable->setBalise($balises[$i]);
                        $variable->setValeur($valeurs[$i]);
                        $em->persist($variable);
                        $em->flush();
                    }
                } else {
                    $text = trim($main);
                    $separator = "\r\n";
                    explode(PHP_EOL, $text);
                    $line = strtok($text, $separator);
                    while ($line !== false) {
                        $keywords = preg_split("/[;]+/", $line);
                        $variable = new Variable();
                        $variable->setIdTemplate($template);
                        $variable->setBalise($keywords[0]);
                        $variable->setValeur($keywords[1]);
                        $em->persist($variable);
                        $em->flush();
                        $line = strtok($separator);
                    }
                }

                if ($request->request->get("template_name") != $template->getNom()) {
                    rename($path . $template->getNom(), $path . $request->request->get("template_name"));
                    @rename('/data/scripts/acer/resultats/' . $template->getNom() . '/', '/data/scripts/acer/resultats/' . $request->request->get("template_name") . '/');
                    $template->setNom($request->request->get("template_name"));
                    $em->persist($template);
                    $em->flush();
                }
                file_put_contents($path . $template->getNom(), $request->request->get("textarea_template"));

                return $this->redirectToRoute('jiwon_audit_template');
            } else {
                //Chargement du template dans le textarea
                $template_object = $em->getRepository('JiwonAuditBundle:Template')->findOneById($request->request->get("template"));
                $variables = $em->getRepository('JiwonAuditBundle:Variable')->findBy(array('id_template' => $request->request->get("template")));
                $template_content = file_get_contents($path . $template_object->getNom());
                $reseau = $request->request->get("association");
                $constructeur = $request->request->get("constructeur");
                $model = $request->request->get("model");
                return $this->render('JiwonAuditBundle:Default:template.html.twig', array(
                            'reseau' => $template_object->getIdAssociation()->getId(),
                            'construc' => $constructeur,
                            'modele' => $model,
                            'associations' => $associations,
                            'template' => $request->request->get("template"),
                            'template_content' => $template_content,
                            'variables' => $variables,
                            'templates' => $templates,
                            'name' => $template_object->getNom(),
                            'version' => $request->request->get("template_version"),
                            'commentaire' => $template_object->getCommentaire()
                ));
            }
        }

        return $this->render('JiwonAuditBundle:Default:template.html.twig', array(
                    'associations' => $associations,
                    'templates' => $templates,
                    'reseau' => $session->get('reseau'),
                    'modele' => $session->get('model'),
                    'construc' => $session->get('constructeur'),
                    'template' => $session->get('template')
        ));
    }

    public function rechercheAction() {
        $em = $this->getDoctrine()->getManager();
        $associations = $em->getRepository('JiwonAdminBundle:Association')->findAllOrdered();
        $templates = $em->getRepository('JiwonAuditBundle:Template')->findAllOrdered();
        $session = $this->get('session');

        return $this->render('JiwonAuditBundle:Default:recherche.html.twig', array(
                    'associations' => $associations,
                    'templates' => $templates,
                    'reseau' => $session->get('reseau'),
                    'model' => $session->get('model'),
                    'construc' => $session->get('constructeur'),
                    'template' => $session->get('template'),
        ));
    }

    public function modscheduleAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->find($id);
        $log = "Modification du schedule:" . $schedule->getId() . ", recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable();

        if ($schedule == null) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Le schedule n'existe pas."
            ));
        }

        $form = $this->createForm(new CrontabType($em), $schedule);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (!$form->isValid()) {
                return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                            'error' => $form->getErrorsAsString()
                ));
            }

            $em->persist($schedule);
            $em->flush();
            $this->get('app.insert_log')->InsertLog($log . " en recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable(), 0);
            shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');

            return $this->redirectToRoute('jiwon_audit_schedule');
        }

        return $this->render('JiwonAuditBundle:Admin:admin_schedule.html.twig', array(
                    'form' => $form->createView(),
                    'schedule' => $schedule,
        ));
    }

    public function delscheduleAction($id) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->find($id);

        if ($schedule == null) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Le schedule n'existe pas."
            ));
        }

        $em->remove($schedule);
        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Ce schedule ne peut pas être supprimé car d'autres éléments de la BDD dépendent de lui."
            ));
        }
        $em->flush();
        $this->get('app.insert_log')->InsertLog("Suppression du schedule de recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable(), 0);
        shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');

        return $this->redirectToRoute('jiwon_audit_schedule');
    }

    public function modadminscheduleAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->find($id);
        $log = "Modification du schedule:" . $schedule->getId() . ", recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable();

        if ($schedule == null) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Le schedule n'existe pas."
            ));
        }

        $form = $this->createForm(new CrontabType($em), $schedule);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (!$form->isValid()) {
                return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                            'error' => $form->getErrorsAsString()
                ));
            }

            $em->persist($schedule);
            $em->flush();
            $this->get('app.insert_log')->InsertLog($log . " en recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable(), 0);
            shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');

            return $this->redirectToRoute('jiwon_audit_schedule_admin');
        }

        return $this->render('JiwonAuditBundle:Admin:admin_schedule.html.twig', array(
                    'form' => $form->createView(),
                    'schedule' => $schedule,
        ));
    }

    public function deladminscheduleAction($id) {
        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository('JiwonAuditBundle:Crontab')->find($id);

        if ($schedule == null) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Le schedule n'existe pas."
            ));
        }

        $em->remove($schedule);
        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Ce schedule ne peut pas être supprimé car d'autres éléments de la BDD dépendent de lui."
            ));
        }
        $em->flush();
        $this->get('app.insert_log')->InsertLog("Suppression du schedule de recurrence:" . $schedule->getRecurrence() . ", heure:" . $schedule->getHeure() . ", enable:" . $schedule->getEnable(), 0);
        shell_exec('perl /data/scripts/generate_crontab_acer.pl 2>/dev/null >/dev/null &');

        return $this->redirectToRoute('jiwon_audit_schedule_admin');
    }

    public function csvAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('JiwonAuditBundle:Template')->findOneBy(array('id' => $request->request->get('id')));
        $page = $request->request->get('page');
        $files = preg_grep('/^([^.])/', scandir('/data/scripts/acer/resultats/' . $template->getNom(), SCANDIR_SORT_ASCENDING));
        $res = 0;
        foreach ($files as $file) {
            if ($res < filectime('/data/scripts/acer/resultats/' . $template->getNom() . '/' . $file)) {
                $res = filectime('/data/scripts/acer/resultats/' . $template->getNom() . '/' . $file);
                $newest_file = $file;
            }
        }
        $contents = array();
        $sliced = array();
        $i = 1;
        if (($handle = fopen('/data/scripts/acer/resultats/' . $template->getNom() . '/' . $newest_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                array_push($contents, $data);
            }
            fclose($handle);
        }
        if ($page == null)
            $page = 1;
        $sliced = array_slice($contents, ($page - 1) * 300, 300);
        $total = count($contents);
        while ($i <= ($total / 300) + 1) {
            $tableau[] = $i;
            $i++;
        }

        return $this->render('JiwonAuditBundle:Default:ajax_csv.html.twig', array(
                    'name' => $request->request->get('id'),
                    'total' => $tableau,
                    'page' => $page,
                    'data' => $sliced
        ));
    }

    public function filesAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('JiwonAuditBundle:Template')->findOneBy(array('id' => $request->request->get('id')));
        $files = preg_grep('/^([^.])/', scandir('/data/scripts/acer/resultats/' . $template->getNom(), SCANDIR_SORT_ASCENDING));
        $list = array();

        if ($request->request->get('debut') == null && $request->request->get('fin') == null)
            $list = $files;
        elseif ($request->request->get('debut') != null && $request->request->get('fin') == null) {
            $debut = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('debut'));
            $debut = $debut->getTimestamp();
            foreach ($files as $file) {
                $keywords = preg_split("/[-\.]/", $file);
                $date = DateTime::createFromFormat('Ymd Hi', $keywords[0] . ' ' . $keywords[1]);
                $date = $date->getTimestamp();
                $debut = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('debut'));
                $debut = $debut->getTimestamp();
                if ($debut <= $date)
                    array_push($list, $file);
            }
        }
        elseif ($request->request->get('debut') == null && $request->request->get('fin') != null) {
            $debut = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('debut'));
            $debut = $debut->getTimestamp();
            foreach ($files as $file) {
                $keywords = preg_split("/[-\.]/", $file);
                $date = DateTime::createFromFormat('Ymd Hi', $keywords[0] . ' ' . $keywords[1]);
                $date = $date->getTimestamp();
                $fin = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('fin'));
                $fin = $fin->getTimestamp();
                if ($fin >= $date)
                    array_push($list, $file);
            }
        }
        elseif ($request->request->get('debut') != null && $request->request->get('fin') != null) {
            $debut = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('debut'));
            $debut = $debut->getTimestamp();
            $fin = DateTime::createFromFormat('d/m/Y H:i', $request->request->get('fin'));
            $fin = $fin->getTimestamp();

            foreach ($files as $file) {
                $keywords = preg_split("/[-\.]/", $file);
                $date = DateTime::createFromFormat('Ymd Hi', $keywords[0] . ' ' . $keywords[1]);
                $date = $date->getTimestamp();
                if ($debut <= $date && $fin >= $date)
                    array_push($list, $file);
            }
        }

        return $this->render('JiwonAuditBundle:Default:ajax_files.html.twig', array(
                    'results' => $list,
        ));
    }

    public function filecsvpreviewAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('JiwonAuditBundle:Template')->findOneBy(array('id' => $request->request->get('id')));
        $page = $request->request->get('page');
        $file = '/data/scripts/acer/resultats/' . $template->getNom() . '/' . $request->request->get('file');
        $contents = array();
        $sliced = array();
        $i = 1;
        $first = null;
        $count = 1;
        $tests = 0;
        $totals = 0;
        $ne = "0";
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if ($ne != $data[0] && $ne == "0") {
                    $ne = $data[0];
                    $first = $ne;
                }
                if ($ne != $data[0]) {
                    if ($count == 0) {
                        array_push($contents, [$ne, "Not OK", "Tests : " . $tests . "/" . $totals]);
                        $tests = 0;
                        $totals = 0;
                        $count = 1;
                    } else {
                        array_push($contents, [$ne, "OK", "Tests : " . $tests . "/" . $totals]);
                        $tests = 0;
                        $totals = 0;
                        $count = 1;
                    }
                }
                if ($data[2] == "OK") {
                    $tests++;
                    $totals++;
                } else {
                    $count = 0;
                    $totals++;
                }
                $ne = $data[0];
            }
            fclose($handle);
        }
        if ($first != null && $count == 1)
            array_push($contents, [$ne, "OK", "Tests : " . $tests . "/" . $totals]);
        elseif ($first != null && $count == 0)
            array_push($contents, [$ne, "Not OK", "Tests : " . $tests . "/" . $totals]);
        if ($page == null)
            $page = 1;
        $sliced = array_slice($contents, ($page - 1) * 300, 300);
        $total = count($contents);
        while ($i <= ($total / 300) + 1) {
            $tableau[] = $i;
            $i++;
        }

        return $this->render('JiwonAuditBundle:Default:ajax_csv_preview.html.twig', array(
                    'name' => $request->request->get('id'),
                    'total' => $tableau,
                    'page' => $page,
                    'data' => $sliced,
                    'template' => $template->getNom(),
                    'file' => $request->request->get('file')
        ));
    }

    public function downloadcsvAction($template, $file, $ne) {
        $em = $this->getDoctrine()->getManager();
        $header = array();
        $result = '/data/scripts/acer/resultats/' . $template . '/' . $file;
        if ($ne == null)
            $fh = fopen(__DIR__ . "/../../../../web/" . $file, "w");
        else
            $fh = fopen(__DIR__ . "/../../../../web/" . $file . "." . $ne . ".csv", "w");
        $header = ["Nom de l'equipement", "Ligne de template", "Resultat", "Match"];
        fputcsv($fh, $header, ";");
        if (($handle = fopen($result, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if ($ne != null) {
                    if ($ne == $data[0])
                        fputcsv($fh, $data, ";");
                } else
                    fputcsv($fh, $data, ";");
            }
            fclose($handle);
        }
        fclose($fh);
        $response = new Response;
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        if ($ne == null) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file . '"');
            $response->setContent(file_get_contents(__DIR__ . "/../../../../web/" . $file));
        } else {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file . '.' . $ne . '.csv');
            $response->setContent(file_get_contents(__DIR__ . "/../../../../web/" . $file . "." . $ne . ".csv"));
        }

        return $response;
    }

    public function admintemplateAction() {
        $em = $this->getDoctrine()->getManager();
        $templates = $em->getRepository('JiwonAuditBundle:Template')->findAllOrdered();

        return $this->render('JiwonAuditBundle:Admin:admin_template.html.twig', array(
                    'templates' => $templates
        ));
    }

    public function deladmintemplateAction($id) {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('JiwonAuditBundle:Template')->find($id);

        if ($template == null) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Le template n'existe pas."
            ));
        }

        $variables = $em->getRepository('JiwonAuditBundle:Variable')->findAllWithTemplate($id);
        $crontabs = $em->getRepository('JiwonAuditBundle:Crontab')->findAllWithTemplate($id);
        foreach ($variables as $variable)
            $em->remove($variable);
        foreach ($crontabs as $crontab)
            $em->remove($crontab);
        $em->remove($template);
        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->render('JiwonAdminBundle:Default:success.html.twig', array(
                        'error' => "Ce template ne peut pas être supprimé car d'autres éléments de la BDD dépendent de lui."
            ));
        }
        $em->flush();
        $this->get('app.insert_log')->InsertLog("Suppression du template de nom:" . $template->getNom(), 0);
        @unlink('/data/scripts/acer/templates/' . $template->getNom());

        return $this->redirectToRoute('jiwon_audit_template_admin');
    }

    public function resultAction($template, $file, $ne) {
        $contents = array();
        $result = '/data/scripts/acer/resultats/' . $template . '/' . $file;
        if (($handle = fopen($result, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if ($ne == null) {
                    array_push($contents, $data);
                } else {
                    if ($ne == $data[0]) {
                        array_push($contents, $data);
                    }
                }
            }
            fclose($handle);
        }
        return $this->render('JiwonAuditBundle:Default:csv.html.twig', array(
                    'data' => $contents,
        ));
    }

}
