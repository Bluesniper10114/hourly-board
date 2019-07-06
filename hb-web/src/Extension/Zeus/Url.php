<?php
namespace Extension\Zeus;

/**
 * Provides helpers to nagivate from one page to another
 */
class Url
{
    /**
     * Forces a certain path to load. It closes the current page.
     *
     * @param string $page
     * @return void
     */
    public static function navigateToPage($page)
    {
        header("Location: " . SITE_URL . "$page");
        exit;
    }

    /**
     * @brief: Redirects to the caller page
     * @return void
     */
    public static function redirectBack()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            Url::navigateToPage('');
        }
    }

    /**
     * Gets the caller URL
     *
     * @return string Caller URL
     **/
    public static function getBackUrl()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return SITE_URL;
        }
    }

    /**
     * remove given query key from url
     * @param  string $url      [description]
     * @param  string $queryKey [description]
     * @return string           [description]
     */
    public static function removeQueryFromURL($url = "", $queryKey = "")
    {
        return preg_replace('~(\?|&)' . $queryKey . '=[^&]*~', '$1', $url);
    }
}
?>