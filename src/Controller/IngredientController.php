<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'controller_name' => 'IngredientController',
        ]);
    }

    #[Route('/api/ingredient', name: 'ingredient.create', methods: ['POST'])]
    /**
     * This function creates a new ingredient
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $jsonRecette = $request->getContent();
        $ingredient = $serializer->deserialize($jsonRecette, Ingredient::class, 'json');
        $errors = $validator->validate($ingredient);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $ingredient->setStatus(true);
        $em->persist($ingredient);
        $em->flush();
        return $this->json($ingredient, 201, [], ['groups' => 'ingredient:read']);
    }

    #[Route('/api/ingredient/{id}', name: 'ingredient.read', methods: ['GET'])]
    /**
     * This function reads an ingredient by is id
     *
     * @param Ingredient $ingredient
     * @param IngredientRepository $repository
     * @return JsonResponse
     */
    public function read(Ingredient $ingredient, IngredientRepository $repository): JsonResponse
    {
        $recette = $repository->find($ingredient);
        // return 404 json if not found
        if (!$recette || $recette->isStatus() == false) {
            return new JsonResponse(['message' => 'Recette not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($ingredient, 200, [], ['groups' => 'ingredient:read']);
    }

    #[Route('/api/ingredient/{id}', name: 'ingredient.update', methods: ['PUT'])]
    /**
     * This function updates an ingredient by is id
     *
     * @param Request $request
     * @param Ingredient $ingredient
     * @param IngredientRepository $repository
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function update(Ingredient $ingredient, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $jsonIngredient = $request->getContent();
        $serializer->deserialize($jsonIngredient, Ingredient::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $ingredient]);
        $errors = $validator->validate($ingredient);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $em->persist($ingredient);
        $em->flush();
        return $this->json($ingredient, 200, [], ['groups' => 'ingredient:read']);
    }

    #[Route('/api/ingredient/{id}', name: 'ingredient.delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour supprimer un ingrédient")]
    /**
     * This function deletes an ingredient by is id
     *
     * @param Ingredient $ingredient
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function delete(Ingredient $ingredient, EntityManagerInterface $em, IngredientRepository $repository): JsonResponse
    {
        $ingredient = $repository->find($ingredient);
        if (!$ingredient || $ingredient->isStatus() == false) {
            return new JsonResponse(['message' => 'Recette not found'], Response::HTTP_NOT_FOUND);
        } else {
            $ingredient->setStatus(false);
            $em->persist($ingredient);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
}
