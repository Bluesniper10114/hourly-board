<?php 
namespace API\Planning;

use DAL\LayoutDALv1;
use DAL\Entities\Line;
use DAL\Entities\LineXmlConverter;

class PlanningModel extends \Common\Model
{
    /** @var LayoutDALv1 */
    public $dal;

    /**
     * Gets all the lines available
     * @return array Lines 
     */
    public function getLines()
    {
        return $this->dal->getLinesList();
    }

    /**
     * Gets a list of tags
     * @return string[] List of tags
     */
    public function getTags()
    {
        return $this->dal->getTagsList();
    }

    /**
     * @param int $profileId User making the request
     * @return Line[]|null All line details, including cells and machines
     */
    public function getLinesCellsAndMachines($profileId)
    {
        $linesXml = $this->dal->getLinesCellsAndMachines($profileId);
        if (is_null($linesXml)) {
            return null;
        }
        $converter = new LineXmlConverter();
        $lines = $converter->fromXml($linesXml);
        return $lines;
    }   
}