<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods="POST")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $content = $request->getContent();

        /** @var User $user */
        $user = $this->serializerService->deserialize($content, User::class);

        $errors = $this->validationService->validate($user);

        if (count($errors) == 0) {
            $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse($this->serializerService->serialize($user, [AbstractNormalizer::IGNORED_ATTRIBUTES => User::IGNORED_ATTRIBUTES]), JsonResponse::HTTP_OK, [], true);
        }

        return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loginAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $content = json_decode($request->getContent(), true);

        if (!empty($content['username']) && !empty($content['password'])) {

            $username = $content['username'];
            $password = $content['password'];

            $userRepository = $this->entityManager->getRepository(User::class);

            /** @var User $user */
            $user = $userRepository->findOneBy(['username' => $username]);

            if ($user !== null) {
                $isPasswordValid = $encoder->isPasswordValid($user, $password);

                if ($isPasswordValid) {
                    $token = $this->generateToken();

                    $user->setApiToken($token);
                    $this->entityManager->flush();

                    $serializedUser = $this->serializerService->serialize($user, [AbstractNormalizer::IGNORED_ATTRIBUTES => User::IGNORED_ATTRIBUTES ]);

                    return new JsonResponse([
                        'status' => JsonResponse::HTTP_OK,
                        'token'  => $token,
                        'user'   => json_decode($serializedUser)
                    ]);
                }
            }
        }

        return new JsonResponse([
            'status' => JsonResponse::HTTP_BAD_REQUEST,
            'message' => 'Identifiants erronés'
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/logout", name="logout", methods="POST")
     */
    public function logoutAction() {
        /** @var User $user */
        $user = $this->getUser();

        $user->setApiToken(null);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['status' => JsonResponse::HTTP_OK, 'message' => 'Logout'], JsonResponse::HTTP_OK);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}