<?php

namespace BaseApi\Support;

use BaseApi\Contracts\ResourceInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Deserializer
 * @package BaseApi\Support
 */
class Deserializer
{
    /**
     * @param array $items
     * @param string $className
     *
     * @return array|ResourceInterface[]
     */
    public function deserializeItems(array $items, string $className): array
    {
        foreach ($items as $key => $item) {
            $items[$key] = $this->deserializeItem($item, $className);
        }

        return $items;
    }

    /**
     * @param array $item
     * @param string $className
     *
     * @return ResourceInterface
     */
    public function deserializeItem(array $item, string $className): ResourceInterface
    {
        static $serializer = null;
        if ($serializer === null) {
            $normalizers = [
                new GetSetMethodNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
                new DateTimeNormalizer(),
                new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
            ];
            $serializer = new Serializer($normalizers, [new JsonEncoder()]);
        }

        return $serializer->deserialize(json_encode($item), $className, 'json');
    }
}
