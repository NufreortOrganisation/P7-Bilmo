<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Pagerfanta\Pagerfanta;

/**
 * @package App\Controller
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="api_users_collection_get", methods={"GET"})
     * @param usersRepository $usersRepository
     * @return JsonResponse
     */
    public function usersCollection(UsersRepository $usersRepository, SerializerInterface $serializer): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', null, 'Seul les CLIENTS peuvent consulter, ajouter, éditer ou supprimer des utilisateurs !');

        return new JsonResponse(
            $serializer->serialize($usersRepository->findAll(), "json"),
            JsonResponse::HTTP_OK,
            [],
            true
          );
    }

    /**
     * @Route("/{id}", name="api_user_get", methods={"GET"})
     * @param Users $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showUser(Users $user, SerializerInterface $serializer): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', null, 'Seul les CLIENTS peuvent consulter, ajouter, éditer ou supprimer des utilisateurs !');

        return new JsonResponse(
          $serializer->serialize($user, "json"),
          JsonResponse::HTTP_OK,
          [],
          true
        );
    }

    /**
     * @Route("/new", name="api_users_collection_post", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function newUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', null, 'Seul les CLIENTS peuvent consulter, ajouter, éditer ou supprimer des utilisateurs !');

        $user = $serializer->deserialize($request->getContent(), Users::class, 'json');

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(
          $serializer->serialize($user, "json"),
          JsonResponse::HTTP_CREATED,
          ["Location" => $urlGenerator->generate("api_user_get", ["id" => $user->getId()])],
          true
        );
    }

/**
     * @Route("/{id}", name="api_user_item_put", methods={"PUT"})
     * @param Users $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function editUser(
        Users $user,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', null, 'Seul les CLIENTS peuvent consulter, ajouter, éditer ou supprimer des utilisateurs !');

        $serializer->deserialize(
          $request->getContent(),
              Users::class,
              'json',
              [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
          );

        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name="api_user_item_delete", methods={"DELETE"})
     * @param Users $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteUser(
        Users $user,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_CLIENT', null, 'Seul les CLIENTS peuvent consulter, ajouter, éditer ou supprimer des utilisateurs !');
        
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
