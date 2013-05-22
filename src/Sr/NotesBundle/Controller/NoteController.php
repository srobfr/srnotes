<?php

namespace Sr\NotesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Sr\NotesBundle\Entity\Note;
use \Sr\NotesBundle\Entity\Tag;
use \Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    /**
     * Affiche la page de modification d'une note
     *
     * @Route("/note/{id}", requirements={"id" = "\d+"})
     * @Template()
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('SrNotesBundle:Note')->findOneById($id);

        $q = $em->createQuery("SELECT n FROM SrNotesBundle:Note n WHERE n.pcAvancement < 100 AND n.parent=:parentId ORDER BY n.pcAvancement ASC, n.prochaine DESC, n.enAttente ASC");
        $q->setParameter('parentId', $note->getId());
        $enfants = $q->getResult();

        $existingFiles = $this->get('punk_ave.file_uploader')->getFiles(array(
            'folder' => $this->getUploadDir($id),
        ));

        return array('note' => $note, 'enfants' => $enfants, 'files' => $existingFiles);
    }

    /**
     * Démarre le chrono d'une note
     *
     * @Route("/chrono/start/{id}", requirements={"id" = "\d+"})
     */
    public function chronoStartAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('SrNotesBundle:Note')->findOneById($id);

        $chrono = $this->get('sr_notes.chrono');
        $chrono->start($note);

        return $this->forward('SrNotesBundle:Note:chrono');
    }

    /**
     * Arrête le chrono d'une note
     * @Route("/chrono/stop")
     */
    public function chronoStopAction() {
        $chrono = $this->get('sr_notes.chrono');
        $chrono->stop();

        return $this->forward('SrNotesBundle:Note:chrono');
    }

    /**
     * Affiche le chrono courant
     * @Template()
     */
    public function chronoAction() {
        $chrono = $this->get('sr_notes.chrono');
        return array(
            'timer' => $chrono->getTimer(),
            'note' => $chrono->getNote(),
        );
    }

    /**
     * Enregistre une note
     * @Route("/note/save")
     * @Template()
     */
    public function saveAction() {
        $em = $this->getDoctrine()->getManager();

        $note = null;
        if(!is_null($this->getRequest()->get('id'))) {
            $note = $em->getRepository('SrNotesBundle:Note')->findOneById($this->getRequest()->get('id'));
        }

        if(is_null($note)) {
            $note = new Note();
            $note->setType(Note::TYPE_NOTE); // Type par défaut
            $note->setDateCreation(new \DateTime());
            $note->setPcAvancement(0);
            $note->setProchaine(false);
            $note->setEnAttente(false);
            $em->persist($note);
        }

        $note->setTitre($this->getRequest()->get('titre', $note->getTitre()));
        $note->setNote($this->getRequest()->get('note', $note->getNote()));
        $note->setPcAvancement($this->getRequest()->get('pcAvancement', $note->getPcAvancement()));
        if(!is_null($this->getRequest()->get('prochaine'))) $note->setProchaine($this->getRequest()->get('prochaine') == 'true');
        if(!is_null($this->getRequest()->get('enAttente'))) $note->setEnAttente($this->getRequest()->get('enAttente') == 'true');
        $note->setType($this->getRequest()->get('type', $note->getType()));

        $dateLimite = $this->getRequest()->get('dateLimite', null);
        if(!is_null($dateLimite)) {
            if(!empty($dateLimite)) {
                $note->setDateLimite(\DateTime::createFromFormat("Y-m-d", $dateLimite));

            } else {
                $note->setDateLimite(null);
            }
        }

        $parentId = $this->getRequest()->get('parentId');
        if(!is_null($parentId)) {
            $parent = $em->getRepository('SrNotesBundle:Note')->findOneById($parentId);
            $note->setParent($parent);
        }

        $titresTags = $this->getRequest()->get('tags', null);
        if(!is_null($titresTags)) {
            $tags = array();

            $titresTags = explode(',', $titresTags);
            foreach($titresTags as $titre) {
                $titre = trim($titre);
                if($titre == '') continue;

                $tag = $em->getRepository('SrNotesBundle:Tag')->findOneByTitre($titre);
                if(is_null($tag)) {
                    $tag = new Tag();
                    $tag->setTitre($titre);
                    $r = rand(160, 255); $v = rand(160, 255); $b = rand(160, 255);
                    $hex = base_convert($r, 10, 16) . base_convert($v, 10, 16). base_convert($b, 10, 16);
                    $tag->setCouleur($hex);

                    $em->persist($tag);
                    $em->flush();
                }

                if(!in_array($tag, $tags)) $tags[] = $tag;
            }

            $note->setTags($tags);
        }

        $em->flush();

        $em->getRepository('SrNotesBundle:Tag')->nettoyerTags();

        if($this->getRequest()->get('redirToNote')) {
            return $this->redirect($this->generateUrl('sr_notes_note_index', array('id' => $note->getId())));

        } elseif($this->getRequest()->get('redirTo')) {
            return $this->redirect($this->getRequest()->get('redirTo'));

        } else {
            return new Response('OK');
        }
    }

    /**
     * Retourne la liste des projets dispos pour être affecté en parent d'une note
     * @Template()
     */
    public function selectProjetsAction() {
        $em = $this->getDoctrine()->getManager();
        $projets = $em->getRepository('SrNotesBundle:Note')->findProjetsOuverts();

        $noteId = $this->getRequest()->get('noteId');
        if(!is_null($noteId)) $note = $em->getRepository('SrNotesBundle:Note')->findOneById($noteId);

        if(!is_null($note->getParent()) && $note->getParent()->getType() != Note::TYPE_PROJET) {
            $projets = array_merge(array($note->getParent()), $projets);
        }

        // Tri sur le breadcrumb
        usort($projets, create_function('$a, $b', 'return strcmp($a->getBreadCrumb(true), $b->getBreadCrumb(true));'));

        return array('projets' => $projets, 'note' => $note);
    }

    /**
     * @Route("/upload/{id}", name="upload", requirements={"id" = "\d+"})
     */
    public function uploadAction($id) {
        $this->get('punk_ave.file_uploader')->handleFileUpload(array(
            'folder' => $this->getUploadDir($id),
            'web_base_path' => $this->getRequest()->getBasePath().'/uploads',
        ));
        // Ce code n'est jamais atteint.
    }

    /**
     * @param $noteId
     * @return string
     */
    public function getUploadDir($noteId) {
        $r = $noteId; // Simple :)
        return $r;
    }
}
