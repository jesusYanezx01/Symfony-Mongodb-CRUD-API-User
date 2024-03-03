<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserFormType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/user/create', name: 'app_crete_user', methods: 'POST')]
    public function createUser(Request $request, DocumentManager $documentManager, ValidatorInterface $validator): JsonResponse
    {

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            try {
                $documentManager->persist($user);
                $documentManager->flush();

                return $this->json(['message' => 'Usuario creado correctamente'], 201);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Error al crear el usuario'], 400);
            }

        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messageError= [];
            foreach ($errors as $error) {
                $messageError[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json(['error' => 'Datos del formulario no validos', 'validation_errors' => $messageError], 400);
        }


        return $this->json(['error' => 'Datos del formulario no validos, pero no se encontraron errores de validacion manual.'], 400);




    }

    #[Route('/users', name: 'app_get_users', methods: 'GET')]
    public function getUsers(DocumentManager $documentManager,SerializerInterface $serializer): Response
    {
        $userRepository = $documentManager->getRepository(User::class);
        $users = $userRepository->findAll();

        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new Response($jsonUsers, Response::HTTP_OK, ['Content-Type' => 'application/json']);

/*
        $usersArray = [];
        foreach ($users as $user) {
            $usersArray[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'lastName' => $user->getLastName(),
                'age' => $user->getAge(),
            ];
        }

        return $this->json($usersArray);
        */
    }


    #[Route('/user/delete/{id}', name: 'app_delete_user', methods: 'DELETE')]
    public function deleteUser($id, DocumentManager $documentManager): JsonResponse
    {
        $user = $documentManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        try {
            $documentManager->remove($user);
            $documentManager->flush();

            return $this->json(['message' => 'Usuario eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al eliminar el usuario'], 400);
        }
    }

    #[Route('/user/edit/{id}', name: 'app_edit_user', methods: 'PATCH')]
    public function updateUser(Request $request, ValidatorInterface $validator, DocumentManager $documentManager, $id): JsonResponse
    {
        $user = $documentManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $documentManager->flush();

                return $this->json(['message' => 'Usuario actualizado correctamente'], 200);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Error al actualizar el usuario'], 400);
            }
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messageError= [];
            foreach ($errors as $error) {
                $messageError[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json(['error' => 'Datos del formulario no válidos', 'validation_errors' => $messageError], 400);
        }

        return $this->json(['error' => 'Datos del formulario no válidos, pero no se encontraron errores de validación manual.'], 400);
    }


}
