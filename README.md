# PHPReverseLatLon
Get the location information based on the latitude and longitude using Google Maps API.

This is a simple PHP class that allows you to pass your Google Maps API key, latitude and longitude and it will return data about the location found at that location. The class does check to see if you have the CURL extension installed, if so use CURL to download the map content. If not installed, it will check to see if fopen is allowed for external sites. If so, it will use the php function, file_get_contents(). To make sure file_get_contents() works with this class, you need to make sure this option is turned on in your PHP.ini file:

```
allow_url_fopen = on
```

Below is an example on how to use this class and the available options. Please note, it grabs the content from Google in JSON format and then we convert it to an object using the PHP function, json_decode(). I used PHP version 7.3.4 when I created this PHP class.

```php

/* Include the class file here */
include('./HelperGeo.php');
    
/* You need to add your Google Maps API key here */
$api_key = '';

/* Enter the latitude/longitude you want to look up. This example uses Memphis, Tennessee */
$lat = '35.149532';
$lon = '-90.048981';

/* This array holds all available options to grab for this reverse location lookup. Only
 * include the data you want to return
*/
$details = array(
  'address',
  'street',
  'city',
  'stateabb',
  'state',
  'postal',
  'country',
  'countrycode',
  'neighborhood',
  'county'
);
    
/* We startup the reverse lookup geo class */
$geo = new HelperGeo($api_key);

/* We then run the method that grabs the content and gets the location data you are requesting.
 * The options are:
 * $lat (Latitude)
 * $lon (Longitude)
 * $details (Array of the data you want to return from this location)
 * $types (Set this to 1 if you want to get the list of types found for this location. For example
 * it may show multiple values like "Premise, Hospital, Place of Business" etc. This is not needed,
 * I just added it in case you may have a use for it)
*/
$location_details = $geo->getLocationByLatLon($lat,$lon,$details,1);
    
/* We just dump out the result. Which will return either an array of the data you requested or it
 * will return an array with an error message. You can use this to check for an error or not. It
 * will be stored in an error under error. So in this case $location_details['error']
var_dump($location_details);
```
