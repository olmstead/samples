<?php

/*
   f(x) = f(x-1)^3 + f(x-2)^2 + f(x-3)
    where
    f(3) = 3
    f(2) = 2
    f(1) = 1 
 */

// TEST DATA
$test_data = array (1,2,3,4,5,6);

// EXECUTION
main($test_data);

function main($dataset) {
    echo "\nRecursive Solution\n";
    foreach ($dataset as $data) {
        $val = f_recursive($data);
        echo "f($data) = $val\n";
    }
    echo "\nIterative Solution\n";
    foreach ($dataset as $data) {
        $val = f_iterative($data);
        echo "f($data) = $val\n";
    }
}

// IMPLEMENTATION
/*
 * recursive implementation
 * @param $x positive number
 * @return result   
 * 
 */
function f_recursive($x) {
    if (!is_integer($x) || $x <= 0)  {
        throw new Exception('number must be a positve integer');
    }
    
    // handle known base cases
    if ($x <= 3)  {
        return $x;
    }
    
    // handle all other cases by recursing down
    return  pow(f_recursive($x-1), 3) + pow(f_recursive($x-2), 2) + f_recursive($x - 3);
}

/*
 * iterative implementation
 * @param $x positive number
 * @return result   
 * 
 */
function f_iterative($x) {
    if ($x <= 0)  {
        throw new Exception('number must be a positve integer');
    }

    // build from the bottom up and cache values along the way
    $cached = array();
    for ($i = 1; $i <= $x; $i++) {
        $value = 0;
        if ($i <= 3) {
             // handle base cases
            $value = $i;
        } else {
            // handle all other cases            
            $value = pow($cached[(string)$i-1], 3) +  pow((string)$cached[$i-2], 2) + $cached[(string)$i-3];
        }
        // cache the value
        $cached[$i] = $value;
    }
    // return value at $x
    return $cached[$x];
}