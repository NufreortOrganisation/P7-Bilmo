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

            $product->setName($fake->lastName)
                ->setPrice($fake->randomFloat(2, 0, 2000))
                ->setCategory($fake->colorName)
                ->setPicture($fake->imageUrl(640, 480));

            $manager->persist($product);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
