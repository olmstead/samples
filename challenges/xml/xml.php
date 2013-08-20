<?php
// TEST DATA

$empty =  <<<XML

XML;

$xml_invalid =  <<<XML
<?xml version='1.0'?> 
<root>
    <step order="1">Cook spaghetti</step>
    <step order="3">Add Sauce</step>
    <step order="2">Drain from pot</step>
    <dish>Pasta</dish>
</root>
XML;

$xml_valid =  <<<XML
<?xml version='1.0'?> 
<root>
    <instructions>
        <step order="1">Cook spaghetti</step>
        <step order="3">Add Sauce</step>
        <step order="2">Drain from pot</step>
    </instructions>
    <dish>Pasta</dish>
</root>
XML;

// EXECUTION
//echo getSortedSteps($empty);
//echo getSortedSteps($xml_invalid);

main($xml_valid);

function main ($xmlstr) {
    echo "\nINPUT:\n$xmlstr\n";
    $list = getSortedSteps($xmlstr) . "\n";
    echo "\nOUTPUT:\n$list\n";  
}

// IMPLEMENTATION
/**
 * @param string $xmlstr valid xml string
 * @return string sorted list of steps
 */
function getSortedSteps ($xmlstr) {
    
    // trim white space
    $xmlstr = trim($xmlstr);
    
    // return false if string is empty
    if (empty($xmlstr)) {
        echo "No data provided\n"; 
        return FALSE;
    }
    
    // convert XML string to simple xml object
    $object = simplexml_load_string($xmlstr);
    
    // return false if data is invalid
    if (!($object && $object->instructions && $object->instructions->step)) {
        echo "Invalid XML data\n";
        return FALSE;
    }

    // build an associate array from the object of value => order
    $array = array();
    foreach ($object->instructions->step as $step){
        $array[(string)$step] = (integer)$step->attributes()->order;
    }

    // sort the associative array by order 
    asort($array);

    // return comma separated list of keys
    return  implode(array_keys($array), ', ');

}
