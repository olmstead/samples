<?php

/** 
 * Service for importing weather data
 * 
 * NOTE: design allows for multiple parser types
 */
class WeatherService {
    const FILE_TYPE_INDEX = 0;
    const FILE_YEAR_INDEX = 1;
    const FILE_MONTH_INDEX = 2; 
    
    /**
     * import data from a file and store in db
     * 
     * @param string $filepath
     */
    static public function importDataset($filepath) {
        if (!file_exists($filepath)) {
            echo "ERROR: dataset does not exist";
        }
        
        $filename = pathinfo($filepath, PATHINFO_FILENAME);

        $fileparts = explode('_', $filename);
        $filetype = $fileparts[self::FILE_TYPE_INDEX];
        $year = $fileparts[self::FILE_YEAR_INDEX];
        $month = $fileparts[self::FILE_MONTH_INDEX];

        // get an appropriate parse
        $parser = self::getParser($filetype);
        if (!$parser) {
            return;
        }
        list($wban, $days) = $parser->parse($filepath);
        foreach ($days as $day => $data) {
            $year = substr($day, 0, 4);
            $month = substr($day, 4, 2);
            $day = substr($day, 6, 2);
            $day = new Day($data, "$year-$month-$day", $wban);
            $day->persist();
        }
    }
   
    /**
     * Get an a parser that can handle the filetype
     * 
     * @param string $filetype
     * @return \NCDCParser|null
     */
    static private function getParser($filetype) {
        switch ($filetype) {
            case 'ncdc':
                return new NCDCParser();
            // can add other parsers here
            default:
                echo "ERROR: no parser for filetype $filetype";
                return NULL;
        }
    }
    
}

/**
 * common interface for parser implementations
 */
interface DataParser {
    public function parse($filepath);
}

/**
 * NCDC specific parser
 */
class NCDCParser implements DataParser {
    const WBAN_INDEX = 0;
    const DAY_INDEX = 1;
    const TMAX_INDEX = 2;
    const TMIN_INDEX = 4;
    const TAVG_INDEX = 6;
    const MISSING_DATA = 'M';
    
    /**
     * parse the file and return an array of the data
     * 
     * @param string $filepath
     * @return array
     */
    public function parse($filepath) {
        $days = array();
        $handle = fopen($filepath, "r");
        $found_data = false;
        $wban = NULL;
        $tmax = $tmin = $avg = 0;
        
        // extract data
        while (!feof($handle)) {
           $line = fgets($handle);
           $lineparts = explode(',', $line);
           
           if ($found_data) {
               
               // check for end of data
               if (empty($wban)) {;
                   // first data item, save wban
                   $wban = $lineparts[self::WBAN_INDEX];
               } else if ($wban != $lineparts[self::WBAN_INDEX]) {
                   // no wban, end of dataset
                   break;
               }
         
               $day = $lineparts[self::DAY_INDEX];
               // skip the month data
               if (strlen($day) != 8) {
                   continue;
               }
               
               // get data for one day
               $tmax = $lineparts[self::TMAX_INDEX];
               $tmin =  $lineparts[self::TMIN_INDEX];
               $tavg = $lineparts[self::TAVG_INDEX];
               
               // save data in array, set missing items to NULL
               $days[$lineparts[self::DAY_INDEX]] = array(
                   Day::TMAX_KEY => $tmax == self::MISSING_DATA ? NULL : trim($tmax),
                   Day::TMIN_KEY => $tmin == self::MISSING_DATA ? NULL : trim($tmin),
                   Day::TAVG_KEY => $tavg == self::MISSING_DATA ? NULL : trim($tavg)
               );
           } else if ($lineparts[0] == 'WBAN') {
               // next line is the beginning of the data
               $found_data = TRUE;
           }
        }
        
        fclose($handle);
        return array($wban, $days);
    } 
}
