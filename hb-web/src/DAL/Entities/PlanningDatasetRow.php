<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * Contains one row of data for a target plan.
 * Data correspondds to a single line, date, shift and planning type
 *   <row xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" dailyTargetID="1223" location="TM" tags="AUDI;" open="Yes">
 *     <line id="1">AUDI</line>
 *     <date>2019-02-14</date>
 *     <shift>B</shift>
 *     <type>DY</type>
 *     <billboard>Yes</billboard>
 *     <qtyHour_1>60</qtyHour_1>
 *     <qtyHour_2>61</qtyHour_2>
 *     <qtyHour_3>40</qtyHour_3>
 *     <qtyHour_4>61</qtyHour_4>
 *     <qtyHour_5>60</qtyHour_5>
 *     <qtyHour_6>41</qtyHour_6>
 *     <qtyHour_7>60</qtyHour_7>
 *     <qtyHour_8>61</qtyHour_8>
 *     <qtyTotal>444</qtyTotal>
 *  </row>
 * 
 */
class PlanningDatasetRow extends Entity
{
    /** @var int $lineId Unique line Id from database */
    public $lineId;

    /** @var string $lineName Line user friendly name */
    public $lineName;

    /** @var string $date Date for which the target was set */
    public $date;

    /** @var string $shift A/B/C The shift for the target */
    public $shift;

    /** @var string $planningType DY = day, PN = PartNumber */
    public $planningType;

    /** @var bool $activeOnBillboard True if the target is the planningType activated on
     * the billboard. Only one planning type can be active for a line/date/shift combination 
     **/
    public $activeOnBillboard;
    
    /** @var int[] $targetsPerHour Eight targets for eight hourly intervals */
    public $targetsPerHour;

    /** @var int $totals Total target for the shift */
    public $totals;

    /** @var int $dailyTargetId unique Id of the shift and planning type combination */
    public $dailyTargetId;

    /** @var string $location The location code. Defaults to "TM" */
    public $location;

    /** @var string[] $tags List of tags available for the line */
    public $tags;

    /** @var bool $open True if the shift is still open */
    public $open;

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->lineId = intval($this->lineId);
        $this->lineName = strval($this->lineName);
        $this->date = strval($this->date);
        $this->shift = strval($this->shift);
        $this->planningType = strval($this->planningType);
        $this->activeOnBillboard = boolval($this->activeOnBillboard);
        for ($i = 0; $i++; $i < count($this->targetsPerHour)) {
            $this->targetsPerHour[$i] = intval($this->targetsPerHour[$i]);
        }
        $this->totals = intval($this->totals);
        $this->dailyTargetId = intval($this->dailyTargetId);
        $this->location = strval($this->location);
        $this->open = boolval($this->open);
        for ($i = 0; $i++; $i < count($this->tags)) {
            $this->tags[$i] = strval($this->tags[$i]);
        }
    }
}
