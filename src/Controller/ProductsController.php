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
use Symfony\Component\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Pagerfanta\Pagerfanta;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;
use Nelmio\ApiDocBundle\Annotation as Doc;

/**
 * @package App\Controller
 * @Route("/products")
 */
class ProductsController extends AbstractController
{
    /**
     * @Route("/", name="api_products_collection_get", methods={"GET"})
     * @param productsRepository $productsRepository
     * @return JsonResponse
     *
     * @Doc\ApiDoc(
     *     resource=true,
     *     description="Obtenir la liste de tous les produits."
     * )
     */
    public function productsCollection(ProductsRepository $productsRepository,
    SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($productsRepository->findAll(), "json"),
            JsonResponse::HTTP_OK,
            [],
            true
          );

      /*  return new JsonResponse(
            $serializer->serialize($productsRepository->findAll(), "json"),
            JsonResponse::HTTP_OK,
            [],
            true
          ); */
    }

    /**
     * @Route("/{id}", name="api_product_get", methods={"GET"})
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
     * @Route("/new", name="api_products_collection_post", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function newProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

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
     * @Route("/{id}", name="api_product_item_put", methods={"PUT"})
     * @param Products $product
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function editProduct(
        Products $product,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $serializer->deserialize(
          $request->getContent(),
              Products::class,
              'json',
              [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
          );

        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name="api_product_item_delete", methods={"DELETE"})
     * @param Products $product
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteProduct(
        Products $product,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seul les admins peuvent ajouter, éditer ou supprimer des produits !');

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
