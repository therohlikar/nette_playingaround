<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;

#[ORM\Table(name: 'product')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\Column]
    protected string $name;

    #[ORM\Column]
    protected int $stockCount = 0;

    #[ORM\Column]
    protected float $price;

    #[ORM\Column]
    protected bool $available = true;

    public function __construct(string $name, int $stockCount, float $price){
        $this->name = $name;
        $this->stockCount = $stockCount;
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stockCount' => $this->stockCount,
            'price' => $this->price,
            'available' => $this->available,
        ];
    }
}