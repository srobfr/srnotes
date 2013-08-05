<?php

namespace Sr\NotesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sr\NotesBundle\Entity\Note
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sr\NotesBundle\Entity\NoteRepository")
 */
class Note
{
    const TYPE_NOTE = 0;
    const TYPE_TACHE = 1;
    const TYPE_REFERENCE = 2;
    const TYPE_PROJET = 3;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime $dateCreation
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var datetime $dateFermeture
     *
     * @ORM\Column(name="dateFermeture", type="datetime", nullable=true)
     */
    private $dateFermeture;

    /**
     * @var datetime $dateLimite
     *
     * @ORM\Column(name="dateLimite", type="datetime", nullable=true)
     */
    private $dateLimite;

    /**
     * @var integer $dureeEstimee
     *
     * @ORM\Column(name="dureeEstimee", type="integer", nullable=true)
     */
    private $dureeEstimee;

    /**
     * @var integer $dureeReelle
     *
     * @ORM\Column(name="dureeReelle", type="integer", nullable=true)
     */
    private $dureeReelle;

    /**
     * @var smallint $pcAvancement
     *
     * @ORM\Column(name="pcAvancement", type="smallint")
     */
    private $pcAvancement;

    /**
     * @var string $titre
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var string $note
     *
     * @ORM\Column(name="note", type="string", length=10000, nullable=true)
     */
    private $note;

    /**
     * @var boolean $prochaine
     *
     * @ORM\Column(name="prochaine", type="boolean")
     */
    private $prochaine;

    /**
     * @var boolean $enAttente
     *
     * @ORM\Column(name="enAttente", type="boolean")
     */
    private $enAttente;

    /**
     * @var object $tags
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="notes" , cascade = {"persist"})
     */
    private $tags;

    /**
     * @var object $parent
     *
     * @ORM\ManyToOne(targetEntity="Note", inversedBy="enfants", cascade={"persist"})
     */
    private $parent;

    /**
     * @var object $enfants
     *
     * @ORM\OneToMany(targetEntity="Note", mappedBy="id", cascade={"persist"})
     */
    private $enfants;

    /**
     * @var object $precedentes
     *
     * @ORM\ManyToMany(targetEntity="Note", inversedBy="suivantes", cascade = {"persist"})
     * @ORM\JoinTable(name="notes_precedentes",
     *   joinColumns={
     * @ORM\JoinColumn(name="note_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     * @ORM\JoinColumn(name="precedente_id", referencedColumnName="id")
     *   }
     * )
     */
    private $precedentes;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * Breadcrumb des parents (cache)
     * @var
     */
    private $breadCrumb;


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
     * Set dateCreation
     *
     * @param datetime $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * Get dateCreation
     *
     * @return datetime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateLimite
     *
     * @param datetime $dateLimite
     */
    public function setDateLimite($dateLimite)
    {
        $this->dateLimite = $dateLimite;
    }

    /**
     * Get dateLimite
     *
     * @return datetime 
     */
    public function getDateLimite()
    {
        return $this->dateLimite;
    }

    /**
     * Set dureeEstimee
     *
     * @param integer $dureeEstimee
     */
    public function setDureeEstimee($dureeEstimee)
    {
        $this->dureeEstimee = $dureeEstimee;
    }

    /**
     * Get dureeEstimee
     *
     * @return integer 
     */
    public function getDureeEstimee()
    {
        return $this->dureeEstimee;
    }

    /**
     * Set dureeReelle
     *
     * @param integer $dureeReelle
     */
    public function setDureeReelle($dureeReelle)
    {
        $this->dureeReelle = $dureeReelle;
    }

    /**
     * Get dureeReelle
     *
     * @return integer 
     */
    public function getDureeReelle()
    {
        return $this->dureeReelle;
    }

    /**
     * Set pcAvancement
     *
     * @param smallint $pcAvancement
     */
    public function setPcAvancement($pcAvancement)
    {
        if($this->pcAvancement < 100 && $pcAvancement >= 100) $this->setDateFermeture(new \DateTime());
        $this->pcAvancement = $pcAvancement;
    }

