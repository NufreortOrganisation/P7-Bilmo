<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $fake = Factory::create();

        for($p = 0; $p < 20; $p++) {
            $product = new Products();

            $product->setName($fake->name)
                ->setPrice($fake->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2000))
                ->setCategory($fake->colorName)
                ->setPicture($fake->imageUrl($width = 640, $height = 480));

            $manager->persist($product);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
