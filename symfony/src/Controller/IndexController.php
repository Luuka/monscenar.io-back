<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/healthcheck", name="healthcheck")
     */
    public function indexAction()
    {
        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/authtest", name="index")
     */
    public function authTestAction()
    {
        return new JsonResponse(['status' => 'ok']);
    }
}