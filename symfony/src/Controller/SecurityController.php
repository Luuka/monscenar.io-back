<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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

                    $serializedUser = $this->serializerService->serialize($user, [AbstractNormalizer::IGNORED_ATTRIBUTES => User::IGNORED_ATTRIBUTES]);

                    return new JsonResponse([
                        'status' => JsonResponse::HTTP_OK,
                        'token' => $token,
                        'user' => json_decode($serializedUser)
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
    public function logoutAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        $user->setApiToken(null);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['status' => JsonResponse::HTTP_OK, 'message' => 'Logout'], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/forgot-password", name="forgotPassword", methods="POST")
     * @param Request $request
     * @param MailerInterface $mailer
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function forgotPasswordAction(Request $request, MailerInterface $mailer): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if(isset($content['email'])) {

            $userRepository = $this->entityManager->getRepository(User::class);

            /** @var User $user */
            $user = $userRepository->findOneBy(['email' => $content['email']]);

            if ($user !== null) {

                $token = $this->generateToken();

                $user->setResetToken($token);
                $this->entityManager->flush();

                $url = $this->getParameter('front_app_url').'/reset-password?token='.$token;

                $html = $this->render('forgot-password-email.html.twig', [
                   'url' => $url
                ]);

                $email = (new Email())
                    ->from('contact.perso@lucas-trebouet.fr')
                    ->to($user->getEmail())
                    ->text('Votre lien de réinitialisation : '.$url)
                    ->subject('Réinitialiser votre mot de passe MonScenar.io')
                    ->html($html->getContent());

                $mailer->send($email);

                return new JsonResponse(['status' => 200]);
            }
        }

        return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/reset-password", name="resetPassword", methods="POST")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $content['email']]);
        if ($user !== null) {
            if($user->getResetToken() === $content['token']) {
                $user->setPassword($encoder->encodePassword($user, $content['password']));
                $user->setResetToken(null);
                $this->entityManager->flush();
                return new JsonResponse(['status' => JsonResponse::HTTP_OK]);
            }
        }

        return new JsonResponse(JsonResponse::HTTP_BAD_REQUEST);
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