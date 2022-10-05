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
    #[Route('/', name: 'app_picture')]
    public function index(): Response
    {
        return $this->render('picture/index.html.twig', [
            'controller_name' => 'PictureController',
        ]);
    }


    #[Route('/api/picture', name: 'picture.create', methods:['POST'])]
    #[ParamConverter("picture",options:["id"=> "idPicture"])]

    public function createPicture(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $picture = new Picture();
        $file = $request->files->get('file');
        $picture->setFile($file)
        ->setMimeType($file->getClientMimeType())
        ->setRealName($file->getClientOriginalName())
        ->setPublicPath('/asset/pictures')
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
    public function getPicture(Picture $picture, PictureRepository $repository, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {

        $RLlocation = $picture->getPublicPath().DIRECTORY_SEPARATOR.$picture->getRealPath();
        $location = $urlGenerator->generate('app_picture',[], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = $location.str_replace('','', $RLlocation);
        return new JsonResponse($serializer->serialize($picture, 'json'), JsonResponse::HTTP_OK, ['Location' => $location], true);
    }
}
