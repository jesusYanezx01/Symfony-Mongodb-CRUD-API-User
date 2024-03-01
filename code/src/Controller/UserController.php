<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserFormType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/user/create', name: 'app_crete_user', methods: 'POST')]
    public function createUser(Request $request, DocumentManager $documentManager, ValidatorInterface $validator): JsonResponse
    {
        //$data = json_decode($request->getContent(), true);

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        /*$user->setName($data['name']);
        $user->setLastName($data['lastName']);
        $user->setAge($data['age']); */

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


        //Error estatico al no detectar un error
        return $this->json(['error' => 'Datos del formulario no validos, pero no se encontraron errores de validacion manual.'], 400);




    }

    #[Route('/users', name: 'app_get_users', methods: 'GET')]
    public function getUsers(DocumentManager $documentManager): JsonResponse
    {
        $userRepository = $documentManager->getRepository(User::class);
        $users = $userRepository->findAll();

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
    }
}
