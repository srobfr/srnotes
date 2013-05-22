<?php

namespace Sr\NotesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sr\NotesBundle\Entity\Tag
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sr\NotesBundle\Entity\TagRepository")
 */
class Tag
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $titre
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var string $couleur
     *
     * @ORM\Column(name="couleur", type="string", length=6)
     */
    private $couleur;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set couleur
     *
     * @param string $couleur
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;
    }

    /**
     * Get couleur
     *
     * @return string 
     */
    public function getCouleur()
    {
        return $this->couleur;
    }
}