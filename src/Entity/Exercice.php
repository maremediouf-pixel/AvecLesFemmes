<?php


namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Enum\StatutExercice;
use App\Repository\ExerciceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciceRepository::class)]
class Exercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $dateDebut;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $dateFin;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $dateLimitePrets;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $dateRetribution;

    #[ORM\Column(enumType: StatutExercice::class)]
    private StatutExercice $statut = StatutExercice::EN_COURS;

    #[ORM\ManyToOne(inversedBy: 'exercices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Groupement $groupement = null;
        /**
     * @var Collection<int, Campagne>
     */
    #[ORM\OneToMany(targetEntity: Campagne::class, mappedBy: 'exercice')]
    private Collection $campagnes;

    public function __construct(
        \DateTime $dateDebut,
        \DateTime $dateFin,
        \DateTime $dateLimitePrets,
        \DateTime $dateRetribution,
    ) {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->dateLimitePrets = $dateLimitePrets;
        $this->dateRetribution = $dateRetribution;
        $this->campagnes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateDebut(): \DateTime { return $this->dateDebut; }
    public function getDateFin(): \DateTime { return $this->dateFin; }
    public function getDateLimitePrets(): \DateTime { return $this->dateLimitePrets; }
    public function getDateRetribution(): \DateTime { return $this->dateRetribution; }

    public function getStatut(): StatutExercice { return $this->statut; }
    public function setStatut(StatutExercice $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getGroupement(): ?Groupement { return $this->groupement; }
    public function setGroupement(?Groupement $groupement): static
    {
        $this->groupement = $groupement;
        return $this;
    }

        /** @return Collection<int, Campagne> */
    public function getCampagnes(): Collection { return $this->campagnes; }

    public function addCampagne(Campagne $campagne): static
    {
        if (!$this->campagnes->contains($campagne)) {
            $this->campagnes->add($campagne);
            $campagne->setExercice($this);
        }
        return $this;
    }

    public function removeCampagne(Campagne $campagne): static
    {
        if ($this->campagnes->removeElement($campagne) && $campagne->getExercice() === $this) {
            $campagne->setExercice(null);
        }
        return $this;
    }
}