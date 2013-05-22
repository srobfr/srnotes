<?php
namespace Sr\NotesBundle\Services;

use \Sr\NotesBundle\Entity\Note;

class Chrono {

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    private $path;

    /**
     * @param $container
     */
    public function __construct($container) {
        $this->container = $container;
        $this->path = $container->get('kernel')->getRootDir()."/data/chrono.ser";
    }

    /**
     * Démarre le chrono sur une note (arrête la précédente si besoin)
     * @param \Sr\NotesBundle\Entity\Note $note
     */
    public function start(Note $note) {
        if(file_exists($this->path)) {
            // Le chrono tourne déjà.
            $this->stop();
        }

        file_put_contents($this->path, serialize(array(
            'id' => $note->getId(),
            'time' => time(),
        )));
    }

    /**
     * Arrête le chrono & enregistre dans la note courante
     */
    public function stop() {
        $note = $this->getNote();
        if($note === null) return null;
        $timer = $this->getTimer();
        $note->setDureeReelle($timer);
        $em = $this->container->get('doctrine')->getManager();
        $em->flush();
        unlink($this->path);
    }

    /**
     * Retourne le nombre de secondes au compteur
     */
    public function getTimer() {
        $infos = $this->getInfos();
        if($infos === null) return null;
        return time() - $infos['time'] + $this->getNote()->getDureeReelle();
    }

    /**
     * Retourne la dernière note
     */
    public function getNote() {
        $infos = $this->getInfos();
        if($infos === null) return null;
        $em = $this->container->get('doctrine')->getManager();
        $note = $em->getRepository('SrNotesBundle:Note')->findOneById($infos['id']);
        return $note;
    }

    /**
     * @return mixed|null
     */
    private function getInfos() {
        if(!file_exists($this->path)) return null;
        return unserialize(file_get_contents($this->path));
    }
}
