<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Recette;
use Symfony\Component\Serializer\Annotation\Groups;

class RecetteController extends AbstractController
{
    #[Route('/recette', name: 'app_recette')]
    public function index(): Response
    {
        return $this->render('recette/index.html.twig', [
            'controller_name' => 'RecetteController',
        ]);
    }
    
    #[Groups(["getAll"])]
    #[Route('/recette/getAll', name: 'cours.getAll')]
    public function getAllRecette(RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->findAll();
        $jsonRecette = $serializer->serialize($recette, 'json');
        return New JsonResponse($jsonRecette, Response::HTTP_OK, [],true);
    }

    
    /*
    #[Route('/recette/{id}', name: 'cours.get', methods: ['GET'])]
    public function getOne(int $id, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->find($id);
        $jsonRecette = $serializer->serialize($recette, 'json');
        return $recette ?
        New JsonResponse($jsonRecette,Response::HTTP_OK, [],true) :
        New JsonResponse(null, Response::HTTP_NOT_FOUND) ;
    }
     */
    
    #[Groups(["get"])]
    #[Route('/recette/{id}', name: 'cours.get', methods: ['GET'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    public function getOne(Recette $recette, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->find($recette);
        $jsonRecette = $serializer->serialize($recette, 'json');
        return New JsonResponse($jsonRecette,Response::HTTP_OK, ['accept'=>'json'],true);
    }
}
