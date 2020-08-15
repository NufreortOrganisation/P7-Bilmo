<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use FOS\RestBundle\Request\ParamFetcherInterface;
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
 * @Route("/products")
 */
class ProductsController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
      $this->serializer = $serializer;
    }

    /**
     * @Get(
     *     path = "/",
     *     name = "api_products_collection_get",
     * )
     * @View
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of products")
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @return JsonResponse
     */
    public function productsCollection(ProductsRepository $productsRepository,
    SerializerInterface $serializer,
    PaginatorInterface $paginator,
    Request $request,
    CacheInterface $cache): JsonResponse
    {
        $cache = new FilesystemAdapter();

        $jsonData = $cache->get('productsList', function(ItemInterface $item) use ($productsRepository, $paginator, $serializer, $request){
            $item->expiresAfter(10);

            $products = $productsRepository->findAll();
            $products = $paginator->paginate($products, $request->get('page', 1), 10);

            return new JsonResponse(
                $serializer->serialize($products, "json"),
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
     *     name = "api_product_get",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=200,
     *     description="Returns one product of the list")
     * @param Products $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showProduct(Products $product, SerializerInterface $serializer): JsonResponse
    {
            return new JsonResponse(
              $serializer->serialize($product, "json"),
              JsonResponse::HTTP_OK,
              [],
              true
            );
    }

     /**
     * @Post(
     *     path = "/new",
     *     name = "api_products_collection_post"
     * )
     * @View
     * @SWG\Response(
     *     response=201,
     *     description="Create a new product")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function newProduct(Request $request,
    SerializerInterface $serializer,
    EntityManagerInterface $entityManager,
    UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null,
        'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $product = $serializer->deserialize($request->getContent(), Products::class, 'json');

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(
          $serializer->serialize($product, "json"),
          JsonResponse::HTTP_CREATED,
          ["Location" => $urlGenerator->generate("api_product_get", ["id" => $product->getId()])],
          true
        );
    }

     /**
     * @Put(
     *     path = "/{id}",
     *     name = "api_product_item_put",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=204,
     *     description="Edite one product of the list")
     * @param Products $product
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function editProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null,
        'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $serializer->deserialize(
          $request->getContent(),
              Products::class,
              'json'
          );

        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

     /**
     * @Delete(
     *     path = "/{id}",
     *     name = "api_product_item_delete",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     * @SWG\Response(
     *     response=204,
     *     description="Delete one product of the list")
     * @param Products $product
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteProduct(
        Products $product,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null,
        'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
