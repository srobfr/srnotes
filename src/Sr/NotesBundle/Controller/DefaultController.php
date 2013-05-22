<?php

namespace Sr\NotesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Doctrine\ORM\Query\ResultSetMappingBuilder;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        // On récupère les infos pour l'affichage du résumé

        $em = $this->getDoctrine()->getManager();

        $r = array();

        // Les tâche inbox
        $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.pcAvancement < 100 AND n.parent=1");
        $r['inbox'] = $q->getResult();

        // Les tâches en attente
        $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.pcAvancement < 100 AND n.enAttente=1");
        $r['enAttente'] = $q->getResult();

        // Les tâches ouvertes
        $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.pcAvancement<100 AND n.type=1 ORDER BY n.prochaine DESC, n.enAttente ASC");
        $r['ouvertes'] = $q->getResult();

        // On trouve les projets qui n'ont pas de prochaine tâche (mais qui ont quand même au moins une tache ouverte)
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('Sr\NotesBundle\Entity\Note', 'n');
        $q = $em->createNativeQuery("SELECT n.*
            FROM Note n
            WHERE n.type=3 --projet
            AND n.pcAvancement<100 --ouvert
            AND NOT EXISTS ( -- sans prochaine tache ouverte
                SELECT 1 FROM Note nn
                WHERE nn.parent_id = n.id
                AND nn.pcAvancement<100
                AND nn.type = 1
                AND nn.prochaine = 1)
            AND EXISTS ( -- Mais avec des taches ouvertes
                SELECT 1 FROM Note nn
                WHERE nn.parent_id = n.id
                AND nn.pcAvancement<100
                AND nn.type = 1)
            AND NOT EXISTS ( -- Mais sans projet ouvert
                SELECT 1 FROM Note nn
                WHERE nn.parent_id = n.id
                AND nn.type = 3
                AND nn.pcAvancement<100)
            ", $rsm);
        $r['projetsSansProchaineTache'] = $q->getResult();

        // Ou trouve les prjets sans tâche ouverte
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('Sr\NotesBundle\Entity\Note', 'n');
        $q = $em->createNativeQuery("SELECT n.*
            FROM Note n
            WHERE n.type=3 --projet
            AND n.pcAvancement<100 --ouvert
            AND n.id != 1 -- Pas la inbox
            AND NOT EXISTS ( -- Sans tâche ouverte
                SELECT 1 FROM Note nn
                WHERE nn.parent_id = n.id
                AND nn.pcAvancement<100
                AND nn.type = 1)
            AND NOT EXISTS ( -- Mais sans projet ouvert
                SELECT 1 FROM Note nn
                WHERE nn.parent_id = n.id
                AND nn.type = 3
                AND nn.pcAvancement<100)
            ", $rsm);
        $r['projetsSansTacheOuverte'] = $q->getResult();

        $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.pcAvancement<100 AND n.type=1 AND n.dateLimite IS NOT NULL ORDER BY n.dateLimite ASC");
        $r['tachesAvecDateLimite'] = $q->getResult();

        return $r;
    }

    /**
     * Retourne une liste de liens de tags
     * @Template()
     */
    public function listtagsAction() {
        $em = $this->getDoctrine()->getManager();

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('Sr\NotesBundle\Entity\Tag', 't');
        $q = $em->createNativeQuery("SELECT t.*
            FROM Tag t
            WHERE EXISTS (SELECT 1 FROM Note n
                INNER JOIN note_tag nt ON n.id = nt.note_id
                WHERE t.id = nt.tag_id
                AND n.pcAvancement < 100)", $rsm);

        $r = array('tags' => $q->getResult());
        return $r;
    }

    /**
     * Retourne une liste de tags pour l'ajout dans le champ tags
     * @Template()
     */
    public function listTagsPourAjoutAction() {
        return $this->listtagsAction();
    }

    /**
     * Retourne le code HTML des raccourcis, dans la zone de gauche
     */
    public function raccourcisAction() {
        $note = $this->getDoctrine()->getRepository("SrNotesBundle:Note")->findOneByTitre("_raccourcis");
        $r = '';
        if(!is_null($note)) $r = $note->getNote();
        $r = str_replace('//', '/', str_replace('%ROOT%', $this->generateUrl('sr_notes_default_index'), $r));
        return new \Symfony\Component\HttpFoundation\Response($r);
    }

    /**
     * Affiche des stats dans la topbar
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statsAction() {
        $em = $this->getDoctrine()->getManager();

        $nbJours = 7;

        $query = $em->createQuery('SELECT COUNT(n) FROM SrNotesBundle:Note n WHERE n.dateCreation >= :date');
        $query->setParameter('date', \DateTime::createFromFormat('U', strtotime("-$nbJours days")));
        $count = $query->getSingleScalarResult();
        $r = "$count ouvertes, ";

        $query = $em->createQuery('SELECT COUNT(n) FROM SrNotesBundle:Note n WHERE n.dateFermeture >= :date');
        $query->setParameter('date', \DateTime::createFromFormat('U', strtotime("-$nbJours days")));
        $count = $query->getSingleScalarResult();
        $r .= "$count fermées dans les $nbJours derniers jours";
        return new \Symfony\Component\HttpFoundation\Response($r);
    }
}
