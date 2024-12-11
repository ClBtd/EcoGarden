<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends AbstractController
{
    #[Route('conseil', name: 'conseil', methods: ['GET'])]
    #[Route('conseil/{mois}', name: 'api_conseil_mois', requirements: ['mois' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER', message:'Vous devez être connectés pour accéder à cette page.')]
    public function getMonthArticles(?int $mois = null, ArticleRepository $articleRepository, SerializerInterface $serializer): JsonResponse
    {
        if (!$mois) {
            $mois = (int) (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('n');
        }
    
        $monthArticles = $articleRepository->findMonthArticles($mois);
        $jsonMonthArticles = $serializer->serialize($monthArticles, 'json');

        return new JsonResponse($jsonMonthArticles, Response::HTTP_OK, [], true);
    }

    #[Route('/conseil', name: 'creationConseil')]
    #[IsGranted('ROLE_ADMIN', message:'Vous devez être administrateur.ice pour accéder à cette page.')]
    public function createArticle(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse 
    {

        $article = $serializer->deserialize($request->getContent(), Article::class, 'json');
        $errors = $validator->validate($article);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($article);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('/conseil/{id}', name:"modificationConseil", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message:'Vous devez être administrateur.ice pour accéder à cette page.')]
    public function updateArticle(Request $request, Article $updatedArticle, EntityManagerInterface $em): JsonResponse 
    {
        $content = $request->toArray();

        if (isset($content['content'])) {
            $updatedArticle->setContent($content['content']);
        }
        if (isset($content['months'])) {
            $updatedArticle->setMonths($content['months']);
        }
        
        $em->persist($updatedArticle);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }

    #[Route('/conseil/{id}', name: 'suppressionConseil', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message:'Vous devez être administrateur.ice pour accéder à cette page.')]
     public function deleteArticle(?Article $article, EntityManagerInterface $em): JsonResponse {
 
         if ($article) {
             $em->remove($article);
             $em->flush();
             return new JsonResponse(null, Response::HTTP_NO_CONTENT);
         }
         return new JsonResponse(null, Response::HTTP_NOT_FOUND);
     }


}
