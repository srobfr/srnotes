<?php
namespace Sr\NotesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Sr\NotesBundle\Entity\Note;

class RecolorTagsCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
        ->setName('notes:recolorTags')
        ->setDescription('Réaffecte les couleurs des tags définis (au hasard)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $tags = $em->getRepository('SrNotesBundle:Tag')->findAll();

        foreach($tags as $tag) {
            $r = rand(160, 255);
            $v = rand(160, 255);
            $b = rand(160, 255);
            $hex = base_convert($r, 10, 16) . base_convert($v, 10, 16) . base_convert($b, 10, 16);
            $tag->setCouleur($hex);
        }

        $em->flush();
    }
}