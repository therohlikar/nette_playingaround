<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_price_history')]
class ProductPriceHistory
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    protected Product $product;

    #[ORM\Column]
    protected float $newPrice;

    #[ORM\Column]
    protected float $oldPrice;

    #[ORM\Column]
    protected \DateTime $dateOfChange;

    public function __construct(Product $product, float $newPrice, float $oldPrice){
        $this->product = $product;
        $this->newPrice = $newPrice;
        $this->oldPrice = $oldPrice;
        $this->dateOfChange = new \DateTime('now', new \DateTimeZone('Europe/Prague'));
    }
}