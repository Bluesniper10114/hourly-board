<?php
namespace DAL\Entities;

use \Core\Entity;
use Karriere\JsonDecoder\JsonDecoder;
use Karriere\JsonDecoder\Transformer;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Bindings\ArrayBinding;

/**
 * DTO class used to map the XML result of PartNumber planning into a php object.
 * Subsequently this object will be encoded into JSON.
 */
class PlanningByPartNumberJsonConverter
{

    /**
     * @param string $json The original JSON
     * @return PlanningByPartNumber|null The converted object
     */
    public function fromJson($json)
    {
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new PlanningByPartNumberTransformer());

        return $jsonDecoder->decode($json, PlanningByPartNumber::class);
    }

    /**
     * Converts the object to a JSON
     * 
     * @param PlanningByPartNumber $planningByPartNumber 
     * @return string String JSON
     */
    public function toJson($planningByPartNumber)
    {
        $result = json_encode($planningByPartNumber);
        return $result;
    }
}

/**
 * Provides information about Json decoding for the PlanningByPartNumber class
 */
class PlanningByPartNumberTransformer implements Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('rows', 'rows', PlanningByPartNumberRow::class));
    }

    public function transforms()
    {
        return PlanningByPartNumber::class;
    }
}
