<?php

namespace App\Controller;

use App\Entity\Recette;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecetteController extends AbstractController
{
    #[Route('/api/recette', name: 'app_recette')]
    public function index(): Response
    {
        return $this->render('recette/index.html.twig', [
            'controller_name' => 'RecetteController',
        ]);
    }
    
    /**
     * This route is used to list all the recettes in the database
     *
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/recette/getAll', name: 'cours.getAll')]
    #[Groups(['recette:read'])]
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
    
    #[Route('api/recette/{id}', name: 'recette.get', methods: ['GET'])]
    #[Groups(['recette:read'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    /**
     * this function return one recette by id
     *
     * @param Recette $recette
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[IsGranted('ROLE_ADMIN',message:"Pas de droit")]
    public function getOne(Recette $recette, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->find($recette);
        $jsonRecette = $serializer->serialize($recette, 'json');
        return New JsonResponse($jsonRecette,Response::HTTP_OK, ['accept'=>'json'],true);
    }
    
    /**
     * @Route("/recette", name="recette.create", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/recette/{id}', name: 'recette.delete', methods: ['DELETE'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    #[Groups(['recette:read'])]
    /**
     * this function delete a recette by id 
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

    /**
     * Create a new recette in the database and return it in json format with the location of the new recette in the header of the response
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlgenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/recette', name: 'recette.create', methods: ['POST'])]
    #[IsGranted('ADMIN',message:"Pas de droit")]
    public function createRecette(Request $request, EntityManagerInterface $manager,SerializerInterface $serializer, UrlGeneratorInterface $urlgenerator, ValidatorInterface $validator):JsonResponse
    {
        $event = $serializer->deserialize(
            $request -> getContent(),
            Recette::class,
            'json'
        );
        $recette = New Recette();
        $recette->setRecetteName("");
        $errors =$validator->validate($recette);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors,'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($recette);
        $manager->flush();

        $content=$request->toArray();

        $location = $urlgenerator->generate('recette.getOne', ['id'=>$recette->getId()],UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse(null, Response::HTTP_CREATED,["Location"=>$location],true);
    }
   
    #[Route('/api/recette/{id)', name: 'recette.update', methods: ['PUT'])]
    #[ParamConverter("recette",options:["id"=> "id"])]
    /**
     * This function update the recette that is associated to id
     *
     * @param [type] $recette
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateRecette(Recette $recette, Request $request, EntityManagerInterface $manager,SerializerInterface $serializer, UrlGeneratorInterface $urlgenerator):JsonResponse
    {
        $updateRecette = $serializer->deserialize(
            $request -> getContent(),
            Recette::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $recette]
        );
        $recette = New Recette();
        $recette->setRecetteName("");
        $manager->persist($recette);
        $manager->flush();

        $content=$request->toArray();
        
        $location = $urlgenerator->generate('recette.getOne', ['id'=>$recette->getId()],UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse(null, Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
