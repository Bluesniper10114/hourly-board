<?php
namespace DAL\Entities;

/**
 * Creates a standard distribution equally dividing totals to hourly interval.
 * The remainder is then distributed to the last hourly interval.
 */
class DatasetStandardDistributionFactoryMethod implements DatasetDistributionFactoryMethodInterface
{
    /**
     * @inheritDoc
     */
    function generateHourlyDataset($totalsPerShift)
    {
        $rest = $totalsPerShift % 8;
        $hourly = intdiv($totalsPerShift, 8); // PHP 7.0 and up
        $values = [];
        for ($i = 0; $i < 8; $i++) {
            if ($i === 7) {
                $value = $hourly + $rest;
            }
            else {
                $value = $hourly;
            }
            $values[$i] = $value;
        }
        return $values;    
    }
}