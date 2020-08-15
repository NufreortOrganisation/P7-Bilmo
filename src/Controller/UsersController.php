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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Swagger\Annotations as SWG;
use Knp\Component\Pager\PaginatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;


/**
 * @package App\Controller
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
      $this->serializer = $serializer;
    }

    /**
     * @Get(
     *     path = "/",
     *     name = "api_users_collection_get",
     * )
     * @View
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of users")
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @return JsonResponse
     */
    public function usersCollection(UsersRepository $usersRepository,
    SerializerInterface $serializer,
    PaginatorInterface $paginator,
    Request $request,
    CacheInterface $cache): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $jsonData = $cache->get('jsonData', function(ItemInterface $item) use ($usersRepository, $paginator, $serializer, $request){
            $item->expiresAt(3);

            $users = $usersRepository->findAll();
            $users = $paginator->paginate($users, $request->get('page', 1), 10);

            return new JsonResponse(
                $serializer->serialize($users, "json"),
                JsonResponse::HTTP_OK,
                [],
                true
              );
        });

        return $jsonData;
    }

     /**
     * @Get(
     *     path = "/{id}",
     *     name = "api_user_get",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=200,
     *     description="Returns one user of the list")
     * @param Users $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showUser(Users $user, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse(
          $serializer->serialize($user, "json"),
          JsonResponse::HTTP_OK,
          [],
          true
        );
    }

     /**
     * @Post(
     *     path = "/new",
     *     name = "api_users_collection_post"
     * )
     * @View
     * @SWG\Response(
     *     response=201,
     *     description="Create a new user")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function newUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

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
     * @Put(
     *     path = "/{id}",
     *     name = "api_user_item_put",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=204,
     *     description="Edite one user of the list")
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

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
     * @Delete(
     *     path = "/{id}",
     *     name = "api_user_item_delete",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=204,
     *     description="Delete one user of the list")
     * @param Users $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteUser(
        Users $user,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
