<?php


namespace App\Controller;


use App\Service\SerializerService;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;

class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /** @var ValidationService */
    protected $validationService;

    /** @var SerializerService */
    protected $serializerService;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(
        ValidationService $validationService,
        SerializerService $serializerService,
        EntityManagerInterface $entityManager
    )
    {
        $this->validationService = $validationService;
        $this->serializerService = $serializerService;
        $this->entityManager = $entityManager;
    }
}