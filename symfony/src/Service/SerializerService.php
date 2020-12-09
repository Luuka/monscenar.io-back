<?php


namespace App\Service;


use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerService
{
    /** @var Serializer $serializer */
    private $serializer;

    /**
     * SerializerService constructor.
     */
    public function __construct()
    {
        $this->serializer = self::getSerializer();
    }

    /**
     * @return Serializer
     */
    private static function getSerializer()
    {
        $annotationsLoader = new AnnotationLoader(new AnnotationReader());
        $classMetadataFactory = new ClassMetadataFactory($annotationsLoader);
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $encoders = [new JsonEncoder()];
        $normalizers = [$normalizer];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * @param Object $data
     * @param string $format
     * @param array $context
     * @return string
     */
    public function serialize(Object $data, $context = [], string $format = 'json')
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * @param string $data
     * @param string $type
     * @param string $format
     * @return array|mixed|object
     */
    public function deserialize(string $data, string $type, $format = 'json') {
        return $this->serializer->deserialize($data, $type, $format);
    }
}