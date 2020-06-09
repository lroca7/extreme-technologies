<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ComplaintTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"ctype:read"}},
 *     denormalizationContext={"groups"={"ctype:write"}},
 * )
 * @ORM\Entity(repositoryClass=ComplaintTypeRepository::class)
 */
class ComplaintType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"ctype:read"})
     */
    private $id;

    /**     
     * @ORM\Column(type="string", length=255)
     * @Groups({"ctype:read", "ctype:write"}) 
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
