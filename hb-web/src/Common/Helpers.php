<?php

namespace Common;

/**
 * Helpers functions class
 */

class Helpers
{
    /**
     * Get IP of monitor
     * @return string
     */
    public static function getIP()
    {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validateIp($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        $checkResult = self::checkIpProxy();
        if (!empty($checkResult)) {
            return $checkResult;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validateIp($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validateIp($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validateIp($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED']) && self::validateIp($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }

        // return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }
    /**
     * Check IP passing through proxies
     * @return string|null
     */
    private static function checkIpProxy()
    {
        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (self::validateIp($ip)) {
                        return $ip;
                    }

                }
            } else {
                if (self::validateIp($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }
        }
    }
    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     * @param string $ip
     * @return bool
     */
    public static function validateIp($ip)
    {
        if (strtolower($ip) === 'unknown') {
            return false;
        }
        // generate ipv4 network address
        $ip = ip2long($ip);
        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            $result = self::internalValidateIp($ip);
            if (!$result) {
                return false;
            }
        }
        return true;
    }
    /**
     * Internal validateIp function
     * @param string $ip
     * @return bool
     */
    private static function internalValidateIp($ip)
    {
        if ($ip >= 0 && $ip <= 50331647) {
            return false;
        } elseif ($ip >= 167772160 && $ip <= 184549375) {
            return false;
        } elseif ($ip >= 2130706432 && $ip <= 2147483647) {
            return false;
        } elseif ($ip >= 2851995648 && $ip <= 2852061183) {
            return false;
        } elseif ($ip >= 2886729728 && $ip <= 2887778303) {
            return false;
        } elseif ($ip >= 3221225984 && $ip <= 3221226239) {
            return false;
        } elseif ($ip >= 3232235520 && $ip <= 3232301055) {
            return false;
        } elseif ($ip >= 4294967040) {
            return false;
        }
        return true;
    }

    /**
     * Sets a http header if an error message is present in the response array
     * 
     * @param array $response Array containing a key "errorMessage"
     * @return void
     */
    public static function setHttpResponseCodeOnError($response)
    {
        $errorMessage = isset($response['errorMessage']) ? $response['errorMessage'] : "";
        if ($errorMessage !== '') {
            http_response_code(500);
        } else {
            http_response_code(200);
        }

    }
}
