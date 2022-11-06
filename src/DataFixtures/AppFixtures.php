<?php

  namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Recette;
  use Doctrine\Bundle\FixturesBundle\Fixture;
  use Doctrine\Persistence\ObjectManager;
  use Faker\Generator;
  use Faker\Factory;
use App\Entity\Ingredient;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

  class AppFixtures extends Fixture
  {
    private Generator $faker;
    
    /**
     * Password hasher
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher){
      $this->faker = Factory::create('fr_FR');
      $this->userPasswordHasher = $userPasswordHasher;
     }
   
    public function load(ObjectManager $manager): void
    {

      // Création de 10 utilisateurs de test avec Faker
      for ($i = 0; $i < 10; $i++) {
        $userUser = new User();
        $password = $this->faker->password(2,6);
        $userUser->setUsername($this->faker->userName(). '@'.$password);
        $userUser->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
        $userUser->setRoles(['ROLE_USER']);
        $manager->persist($userUser);
        $manager ->flush();
      }

      // Création d'un utilisateur admin
      $userUser = new User();
      $password = $this->faker->password(2,6);        
      $userUser->setUsername("admin");
      $userUser->setRoles(['ROLE_ADMIN']);
      $userUser->setPassword($this->userPasswordHasher->hashPassword($userUser,"password"));
      $manager->persist($userUser);
      $manager ->flush();

      // Création d'ingredients
      $ingredientList = [];
      for($i=0;$i<10; $i++){
        $ingredientList[$i] = new Ingredient();
        $ingredientList[$i] ->setName($this->faker->firstName());
        $ingredientList[$i] -> setQuantity($this->faker->numberBetween(1,10));
        $manager ->persist($ingredientList[$i]);
      }

      for($i=0; $i<10; $i++){
          $recette = new Recette();
          $recette->setRecetteName($this->faker->sentence(3));
          $recette->setStatus(true);
          $recette->addIngredient($ingredientList[$i]);
          $manager -> persist($recette);
      }
      $manager ->flush();
     } 
  }

  