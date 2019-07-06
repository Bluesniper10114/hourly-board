<?php
if (!function_exists('getSetting')) {
    /**
     *
     * Get Setting
     * @param string $key
     *
     * @return string
    */
    function getSetting($key)
    {
        return \Settings\Config::getSetting($key);
    }
}

if (!function_exists('printr')) {
    /**
     * @brief A helper function to dump an array and optionally exit or continue parsing
     *
     * @param array $data Data array
     * @param bool $continue If false it exits.
     * @return void
     * @SuppressWarnings checkProhibitedFunctions
     */
    function printr($data = [], $continue = false)
    {
        echo "<PRE>";
        print_r($data);
        echo "</PRE>";
        if (!$continue){
            exit;
        }
    }
}
if (!function_exists('vd')) {
    /**
     * @brief A helper function to dump an array ( with data types ) and optionally exit or continue parsing
     * @param array $data Data array
     * @param bool $continue If false it exits.
     * @return void
     * @SuppressWarnings checkProhibitedFunctions
     */
    function vd($data = [], $continue = false)
    {
        echo "<PRE>";
        var_dump($data);
        echo "</PRE>";
        if (!$continue){
            exit;
        }
    }
}


if (!function_exists('redirectTo')) {
    /**
     * @brief Redirects to a link
     * @param string $url Url to redirect to
     * @param int $statusCode HTTP status code to return
    */
    function redirectTo($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
}

if (!function_exists('arrayKeysExist')) {
    /**
     * Checks if the keys in array $keys exist in dictionary $targetArray
     *
     * @param string[] $keys Keys to find
     * @param object[] $targetArray Array where to look for the keys
     * @return bool True if all the keys exists, false if any key is missing
     */
    function arrayKeysExist(array $keys, array $targetArray)
    {
        return !array_diff_key(array_flip($keys), $targetArray);
    }
}

if (!function_exists('getSessionMessage')) {

    /**
     *
     * Set Session Alert Message
     * @param string $type
     * @param string $message
     *
     * @return void
     */
    function setSessionMessage($type, $message)
    {
        $storageSessionMessage = new Core\Persistence\PersistentObject("appAlert");
        $storageSessionMessage->save(['type' => $type, 'message' => $message]);
    }

    /**
     * Get Session Alert Message
     *
     * @param string $defaultValue
     *
     * @return string
     */
    function getSessionMessage($defaultValue = null)
    {
        $storageSessionMessage = new Core\Persistence\PersistentObject("appAlert");
        $data = $storageSessionMessage->getValue($defaultValue);
        $msgHtml = '';
        if (!empty($data) && $data !== $defaultValue){
        $storageSessionMessage->clear();
        $cls = 'alert-danger';
            if ($data['type'] === 'success'){
                $cls = 'alert-success';
            }
            $msgHtml = '<div class="alert ' . $cls . '" role="alert">
                                  ' . $data['message'] . '
                        </div>';
        }
        return $msgHtml;
    }
}
if (!function_exists('urlGenerate')) {
    /**
     *
     * Generate Url of page by merging new query parameters into existing page url parameters
     * @param string $page
     * @param array $query
     *
     * @return string
     */
    function urlGenerate($page, $query = [])
    {
        $parts = parse_url($_SERVER['REQUEST_URI']);
        $query0 = null;
        if (!empty($parts['query'])){
            parse_str($parts['query'], $query0);
            $query = array_merge($query0, $query);
        }
        return SITE_URL . $page . "?" . http_build_query($query);
    }
}

if (!function_exists('getHttpHeaderInfo')) {
    /**
     * Gets the token from the HTTP header request.
     * Note: php stores HTTP header request information in the $_SERVER array. 
     * The keys it uses are particular to php and don't match the standard. 
     * 
     * @param string $key The HTTP request protocol key
     * @return string|null The token
     */
    function getHttpHeaderInfo($key = "Authorization")
    {
        // map standard keys to php specific keys.
        $map = [
            "Authorization" => "HTTP_AUTHORIZATION"
        ];
        if (!isset($map[$key])) {
            return null;
        }

        $attribute = $map[$key];
        if (isset($_SERVER[$attribute])) {
            return $_SERVER[$attribute];
        }
        return null;
    }
}
?>