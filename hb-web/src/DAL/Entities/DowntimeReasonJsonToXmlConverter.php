<?php
namespace DAL\Entities;

/**
 * Transforms incoming Json object with reasons into an XML string
 * to match the requirements of the DB procedure call
 */
class DowntimeReasonJsonToXmlConverter
{
    /**
     * @var string[] $reasonsDictionary;
     */
    public $reasonsDictionary = [];

    /**
     * @var array List of reasons 
     */
    public $downtimeReasons = [];

    /**
     * @var int $hourlyId 
     */
    public $hourlyId = 0;

    /**
     * @var string $timeStamp Timestamp when the call was received
     */
    public $timeStamp;

    /**
     * 
     * {
     *   "hourlyId": 2446,
     *   "timeStamp": "2019-01-27 18:14:33.567",
     *   "downtimeReasons": [
     *       {
     *            "downtimeId": 140,
     *            "timeInterval": "09:16 09:17",
     *            "machine": "WB1501",
     *            "validatedDuration": 1,
     *            "totalDuration": 1,
     *            "timeStamp": "2019-01-27 18:14:33.567",
     *            "reasons": [
     *                {
     *                    "id": 1,
     *                    "duration": 1,
     *                    "reason": 5
     *                }
     *            ]
     *       },
     *       {
     *            "downtimeId": 141,
     *            "timeInterval": "09:16 09:21",
     *            "machine": "WB1501",
     *            "validatedDuration": 5,
     *            "totalDuration": 5,
     *            "timeStamp": "2019-01-27 18:14:33.567",
     *            "reasons": [
     *                {
     *                    "id": 2,
     *                    "duration": 2,
     *                    "reason": 7
     *                },
     *                {
     *                    "id": 3,
     *                    "duration": 3,
     *                    "reason": 9
     *                }
     *            ]
     *       }
     *   ]
     * }
     * 
     * 
     * 
     * 
     * 
     * 
     * <root>
     *   <reasons downtimeid="140">
     *     <reason id="1" timeStamp="2019-01-24 00:57:13.680 ">
     *       <comment>Comment 1</comment>
     *       <duration>1</duration>
     *     </reason>
     *   </reasons>
     *   <reasons downtimeid="141">
     *     <reason id="2" timeStamp="2019-01-24 00:57:13.680 ">
     *       <comment>Comment 2</comment>
     *       <duration>2</duration>
     *     </reason>
     *     <reason id="3" timeStamp="2019-01-24 00:57:13.680 ">
     *       <comment>Comment 3</comment>
     *       <duration>3</duration>
     *     </reason>
     *   </reasons>
     * </root>
     * 
     * 
     * 
     * @return string
     */
    public function transform()
    {
        $xml = new \SimpleXMLElement('<root/>');
        foreach ($this->downtimeReasons as $downtime) {
            $reasonsXml = $xml->addChild("reasons");
            $reasonsXml->addAttribute("downtimeid", $downtime->downtimeId);

            foreach ($downtime->reasons as $downtimeReason) {
                $reasonXml = $reasonsXml->addChild("reason");
                
                $reasonTimeStamp = ($downtimeReason->id === 0) ? $this->timeStamp : $downtimeReason->timeStamp;
                $reasonId = $downtimeReason->reason;
                $comment = isset($this->reasonsDictionary[$reasonId]) ? $this->reasonsDictionary[$reasonId] : null;

                $reasonXml->addAttribute("id", $downtimeReason->id);
                $reasonXml->addAttribute("timeStamp", $reasonTimeStamp);
                $reasonXml->addChild("comment", $comment);
                $reasonXml->addChild("duration", $downtimeReason->duration);
            }
        }
        return $xml->asXML();
    }
}