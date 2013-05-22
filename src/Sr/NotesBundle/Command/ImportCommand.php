<?php
namespace Sr\NotesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Sr\NotesBundle\Entity\Note;

class ImportCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
        ->setName('notes:import')
        ->setDescription('Importe des notes depuis une bd mysql GTD-PHP');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        // Login vers la base Mysql de GTD-PHP
        $infosServeurGtdPhp = array(
            'host' => 'localhost',
            'user' => 'user',
            'db' => 'gtd',
            'password' => 'password'
        );

        $db = mysql_connect($infosServeurGtdPhp['host'], $infosServeurGtdPhp['user'], $infosServeurGtdPhp['password']);
        mysql_select_db($infosServeurGtdPhp['db'], $db);

        $res = mysql_query("SELECT * FROM gtdphp_items
            INNER JOIN gtdphp_itemstatus ON gtdphp_itemstatus.itemId = gtdphp_items.itemId
            INNER JOIN gtdphp_itemattributes ON gtdphp_itemattributes.itemId = gtdphp_items.itemId
            LEFT OUTER JOIN gtdphp_nextactions ON gtdphp_nextactions.nextaction = gtdphp_items.itemId
            ", $db);

        $data = array();
        while($l = mysql_fetch_assoc($res)) {
            $data[] = $l;
        }

        mysql_close($db);

        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        copy("$rootDir/data/database.sqlite3", "$rootDir/data/database.sqlite3_backup".date('Y-m-d_H-i-s'));

        $em = $this->getContainer()->get('doctrine')->getManager();

        //var_dump($data); return;

        // On crée un projet dans Inbox
        $parent = new Note();
        $parent->setParent($em->getRepository('SrNotesBundle:Note')->findOneById(1));
        $parent->setTitre("Import GTD-PHP");
        $parent->setType(Note::TYPE_PROJET); // Type par défaut
        $parent->setDateCreation(new \DateTime());
        $parent->setPcAvancement(0);
        $parent->setProchaine(false);
        $parent->setEnAttente(false);
        $em->persist($parent);
        $em->flush();

        foreach($data as $d) {
            $n = new Note();
            $n->setParent($parent);
            $n->setTitre(utf8_encode($d['title']));
            $n->setNote(utf8_encode($d['description']));
            $n->setType(Note::TYPE_NOTE); // Type par défaut
            $n->setDateCreation(\DateTime::createFromFormat('Y-m-d', $d['dateCreated']));
            if(!is_null($d['deadline'])) $n->setDateLimite(\DateTime::createFromFormat('Y-m-d', $d['deadline']));
            $n->setPcAvancement(is_null($d['dateCompleted'])?0:100);
            $n->setProchaine(!is_null($d['nextaction']));
            $n->setEnAttente(false);
            $em->persist($n);
        }

        $em->flush();
    }
}