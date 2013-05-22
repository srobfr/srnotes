<?php

namespace Sr\NotesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Doctrine\ORM\Query\ResultSetMappingBuilder;

class SearchController extends Controller
{
    /**
     * Affiche la page principale de recherche
     *
     * @Route("/search")
     * @Template()
     */
    public function indexAction()
    {
        $s = $this->getRequest()->get('s', null);
        $q = $this->getRequest()->get('q', null);
        $r = $this->getRequest()->get('r', null);

        return array('s' => $s, 'q' => $q, 'r' => $r);
    }


    /**
     * Construit un arbre d'analyse d'après la requête donnée
     * @param $str
     * @return array
     */
    public function parseRequete($str) {
        if($str == 'R') return array('type' => 'Reference');
        if($str == 'C') return array('type' => 'Complete');
        if($str == 'P') return array('type' => 'Projet');
        if($str == 'T') return array('type' => 'Tache');
        if($str == 'N') return array('type' => 'Prochaine');

        if(preg_match('~^\((.*?)\)$~', $str, $m)) return array('type' => 'Parentheses', 'valeur' => $this->parseRequete($m[1]));

        if(preg_match('~^(.*?)[&,](.*?)$~', $str, $m)) {
            // on vérifie qu'il y ait bien autant de parenthèses ouvrantes que fermantes de chaque coté
            if(substr_count($m[1], '(') == substr_count($m[1], ')') && substr_count($m[2], '(') == substr_count($m[2], ')')) {
                return array('type' => 'Et', 'valeur' => $this->parseRequete($m[1]), 'valeur2' => $this->parseRequete($m[2]));
            }
        }
        if(preg_match('~^(.*?)\|(.*?)$~', $str, $m)) {
            // on vérifie qu'il y ait bien autant de parenthèses ouvrantes que fermantes de chaque coté
            if(substr_count($m[1], '(') == substr_count($m[1], ')') && substr_count($m[2], '(') == substr_count($m[2], ')')) {
                return array('type' => 'Ou', 'valeur' => $this->parseRequete($m[1]), 'valeur2' => $this->parseRequete($m[2]));
            }
        }

        if(preg_match('~^!(.*?)$~', $str, $m)) return array('type' => 'Not', 'valeur' => $this->parseRequete($m[1]));
        if(preg_match('~^[#\.](.*?)$~', $str, $m)) return array('type' => 'Tag', 'valeur' => $m[1]);
        if(preg_match('~^%(.*?)%$~', $str, $m)) return array('type' => 'Texte', 'valeur' => $m[1]);
        if(preg_match('~^\^([0-9]+)$~', $str, $m)) return array('type' => 'Parent', 'valeur' => $m[1]);
        if(preg_match('~^cf([0-9]+)$~', $str, $m)) return array('type' => 'ClosedFor', 'valeur' => $m[1]);
        return array('type' => 'Literal', 'valeur' => $str); // Erreur de syntaxe
    }

    /**
     * Regénère une requête SQL d'après l'arbre d'analyse donné
     * @param $arbre
     * @return string
     */
    public function genererSqlDapresArbre($arbre) {
        $r = '';
        if($arbre['type'] == 'Reference') $r = "n.type=2";
        elseif($arbre['type'] == 'ClosedFor') $r = "n.dateFermeture >= '".date('Y-m-d H:i:s', strtotime("- {$arbre['valeur']} days"))."'";
        elseif($arbre['type'] == 'Complete') $r = "n.pcAvancement==100";
        elseif($arbre['type'] == 'Prochaine') $r = "n.prochaine==1";
        elseif($arbre['type'] == 'Projet') $r = "n.type==3";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == 'Projet') $r = "n.type!=3";
        elseif($arbre['type'] == 'Tache') $r = "n.type==1";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == 'Tache') $r = "n.type!=1";
        elseif($arbre['type'] == 'Reference') $r = "n.type==1";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == 'Reference') $r = "n.type!=2";
        elseif($arbre['type'] == 'Texte') $r = "(n.note LIKE '%{$arbre['valeur']}%' OR n.titre LIKE '%{$arbre['valeur']}%')";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == "Texte") $r = "(n.note NOT LIKE '%{$arbre['valeur']['valeur']}%' AND n.titre NOT LIKE '%{$arbre['valeur']['valeur']}%')";
        elseif($arbre['type'] == 'Tag') $r = "EXISTS (SELECT 1 FROM Tag INNER JOIN note_tag ON Tag.id = tag_id WHERE note_id = n.id AND Tag.titre='{$arbre['valeur']}')";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == "Tag") $r = "NOT " . $this->genererSqlDapresArbre($arbre['valeur']);
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == "Complete") $r = "n.pcAvancement<100";
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == "Prochaine") $r = "n.prochaine==0";
        elseif($arbre['type'] == 'Parentheses') $r = "(".$this->genererSqlDapresArbre($arbre['valeur']) . ")";
        elseif($arbre['type'] == 'Ou') $r = $this->genererSqlDapresArbre($arbre['valeur'])." OR ". $this->genererSqlDapresArbre($arbre['valeur2']);
        elseif($arbre['type'] == 'Et') $r = $this->genererSqlDapresArbre($arbre['valeur']) . " AND " . $this->genererSqlDapresArbre($arbre['valeur2']);
        elseif($arbre['type'] == 'Parent') $r = "n.parent_id=" . $arbre['valeur'];
        elseif($arbre['type'] == 'Not' && $arbre['valeur']['type'] == 'Parent') $r = "n.parent_id!=" . $arbre['valeur']['valeur'];
        elseif(is_array($arbre['valeur'])) $r = $this->genererSqlDapresArbre($arbre['valeur']);
        else return '';
        return $r;
    }

    public function genererRequeteSqlDapresRequete($requete) {
        $arbre = $this->parseRequete($requete);
        //var_dump($arbre);
        $sql = $this->genererSqlDapresArbre($arbre);
        if('' != $sql) $sql = "WHERE $sql";
        $s = "SELECT n.* FROM Note n $sql ORDER BY n.type ASC, n.prochaine DESC, n.enAttente ASC, n.pcAvancement ASC";
        //echo "$requete => $s"; die;
        return $s;
    }

    /**
     * Effectue la recherche et affiche le résultat sous forme de tableau
     * @param $s Un terme à chercher
     * @param $q Une requête DSQL à exécuter
     * @param $r Une requête
     * @Template()
     */
    public function listeAction($s, $q, $r) {
        $em = $this->getDoctrine()->getManager();
        $res = array();

        if(!is_null($r)) {
            $sql = $this->genererRequeteSqlDapresRequete($r);

            $rsm = new ResultSetMappingBuilder($em);
            $rsm->addRootEntityFromClassMetadata('Sr\NotesBundle\Entity\Note', 'n');
            $q = $em->createNativeQuery($sql, $rsm);
            $res = $q->getResult();

        } elseif(!is_null($q)) {
            $q = $em->createQuery($q);
            $res = $q->getResult();

        } elseif(!is_null($s)) {
            $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.titre LIKE :s OR n.note LIKE :s")
                ->setParameter('s', "%$s%");
            $res = $q->getResult();
        }

        return array('notes' => $res, 's' => $s, 'r' => $r, 'q' => $q);
    }
}
