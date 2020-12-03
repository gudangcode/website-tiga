<?php

namespace Acelle\Library;

use SimpleXMLElement;

/**
 * Tool class.
 */
class Rss
{
    public static function parse($content)
    {
        $rss = new \Twig\TwigFunction('rss', function ($url, $count = 10) {
            $dom = simplexml_load_string(file_get_contents($url), 'SimpleXMLElement', LIBXML_NOCDATA);
            $x = self::xmlToArray($dom);
            $x = ($x['rss']['channel']);
            $x['item'] = array_slice($x['item'], 0, $count);
            return $x;
        });

        $loader = new \Twig\Loader\ArrayLoader([
            'content' => $content,
        ]);

        $twig = new \Twig\Environment($loader);
        $twig->addFunction($rss);

        return $twig->render('content');
    }

    private static function xmlToArray(SimpleXMLElement $xml): array
    {
        $parser = function (SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (0 !== count($attributes)) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['attributes'][$attrName] = html_entity_decode(strval($attrValue));
                }
            }

            if (0 === $nodes->count()) {
                // $collection['value'] = strval($xml);
                // return $collection;
                return html_entity_decode(strval($xml));
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);
                    continue;
                }

                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [
            $xml->getName() => $parser($xml)
        ];
    }
}
