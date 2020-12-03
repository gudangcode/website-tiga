<?php

/**
 * StringHelper class.
 *
 * Provide string helper methods
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   Acelle Library
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Library;

use DOMDocument;

class StringHelper
{
    /**
     * Custom base64 encoding. Replace unsafe url chars.
     *
     * @param string $val
     *
     * @return string
     */
    public static function base64UrlEncode($val)
    {
        return strtr(base64_encode($val), '+/=', '-_,');
    }

    /**
     * Custom base64 decode. Replace custom url safe values with normal
     * base64 characters before decoding.
     *
     * @param string $val
     *
     * @return string
     */
    public static function base64UrlDecode($val)
    {
        return base64_decode(strtr($val, '-_,', '+/='));
    }

    /**
     * Custom base64 decode. Replace custom url safe values with normal
     * base64 characters before decoding.
     *
     * @param string $val
     *
     * @return string
     */
    public static function cleanupMessageId($msgId)
    {
        return preg_replace('/[<>\s]*/', '', $msgId);
    }

    /**
     * Custom base64 decode. Replace custom url safe values with normal
     * base64 characters before decoding.
     *
     * @param string $val
     *
     * @return string
     */
    public static function getDomainFromEmail($email)
    {
        return substr(strrchr($email, '@'), 1);
    }

    /**
     * Generate MessageId from domain name.
     *
     * @param string $val
     *
     * @return string
     */
    public static function generateMessageId($domain, $test = false)
    {
        // @note: be careful when changing the message format, it may impact some other parts of the application
        // For example, see the DeliveryHandler::getMessageId()
        if ($test) {
            // generate a test MessageId for a test email
            // then replace the uniqid() with 0*13
            return time().rand(100000, 999999).'.0000000000000@'.$domain;
        } else {
            return time().rand(100000, 999999).'.'.uniqid().'@'.$domain;
        }
    }

    /**
     * Check if a given string is a test Message Id.
     *
     * @param string $messageId
     *
     * @return bool
     */
    public static function isTestMessageId($messageId)
    {
        return strpos($messageId, '.0000000000000@') !== false;
    }

    /**
     * Custom base64 decode. Replace custom url safe values with normal
     * base64 characters before decoding.
     *
     * @param string $val
     *
     * @return string
     */
    public static function joinUrl()
    {
        $array = array_map(function ($e) {
            return preg_replace('/(^\/+|\/+$)/', '', $e);
        }, func_get_args());

        return implode('/', $array);
    }

    /**
     * Extract SendGrid X-Message-Id from Smtp-Id
     * For example, extract "GuUFV1znQTmkQyPXrPLyxA" from "<GuUFV1znQTmkQyPXrPLyxA@ismtpd0019p1sin1.sendgrid.net>".
     *
     * @param string $val
     *
     * @return string
     */
    public static function extractSendGridMessageId($smtpId)
    {
        $cleaned = self::cleanupMessageId($smtpId);

        return substr($cleaned, 0, strpos($cleaned, '@'));
    }

    /**
     * Detect file encoding.
     *
     * @param string file path
     *
     * @return string encoding or false if cannot detect one
     */
    public static function detectEncoding($file, $max = 100)
    {
        $file = fopen($file, 'r');

        $sample = '';
        $count = 0;
        while (!feof($file) && $count <= $max) {
            $count += 1;
            $sample .= fgets($file);
        }
        fclose($file);

        return mb_detect_encoding($sample, 'UTF-8, ISO-8859-1', true);
    }

    /**
     * Convert from one encoding to the other.
     *
     * @param string file path
     */
    public static function toUTF8($file, $from = 'UTF-8')
    {
        $content = file_get_contents($file);
        $content = mb_convert_encoding($content, 'UTF-8', $from);
        file_put_contents($file, $content);
    }

    /**
     * Check if a (UTF-8 encoded) file contains BOM
     * Fix it (remove BOM chars) if any.
     *
     * @param string file path
     */
    public static function checkAndRemoveUTF8BOM($file)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = file_get_contents($file);
        $matched = preg_match("/^$bom/", $text, $result);

        if (!$matched) {
            return false;
        }

        $text = preg_replace("/^$bom/", '', $text);
        file_put_contents($file, $text);

        return true;
    }

    public static function replaceBareLineFeed($content)
    {
        return trim(preg_replace("/(?<=[^\r])\n/", "\r\n", $content));
    }

    public static function getRandomUSIpAddresses()
    {
        $ips = [
            '204.113.58.0',
            '121.54.104.0',
            '213.157.236.0',
            '31.142.80.0',
            '212.95.142.0',
            '172.237.145.0',
            '202.3.232.0',
            '123.50.72.0',
            '62.162.72.0',
            '195.76.126.0',
            '104.235.112.0',
            '203.84.183.0',
            '79.107.195.0',
            '197.210.185.0',
            '216.9.154.0',
            '173.25.32.0',
            '156.157.173.0',
            '31.132.224.0',
            '23.18.2.0',
            '58.181.96.0',
            '201.190.47.0',
            '208.84.83.0',
            '213.101.168.0',
            '201.217.11.0',
            '212.235.251.0',
            '153.215.187.0',
            '217.131.28.0',
            '203.128.83.0',
            '37.218.168.0',
            '61.94.163.0',
            '188.140.171.0',
            '188.135.24.0',
            '89.135.185.0',
            '84.237.238.0',
            '118.97.136.0',
            '61.5.40.0',
            '87.110.26.0',
            '87.110.41.0',
            '41.224.103.0',
            '125.139.107.0',
            '59.98.0.0',
            '222.45.112.0',
            '196.13.102.0',
            '85.71.183.0',
            '1.9.100.0',
            '213.100.103.0',
            '201.252.22.0',
            '201.240.143.0',
            '201.216.155.0',
            '212.52.128.0',
            '213.154.39.0',
            '59.129.59.0',
            '213.14.139.0',
            '182.1.62.0',
            '93.187.176.0',
            '89.142.48.0',
            '36.37.133.0',
            '58.157.102.0',
            '41.74.46.0',
            '205.242.198.0',
            '59.104.0.0',
            '212.156.99.0',
            '83.239.122.0',
            '88.229.68.0',
            '94.66.154.0',
            '120.89.125.0',
            '220.224.146.0',
            '84.112.150.0',
            '31.206.151.0',
            '37.26.98.0',
            '94.67.116.0',
            '202.188.148.0',
            '109.178.2.0',
            '115.133.224.0',
            '74.128.61.0',
            '125.162.76.0',
            '109.76.207.0',
            '31.209.96.0',
            '194.157.4.0',
            '94.25.40.0',
            '94.66.59.0',
            '213.87.251.0',
            '37.233.130.0',
            '31.144.173.0',
            '89.47.78.0',
            '43.229.13.0',
            '217.16.81.0',
            '203.210.235.0',
            '88.231.42.0',
            '213.179.245.0',
            '31.128.41.0',
            '197.231.251.0',
            '89.143.77.0',
            '78.171.254.0',
            '59.131.117.0',
            '90.139.213.0',
            '87.229.46.0',
            '217.73.140.0',
            '188.245.148.0',
            '94.66.206.0',
            '36.252.32.0',
            '212.26.188.0',
            '217.107.126.0',
            '196.200.61.0',
            '31.152.23.0',
            '223.205.207.0',
            '217.117.176.0',
            '36.37.231.0',
            '202.53.146.0',
            '202.8.73.0',
            '180.54.50.0',
            '103.30.138.0',
            '41.63.223.0',
            '41.73.212.0',
            '31.141.120.0',
            '31.128.90.0',
            '212.90.50.0',
            '202.252.244.0',
            '203.104.24.0',
            '80.249.68.0',
            '196.22.6.0',
            '205.160.111.0',
            '116.96.30.0',
            '117.242.81.0'
        ];

        return $ips[array_rand($ips)];
    }

    public static function purifyHtml($html, $tags = [])
    {
        $defaults = ['script'];
        $defaults = array_merge($defaults, $tags);
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // disable warning for invalid tags
        $dom->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        foreach($defaults as $tag) {
            $itemsToRemove = $dom->getElementsByTagName($tag);

            $remove = [];
            foreach($itemsToRemove as $item) {
                $remove[] = $item;
            }

            foreach ($remove as $item) {
                $item->parentNode->removeChild($item); 
            }
        }
        return $dom->saveHTML();
    }
}
