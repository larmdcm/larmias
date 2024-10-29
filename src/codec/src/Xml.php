<?php

declare(strict_types=1);

namespace Larmias\Codec;

use InvalidArgumentException;
use Larmias\Contracts\Arrayable;
use SimpleXMLElement;

class Xml
{
    /**
     * @param mixed $data
     * @param SimpleXMLElement|null $parentNode
     * @param string $root
     * @return string
     * @throws \Exception
     */
    public static function toXml(mixed $data, ?SimpleXMLElement $parentNode = null, string $root = 'root'): string
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } else {
            $data = (array)$data;
        }
        if ($parentNode === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?>' . "<{$root}></{$root}>");
        } else {
            $xml = $parentNode;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::toXml($value, $xml->addChild($key));
            } else {
                if (is_numeric($key)) {
                    $xml->addChild('item' . $key, (string)$value);
                } else {
                    $xml->addChild($key, (string)$value);
                }
            }
        }
        return trim($xml->asXML());
    }

    /**
     * @param string $xml
     * @return array
     */
    public static function toArray(string $xml): array
    {
        $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

        if ($respObject === false) {
            throw new InvalidArgumentException('Syntax error.');
        }

        return json_decode(json_encode($respObject), true);
    }
}