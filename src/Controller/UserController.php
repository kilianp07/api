<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/api/user', name: 'user.create', methods: ['POST'])]
    /**
     * This function creates a new user
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return JsonResponse
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $jsonUser = $request->getContent();
        $user = $serializer->deserialize($jsonUser, User::class, 'json');
        $errors = $validator->validate($user);
        $user->setStatus(true);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();
        return $this->json($user, 201, [], ['groups' => 'user:read']);
    }

    #[Route('/api/user/{id}', name: 'user.read', methods: ['GET'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour accéder à cette ressource")]
    /**
     * This function reads a user by is id
     *
     * @param User $user
     * @param UserRepository $repository
     * @return JsonResponse
     */
    public function read(User $user, UserRepository $repository): JsonResponse
    {
        $user = $repository->find($user);
        // return 404 json if not found
        if (!$user || $user->isStatus() == false) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/api/user/{id}', name: 'user.update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour accéder à cette ressource")]
   /**
    * This function updates a user by is id
    *
    * @param User $user
    * @param Request $request
    * @param SerializerInterface $serializer
    * @param EntityManagerInterface $em
    * @param ValidatorInterface $validator
    * @param UserPasswordHasherInterface $userPasswordHasher
    * @return JsonResponse
    */
    public function update(User $user, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator,UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $jsonUser = $request->getContent();
        $serializer->deserialize($jsonUser, User::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $user]);
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }
}
