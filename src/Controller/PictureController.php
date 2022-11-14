<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PictureController extends AbstractController
{
    #[Route('/api/picture', name: 'picture.create', methods:['POST'])]
    #[ParamConverter("picture",options:["id"=> "idPicture"])]
    /**
     * Create a picture
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function createPicture(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $picture = new Picture();
        $file = $request->files->get('file');
        $picture->setFile($file)
        ->setMimeType($file->getClientMimeType())
        ->setRealName($file->getClientOriginalName())
        ->setPublicPath('asset/pictures')
        ->setStatus(true)
        ->setUploadDate(new \DateTime());

        $manager->persist($picture);
        $manager->flush();

        $jsonPicture = $serializer->serialize($picture, 'json');
        $location = $urlGenerator->generate('picture.get',['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonPicture, Response::HTTP_CREATED, ['Location' => $location], true);
    }


    #[Route('/api/picture/{idPicture}', name: 'picture.get', methods: ['GET'])]
    #[Groups(['Pictures:read'])]
    #[ParamConverter("picture",options:["id"=> "idPicture"])]
    /**
     * Function to get one picture by id, return a json response with the picture added in the body of the response
     *
     * @param Picture $picture
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    public function getPicture(Picture $picture, SerializerInterface $serializer, Request $request): JsonResponse
    {

        $RLlocation = $picture->getPublicPath().'/'.$picture->getRealPath();
        $location = $request ->getUriForPath('/');
        $location = $location.str_replace('','', $RLlocation);
        $picture->setPublicPath($location);
        if (!$picture || $picture->getStatus() == false) {
            return new JsonResponse(['message' => 'Picture not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($serializer->serialize($picture, 'json'), JsonResponse::HTTP_OK, ['Location' => $location], true);
    }

    #[Route('/api/picture/{idPicture}', name: 'picture.delete', methods: ['DELETE'])]
    #[ParamConverter("picture",options:["id"=> "idPicture"])]
    /**
     * Function to delete a picture by id
     *
     * @param Picture $picture
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function deletePicture(Picture $picture, EntityManagerInterface $manager): JsonResponse
    {
        if (!$picture || $picture->getStatus() == false) {
            return new JsonResponse(['message' => 'Picture not found'], Response::HTTP_NOT_FOUND);
        }
        $picture->setStatus(false);
        $manager->persist($picture);
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/picture', name: 'picture.update', methods: ['PUT'])]
    /**
     * This function exists to respect the REST architecture, but it is not used in the project
     *
     * @return JsonResponse
     */
    public function updatePicture(): JsonResponse
    {
        return new JsonResponse(['message' => 'Cannot update a picture'], Response::HTTP_NOT_FOUND);
    }
}
