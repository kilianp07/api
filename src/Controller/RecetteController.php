<?php

namespace App\Controller;

use App\Entity\Recette;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
//use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RecetteController extends AbstractController
{
    #[Route('/api/recette', name: 'app_recette')]
    public function index(): Response
    {
        return $this->render('recette/index.html.twig', [
            'controller_name' => 'RecetteController',
        ]);
    }
    
   
    #[Route('/api/recette/getAll', name: 'recette.getAll')]
    #[Groups(['recette:read'])]
    /**
     * Return all recettes contained in the database
     *
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour accéder à cette ressource')]
    public function getAllRecette(Request $request, RecetteRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $limit = $limit > 20 ? 20 : $limit;
        $limit = $limit < 1 ? 1 : $limit;

        $recette = $repository->findWithPagination($page,$limit);
         
        //$recette = $repository->findAll();
        $jsonRecette = $serializer->serialize($recette, 'json');
        $jsonRecette = $cache->get("getAllRecette", function (ItemInterface $item) use ($repository, $serializer) {
            $item->tag("recetteCache");
            echo "Mise en cache";
            $context = SerializationContext::create()->setGroups(['recette:read']);
            return $serializer->serialize($jsonRecette, 'json', $context);
        });
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
    public function getOne(Recette $recette, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $recette = $repository->find($recette);
       
        if($recette->status == true ){
            $jsonRecette = $serializer->serialize($recette, 'json');
            return New JsonResponse($jsonRecette,Response::HTTP_OK, ['accept'=>'json'],true);
        }
        else{
            return New JsonResponse(null, Response::HTTP_NOT_FOUND) ;
        }  
    }
    


    /**
     * This function returns a recette by an ingredient name
     * @param Request $request
     * @param Recette $recette
     * @param RecetteRepository $repository
     * @param SerializerInterface $serializer
     * @param IngredientRepository $ingredientRepo
     * @return JsonResponse
     */
    #[Route('/api/recette/getByIngredient/{name}', name: 'recette.getByIngredient', methods: ['GET'])]
    #[Groups(['recette:read'])]
    public function getByIngredient(Request $request, RecetteRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $name = $request->get('name');
        $recette = New Recette();
        $recette = $repository->getRecetteByIngredient($name);
        $jsonRecette = $serializer->serialize($recette, 'json');
        return New JsonResponse($jsonRecette,Response::HTTP_OK, [],true);
    }
 

    #[Route('/api/recette/{id}', name: 'recette.delete', methods: ['DELETE'])]
    #[Groups(['recette:read'])]
    /**
     * This function delete one recette by id 
     *
     * @param Recette $recette
     * @param EntityManagerInterface $entityManager
     * @param RecetteRepository $repository
     * @return JsonResponse
     */

    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour supprimer une recette")]
    public function deleteRecette(Recette $recette, EntityManagerInterface $entityManager, RecetteRepository $repository): JsonResponse
    {
        //Find the recette by id
        $recette = $repository->find($recette);

        //If the recette doesn't exist return a 404 error
        if ($repository === null) {
            return new JsonResponse(['message' => 'Recette not found'], Response::HTTP_NOT_FOUND);
        }
        // Else delete the recette and return a 204 status code
        else{
            //Remove the recette
            $entityManager->remove($recette);
            //Save the change in the database
            $entityManager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
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
    #[Route('/api/recette/createRecette', name: 'recette.create', methods: ['POST'])]
    public function createRecette(Request $request, EntityManagerInterface $manager,SerializerInterface $serializer, UrlGeneratorInterface $urlgenerator, ValidatorInterface $validator):JsonResponse
    {

        $recette = New Recette();

        //get the json data from the request and deserialize it into a recette object
        $recette = $serializer->deserialize(
            $request -> getContent(),
            Recette::class,
            'json'
        );
        
        // validate the recette
        $errors = $validator->validate($recette);

        // if there are errors, return them in json format with a 400 status code (bad request)
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors,'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $recette->setStatus(true);
        // if there are no errors, save the recette in the database
        $manager->persist($recette);
        $manager->flush();

        // return the recette in json format with a 201 status code (created) and the location of the new recette in the header of the response
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
        //get the json data from the request and deserialize it into a recette object and update the recette
        $updateRecette = $serializer->deserialize(
            $request -> getContent(),
            Recette::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $recette]
        );
        $content = $request->toArray();

        // Populate the object with fresh data if it exists either get the old data
        $updateRecette->setRecetteName($updateRecette->getName() ?? $recette->getRecetteName());
        // save the recette in the database
        $manager->persist($updateRecette);
        $manager->flush();
        $content=$request->toArray();
        
        // return the recette in json format with a 201 status code (created) and the location of the new recette in the header of the response
        $location = $urlgenerator->generate('recette.getOne', ['id'=>$recette->getId()],UrlGeneratorInterface::ABSOLUTE_PATH);
        return new JsonResponse(null, Response::HTTP_CREATED,["Location"=>$location],true);
    }

}
