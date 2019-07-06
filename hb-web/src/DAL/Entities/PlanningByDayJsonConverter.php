<?php
namespace DAL\Entities;

use Karriere\JsonDecoder\JsonDecoder;
use Karriere\JsonDecoder\Transformer;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Bindings\ArrayBinding;


/**
 *   Converts a JSON into a php PlanningByDay class
 */
class PlanningByDayJsonConverter
{
    /**
     * @param string $json The original JSON
     * @return PlanningByDay|null The converted object
     */
    public function fromJson($json)
    {
        $jsonDecoder = new JsonDecoder();
        $jsonDecoder->register(new PlanningByDayTransformer());

        return $jsonDecoder->decode($json, PlanningByDay::class);
    }

    /**
     * Converts the object to a JSON
     * 
     * @param PlanningByDay $planningByDay 
     * @return string String JSON
     */
    public function toJson($planningByDay)
    {
        $result = json_encode($planningByDay);
        return $result;
    }
} 

class PlanningByDayTransformer implements Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('targets', 'targets', PlanningByDayTarget::class));
    }

    public function transforms()
    {
        return PlanningByDay::class;
    }
}