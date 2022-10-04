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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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
    
    #[Route('/recette/getAll', name: 'cours.getAll')]
    /**
     * Return all recettes
     *
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
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
    
    #[Route('/recette/{id}', name: 'recette.get', methods: ['GET'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    /**
     * Undocumented function
     *
     * @param Recette $recette
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getOne(Recette $recette, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->find($recette);
        $jsonRecette = $serializer->serialize($recette, 'json');
        return New JsonResponse($jsonRecette,Response::HTTP_OK, ['accept'=>'json'],true);
    }
    
    #[Route('/recette/{id}', name: 'recette.delete', methods: ['DELETE'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    /**
     * This function delete the recette that is associated to id
     *
     * @param [type] $recette
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteRecette($recette, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($recette);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
