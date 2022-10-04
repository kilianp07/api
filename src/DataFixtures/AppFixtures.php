<?php

  namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recette;
  use Doctrine\Bundle\FixturesBundle\Fixture;
  use Doctrine\Persistence\ObjectManager;
  use Faker\Generator;
  use Faker\Factory;
 
  class AppFixtures extends Fixture
  {
    private Generator $faker;
    
    public function __construct(){
      $this->faker = Factory::create('fr_FR');
     }
   
    public function load(ObjectManager $manager): void
    {
      for($i=0;$i<3; $i++){
        $ingredient = new Ingredient();
        $ingredient ->setName($this->faker->firstName());
        $manager->persist($ingredient);
      }
      for($i=0; $i<10; $i++){
          $recette = new Recette();
          $recette->setRecetteName($this->faker->sentence(3));
          $manager ->flush();
          $manager -> persist($recette);
      }
     }
  }

  