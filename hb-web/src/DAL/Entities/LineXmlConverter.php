<?php
namespace DAL\Entities;

class LineXmlConverter
{
    /**
     * Converts an Xml string into a list of Lines
     * 
     * @param string $xml Original Xml string 
     * @return Line[] A list of line objects
     */
    public function fromXml($xml)
    {
        $lines = [];
        $parser = new \SimpleXMLElement($xml);
        
        $lineParser = $parser->lines;
        $cellParser = $parser->cells;
        $machineParser = $parser->machines;

        foreach ($lineParser->line as $parsedLine) {
            $line = new Line();
            //$line->id = $parserLine->id;
            $line->name = $parsedLine->name;
            $line->location = $parsedLine->location;
            $line->description = $parsedLine->description;
            $tags = explode(";", $parsedLine->tags);
            $line->tags = array_filter($tags); // remove empty entries
            $line->cells = [];
            foreach ($cellParser->cell as $parsedCell) {
                if ($parsedCell->line === $line->name) {
                    $cell = $this->getCell($parsedCell, $machineParser);
                    $cell->line = $line;
                    $line->cells[] = $cell;
                }
            }

            $line->typify();
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Builds a cell and its corresponding machines
     * 
     * @param \SimpleXMLElement $parsedCell The Xml object containing a single cell
     * @param \SimpleXMLElement $machinesParser The Xml object containing all the machines
     */
    protected function getCell($parsedCell, $machinesParser)
    {
        $cell = new Cell();
        $cell->location = $parsedCell->location;
        $cell->name = $parsedCell->name;
        $cell->description = $parsedCell->description;
        $cell->machines = [];
        foreach ($machinesParser->machine as $parsedMachine) {
            if ($parsedMachine->cell === $cell->name) {
                $machine = $this->getMachine($parsedMachine);
                $cell->machines[] = $machine;
            }
        }
    }

    /**
     * Builds a machine 
     * 
     * @param \SimpleXMLElement $machineParser The Xml object containing a single machine
     * @return Machine A machine
     */
    protected function getMachine($machineParser)
    {
        $machine = new Machine();
        $machine->location = $machineParser->location;
        $machine->line = $machineParser->line;
        $machine->cell = $machineParser->cell;
        $machine->name = $machineParser->name;
        $machine->description = $machineParser->description;
        $machine->reference = $machineParser->reference;
        $machine->previousMachine = $machineParser->previousMachine;
        $machine->eol = $machineParser->eol;
        $machine->type = $machineParser->machineType;
        $machine->capacity = $machineParser->capacity;
        $machine->routing = $machineParser->routing;
        return $machine;
    }
}