<?php

/*
 * Controller for weather data
 * 
 */
class Controller {
    
    const HTTP_OK = 200;
    const HTTP_NOT_FOUND = 404;
    
    private $_params;
    
    public function __construct() {  
        // parse query string
        parse_str($_SERVER['QUERY_STRING'], $this->_params);
        
        // setup to server json
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
    }

    /** 
     *  get weather data in JSON format by day and wban id
     * 
     *  @uri /weather/?wban=<integer>&day=<year>-<padded-month>-<padded-day>
     */
    public function getAction() {
        $day = $this->_params['day'];
        $wban = $this->_params['wban'];

        if ($day == '' || $wban == '') {
            return $this->returnError(array("missing request parameters"));
        } 
        
        $location = Location::find($wban);
        if (!$location) {
            return $this->returnError(array("location missing from database"));
        } 
        
        $day = Day::find($wban, $day);
        if (!$day) {
            return $this->returnError(array("no data for that date"));
        } 
        return $this->returnJson($day->getData());
        
    }
    
    /**
     *  output errors as json
     */
    public function returnError($errors) {
        header('HTTP/1.0 404 No data available', true, self::HTTP_NOT_FOUND);
        $json = json_encode(array(
            'statusCode' => self::HTTP_NOT_FOUND,
            'errors' => $errors
        ));
        echo $json;
        ob_flush();
    }
    
    /**
     * output data as json
     */
    public function returnJson($data) {
        $json = json_encode(array(
            'statusCode' => self::HTTP_OK,
            'data' => $data
        ));
        echo $json;
        ob_flush();
    }
     
}