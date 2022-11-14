<?php

  namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Recette;
  use Doctrine\Bundle\FixturesBundle\Fixture;
  use Doctrine\Persistence\ObjectManager;
  use Faker\Generator;
  use Faker\Factory;
use App\Entity\Ingredient;
use App\Entity\Instruction;
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
        $userUser->setStatus(true);
        $manager->persist($userUser);
        $manager ->flush();
      }

      // Création d'un utilisateur admin
      $userUser = new User();
      $password = $this->faker->password(2,6);
      $userUser->setUsername("admin");
      $userUser->setRoles(['ROLE_ADMIN']);
      $userUser->setStatus(true);
      $userUser->setPassword($this->userPasswordHasher->hashPassword($userUser,"password"));
      $manager->persist($userUser);
      $manager ->flush();

      /*
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
      */


      $json = file_get_contents('src/DataFixtures/data.json');
      $data = json_decode($json, true);

      // For each recipe contained in the json
      foreach($data as $recipe){
        // Create a new recipe
        $recette = new Recette();
        // Set the name of the recipe
        $recette->setRecetteName($recipe['name']);
        // Set the status of the recipe
        $recette->setStatus(true);
        // For each ingredient of the recipe
        foreach($recipe['ingredients'] as $ingredients){
          // Create a new ingredient
          $ingredient = new Ingredient();
          // Set the name of the ingredient
          $ingredient->setName($ingredients);
          // Set the quantity of the ingredient
          $ingredient->setQuantity($this->faker->numberBetween(1,10));
          $ingredient->setStatus(true);
          // Add the ingredient to the recipe
          $recette->addIngredient($ingredient);
          // Persist the ingredient
          $manager->persist($ingredient);
        }
        $instruction = New Instruction();
        $instruction->setInstructionList(explode(".", $recipe['instructions']));
        $instruction->setStatus(true);
        $manager->persist($instruction);

        // Link the instruction and ingredient to the recipe
        $recette->setInstructions($instruction);
        $recette->setStatus(true);

        // Persist the recipe
        $manager->persist($recette);
      }
      $manager ->flush();
     }
  }
