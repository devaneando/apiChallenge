<?php

namespace App\Controller;

use App\Entity\User;
use App\Exceptions\UserExistsException;
use App\Form\UserType;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
        private readonly UserManager $userManager
    ) {

    }

    #[Route('/user/register', name: 'api_user_register', methods: Request::METHOD_POST)]
    public function createAction(Request $request): JsonResponse
    {
        $user = new User();
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->json(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $created = $this->userManager->createUser($user);

            return $this->json([
                'message' => 'User created successfully',
                'user' => $user
            ], JsonResponse::HTTP_CREATED, [], ['groups' => ['safe']]);

        } catch (UserExistsException|ValidationFailedException $ex) {
            return $this->json(['errors' => $ex->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}', name: 'api_user_view', methods: [Request::METHOD_GET], requirements: ['id' => '\d+'])]
    public function viewAction(Request $request, User $user): JsonResponse
    {
        return new JsonResponse(
            data: $this->serializer->serialize($user, 'json', ['groups' => ['safe']]),
            json: true
        );
    }

    #[Route('user/{id}/delete', name: 'api_user_delete', methods: [Request::METHOD_DELETE], requirements: ['id' => '\d+'])]
    public function deleteAction(Request $request, User $user): JsonResponse
    {
        $this->userManager->deleteUser($user);

        return $this->json([
            'message' => 'User deleted!',
        ], JsonResponse::HTTP_OK);
    }
}
