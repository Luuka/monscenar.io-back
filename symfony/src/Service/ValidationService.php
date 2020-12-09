<?php


namespace App\Service;


use PhpParser\Builder\Class_;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(Object $entity)
    {
        $errors = $this->validator->validate($entity);

        $errorsMessages = [];

        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $errorsMessages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorsMessages;
    }
}