    /**
     * Get pcAvancement
     *
     * @return smallint 
     */
    public function getPcAvancement()
    {
        return $this->pcAvancement;
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
     * Set note
     *
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set prochaine
     *
     * @param boolean $prochaine
     */
    public function setProchaine($prochaine)
    {
        $this->prochaine = $prochaine;
    }

    /**
     * Get prochaine
     *
     * @return boolean 
     */
    public function getProchaine()
    {
        return $this->prochaine;
    }

    /**
     * Set enAttente
     *
     * @param boolean $enAttente
     */
    public function setEnAttente($enAttente)
    {
        $this->enAttente = $enAttente;
    }

    /**
     * Get enAttente
     *
     * @return boolean 
     */
    public function getEnAttente()
    {
        return $this->enAttente;
    }

    /**
     * Set tags
     *
     * @param object $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get tags
     *
     * @return object 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set parent
     *
     * @param object $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return object 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param object $enfants
     */
    public function setEnfants($enfants) {
        $this->enfants = $enfants;
        return $this;
    }

    /**
     * @return object
     */
    public function getEnfants() {
        return $this->enfants;
    }

    /**
     * @param object $precedentes
     */
    public function setPrecedentes($precedentes) {
        $this->precedentes = $precedentes;
        return $this;
    }

    /**
     * @return object
     */
    public function getPrecedentes() {
        return $this->precedentes;
    }

    /**
     * Retourne le chemin vers cette note dans l'arborescence de notes
     * @return string
     */
    public function getBreadCrumb($withThis = false) {

        if(is_null($this->breadCrumb)) {
            $parents = $this->getParentsRecursifs();
            $r = array();
            foreach($parents as $p) $r[] = $p->getTitre();
            $this->breadCrumb = implode(' > ', $r);
        }

        if($withThis) $parents[] = $this;

        return $this->breadCrumb.($withThis?(''==$this->breadCrumb ? '':' > ').$this->getTitre():'');
    }

    /**
     * Retourne les tags de cette note sous forme d'array
     * @return array
     */
    public function getTagsAsStrings($color = false) {
        $r = array();
        foreach($this->getTags() as $t) {
            $s = $t->getTitre();

            if($color) $s = "<span style='background-color: #{$t->getCouleur()}'>$s</span>";
            $r[] = $s;
        }
        return $r;
    }

    public function getParentsRecursifs() {
        $maxRecursions = 10;
        $parent = $this;
        $r = array();

        while($maxRecursions-- >= 0 && !is_null($parent = $parent->getParent())) {
            $r[] = $parent;
        }

        $r = array_reverse($r);

        return $r;
    }

    /**
     * Retourne true si $this est un descendant de $note
     * @param $note
     * @return bool
     */
    public function isEnfantDe($note) {
        return (in_array($note, $this->getParentsRecursifs()));
    }

    public function getListLineClass() {
        $r = array();
        
        if(!is_null($this->getDateLimite())
            && $this->getDateLimite() <= \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime("now +1 days")))
            && $this->getPcAvancement() < 100) $r[] = 'bientot'; // Date limite proche

        if($this->getType() == 2) $r[] = 'reference'; // Référence
        elseif($this->getType() == 3) $r[] = 'projet'; // Projet

        if($this->getPcAvancement() == 100) $r[] = 'complete';
        if($this->getProchaine()) $r[] = 'prochaine'; // Prochaine
        if($this->getEnAttente()) $r[] = 'attente'; // En attente

        return implode(' ', $r);
    }

    /**
     * @param \Sr\NotesBundle\Entity\datetime $dateFermeture
     */
    public function setDateFermeture($dateFermeture) {
        $this->dateFermeture = $dateFermeture;
        return $this;
    }

    /**
     * @return \Sr\NotesBundle\Entity\datetime
     */
    public function getDateFermeture() {
        return $this->dateFermeture;
    }
}