<?php
namespace DAL;
use PDO;
/**
 * Utils DAL class
 *
 *
 */
class UtilsDAL extends \Core\Data
{

    /**
     * Gets all localization languages available for this website
     *
     * @return array ["Code", "Name"] E.g. "en", "English
     */
    public function getLanguages()
    {
        $languages = [["Code" => "ro", "Name" => "Romana"],
        ["Code" => "en", "Name" => "English"]];
        return $languages;
    }
}