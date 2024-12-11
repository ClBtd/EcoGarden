<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, ValidatorInterface $validator): JsonResponse 
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $user = $user->setRoles(['ROLE_USER']);
        $user = $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword())); 
        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('/user/{id}', name:"updateUser", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message:'Vous devez être administrateur.ice pour accéder à cette page.')]
    public function updateUser(Request $request, User $updatedUser, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): JsonResponse 
    {
        $content = $request->toArray();

        if (isset($content['username'])) {
            $updatedUser->setUsername($content['username']);
        }
        if (isset($content['months'])) {
            $updatedUser->setPassword($userPasswordHasher->hashPassword($updatedUser, $content['password']));
        }
        if (isset($content['roles'])) {
            $updatedUser->setRoles($content['roles']);
        }
        if (isset($content['zipcode'])) {
            $updatedUser->setZipcode($content['zipcode']);
        }
        
        $em->persist($updatedUser);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }

    #[Route('/user/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message:'Vous devez être administrateur.ice pour accéder à cette page.')]
     public function deleteArticle(?User $user, EntityManagerInterface $em): JsonResponse {
 
         if ($user) {
             $em->remove($user);
             $em->flush();
             return new JsonResponse(null, Response::HTTP_NO_CONTENT);
         }
         return new JsonResponse(null, Response::HTTP_NOT_FOUND);
     }
}
