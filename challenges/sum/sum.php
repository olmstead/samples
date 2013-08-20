<?php
// TEST DATA
$input_data = array (
    array(0,0),
    array(2,0),
    array(0,2),
    array(-4,0),
    array(0,-4),
    array(3,5),
    array(-3,6),
    array(13,-8),
    //array(3,'A'),
);

// EXECUTION
main($argv, $input_data);

function main($argv, $input_data) {
    
    // if provided, use comman line data
    if (count($argv) == 3 ) {
        $input_data = array(
            array((float)$argv[1], (float)$argv[2])
        );
    } elseif (count($argv) != 1) {
        echo "\nUsage: php sum.php <number> <number>\n";
        echo "       php sum.php (runs test data)\n\n";
        return;
    }

    foreach ($input_data as $data) {
        list($a, $b) = $data;

        // add will throw an excpetion if numbers are non-numeri c
        try {
            $result = addIntegers($a, $b);
            echo "$a + $b = $result\n";
        } catch (Exception $e) {
            echo "ERROR: $e";
        }
    }
}


/**
 * Sum two integers using bitwise operations and recursion
 * 
 * @param integer $a
 * @param integer $b
 * @throws exception
 * @return integer restult
 * 
 */
function addIntegers($a, $b){ 
    if (!is_integer($a) || !is_integer($b)) {
        throw new Exception("input not an integer");
    }  
    
    // deal with trival cases
    if (!$a)  {
        return $b;
    } 
    if (!$b)  {
        return $a;
    } 
    
    // use bitwise operations AND, SHIFT, and OR together to add integers
    return addIntegers(($a & $b) << 1, $a ^ $b);
}