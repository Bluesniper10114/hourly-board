<?php

namespace Core;

/**
 * View
 *
 * PHP version 7.0
 */
class View
{

    /**
     * Render a view file
     * @param  string $view The vie file
     * @param  array  $args Associative array of data to display in the view (optional)
     * @return void
     */
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $view;  // relative to Core directory

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }
}
