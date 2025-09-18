<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(name="idx_name", columns={"name"})})
 * @ORM\Entity
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    public $name;

    /**
     * @var int
     *
     * @ORM\Column(name="cost", type="integer", nullable=false, options={"unsigned"=true})
     */
    public $cost;

    /**
     * @var int
     *
     * @ORM\Column(name="markup", type="integer", nullable=false, options={"unsigned"=true})
     */
    public $markup;

    /**
     * @var Discount[]
     *
     * @ORM\OneToMany(targetEntity="Discount", mappedBy="product")
     */
    public $discounts;
}
