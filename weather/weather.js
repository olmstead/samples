/** 
 *  Module to contain weather fetch and display functionality
 */
var Weather =  (function ($) {
    var exports = {};
    /**
     * load data from the server
     */
    exports.load = function (wban, year, month, day) {
        // fetch data for day from the server
        url = '/weather/api.php?wban=' + wban + '&day=' + year + '-' + month + '-' + day;
        $.ajax({
            url: url,
            method: 'GET',
            success: function(xhr) {
                // load data into page converting null values to 'N/A'
                var data = xhr.data;
                $('#max-temp').html(data.tmax ? (data.tmax + '&deg') : 'N/A');
                $('#min-temp').html(data.tmin ? (data.tmin + '&deg') : 'N/A');
                $('#avg-temp').html(data.tavg ? (data.tavg + '&deg') : 'N/A');
            },
            error: function(xhr) {
                // display 'N/A' for all values
                $('#max-temp').html('N/A');
                $('#min-temp').html('N/A');
                $('#avg-temp').html('N/A');
            }
        });
    };

    return exports;
}(jQuery));