<?php
// TEST DATA

// directory does not exist
$path1 = '/some/directory';
// directory contains no files
$path2 = '/Users/john/src/cpf/trunk/tests/metro';
// directory contains files
$path3 = '/Users/john/src/cpf/trunk/tests';

// EXECUTION
main($argv);
function main($argv) {
    if (count($argv) != 2 ) {
        echo "\nUsage: php tree.php <directory>\n\n";
        return;
    }
    listDirectory($argv[1]);
}


// IMPLEMENTATION
    
/**
 * Files are displayed with a preceeding "-"
 * 
 * @param string $path directory to list, default is current
 * @param integer $depth recursion depth
 * @return boolean
 */
function listDirectory($path = '.', $depth = 0) {
    
    // ignored files
    $ignore = array('.', '..', '.svn', '.git', '.DS_Store');
    
    // handle case where directry does not exist
    if (!is_dir($path)) {
        echo "Directory $path does not exist.\n";
        return false;
    }
    
    // use a recursive iterator
    $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), 
            RecursiveIteratorIterator::SELF_FIRST);
    
    foreach($objects as $file => $object){
        
        // skip some files
        if (in_array($file, $ignore)) {
            continue;
        }  
        
        // output tree info
        $depth = $objects->getDepth();
        $isdir = is_dir("$file");
        echo str_repeat("\t", $depth).($isdir ? '' : ' -')."$file\n";
    }
   
    return TRUE;
}




