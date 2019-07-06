<?php
namespace DAL\Entities;

/**
 * Method factory interface for distributing a shift plan to individual hours
 */
interface DatasetDistributionFactoryMethodInterface
{
    /**
     * Generates the 8 hourly targets
     * @param int $totalsPerShift Number of units to be distributed
     * @return int[] Array of 8 integer number of items
     */
    function generateHourlyDataset($totalsPerShift);
}