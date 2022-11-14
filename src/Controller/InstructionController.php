<?php

namespace App\Controller;

use App\Repository\InstructionRepository;
use App\Entity\Instruction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class InstructionController extends AbstractController
{
    #[Route('/instruction', name: 'app_instruction')]
    public function index(): Response
    {
        return $this->render('instruction/index.html.twig', [
            'controller_name' => 'InstructionController',
        ]);
    }

    #[Route('/api/instruction', name: 'instruction.create', methods: ['POST'])]
    /**
     * This function creates a new instruction
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $jsonInstruction = $request->getContent();
        $instruction = $serializer->deserialize($jsonInstruction, Instruction::class, 'json');
        $errors = $validator->validate($instruction);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $instruction->setStatus(true);
        $em->persist($instruction);
        $em->flush();
        return $this->json($instruction, 201, [], ['groups' => 'instruction:read']);
    }

    #[Route('/api/instruction/{id}', name: 'instruction.read', methods: ['GET'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour accéder à cette ressource")]
    /**
     * This function reads an instruction by is id
     *
     * @param Instruction $instruction
     * @param InstructionRepository $repository
     * @return JsonResponse
     */
    public function read(Instruction $instruction, InstructionRepository $repository): JsonResponse
    {
        $instruction = $repository->find($instruction);
        // return 404 json if not found
        if (!$instruction || $instruction->isStatus() == false) {
            return new JsonResponse(['message' => 'Recette not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($instruction, 200, [], ['groups' => 'instruction:read']);
    }


    #[Route('/api/instruction/{id}', name: 'instruction.update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour accéder à cette ressource")]
    /**
     * This function updates an instruction by is id
     *
     * @param Instruction $instruction
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function update(Instruction $instruction, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $jsonInstruction = $request->getContent();
        $serializer->deserialize($jsonInstruction, Instruction::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $instruction]);
        $errors = $validator->validate($instruction);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }
        $em->persist($instruction);
        $em->flush();
        return $this->json($instruction, 200, [], ['groups' => 'instruction:read']);
    }

    #[Route('/api/instruction/{id}', name: 'instruction.delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER',message:"Vous n'avez pas les droits pour accéder à cette ressource")]
    /**
     * This function deletes an instruction by is id
     *
     * @param Instruction $instruction
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function delete(Instruction $instruction, EntityManagerInterface $em): JsonResponse
    {
        $instruction->setStatus(false);
        $em->persist($instruction);
        $em->flush();
        return $this->json(['status' => 'Instruction deleted'], 200);
    }


}
