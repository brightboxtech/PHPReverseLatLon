<?php

    class HelperGeo {
        
        private $apikey;
        
        public function __construct($apikey)
        {
            
            $this->apikey = $apikey;
            
        }
        
        public function getLocationByLatLon($lat,$lon,$details,$types = 0)
        {
            
            //We generate variables needed for this method
            $map_details = FALSE;
            $map_types = FALSE;
            
            //We first get the URL to use for this location request
            $map_url = $this->generateGoogleMapURL($lat,$lon);
            
            //We now download the content from the map URL
            $map_content = $this->getGoogleMapData($map_url);
            
            //We check to see if any errors were passed back to us
            if(!$map_content['error'])
            {
                
                //We get convert the JSON value into a readable array
                $map_array = json_decode($map_content['data']);
                
                //We check to see if there is a map error
                if(isset($map_array->error_message))
                {
                    
                    //We assign the error to the array and do nothing else but return the error back to script
                    $map_details['error'] = trim($map_array->error_message);
                    
                }else
                {
                
                    //We now go through the list of location details requested
                    if($details)
                    {
                        
                        //We generate the array that will house the location details below
                        $map_details = array();
                        
                        foreach($details AS $curr_detail)
                        {
                            
                            //We put the current detail into proper format
                            $curr_request = strtolower(trim($curr_detail));
                            
                            switch($curr_request)
                            {
                                
                                case 'country':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('country',$map_array->results[0]->address_components);
                                    
                                break;
                                
                                case 'stateabb':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('administrative_area_level_1',$map_array->results[0]->address_components,TRUE);
                                    
                                break;
                                
                                case 'state':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('administrative_area_level_1',$map_array->results[0]->address_components);
                                    
                                break;
                                
                                case 'city':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('locality',$map_array->results[0]->address_components);
                                    
                                break;
                                
                                case 'street':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('street_number',$map_array->results[0]->address_components) .' ' .$this->findMapContentByName('route',$map_array->results[0]->address_components);                                
                                    
                                break;
                                
                                case 'postal':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('postal_code',$map_array->results[0]->address_components);
                                    
                                break;
                                
                                case 'countrycode':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('country',$map_array->results[0]->address_components,TRUE);
                                    
                                break;
                                
                                case 'county':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('administrative_area_level_2',$map_array->results[0]->address_components);
                                    
                                break;
                                
                                case 'address':
                                    
                                    $map_details[$curr_request] = trim($map_array->results[0]->formatted_address);
                                    
                                break;
                                
                                case 'neighborhood':
                                    
                                    $map_details[$curr_request] = $this->findMapContentByName('neighborhood',$map_array->results[0]->address_components);
                                    
                                break;
                                
                            }
                            
                        }
                        
                        //We check to see if they want the types listed for this location
                        if($types)
                        {
                            
                            foreach($map_array->results[0]->types AS $curr_type)
                            {
                                
                                //We put the type name in proper format
                                $type_name = ucwords(str_replace('_',' ',strtolower(trim($curr_type))));
                                
                                //We now add the type to the type list
                                $map_types[] = $type_name;
                                
                            }
                            
                            //We now add the location types to the map details array
                            $map_details['types'] = $map_types;
                            
                        }
                        
                    }else
                    {
                        
                        //We return the error back to the script
                        $map_details['error'] = $map_content['error'];
                        
                    }
                    
                }
                
                return $map_details;
                
            }
            
        }
        
        private function generateGoogleMapURL($lat,$lon)
        {
            
            //We return the URL needed to get the location details based on lat/lon
            return 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' .urlencode($lat) .',' .urlencode($lon) .'&sensor=false&key=' .urlencode($this->apikey);
            
        }
        
        private function getGoogleMapData($url)
        {
            
            //We generate variables needed for this method
            $data = '';
            $error = FALSE;
            
            //We first check to see if curl is available, as this is the preferred method
            if(function_exists('curl_version'))
            {
                
                try
                {
                    
                    //We download the content from this map URL
                    $handle = curl_init();
                    curl_setopt($handle,CURLOPT_URL,$url);
                    curl_setopt($handle,CURLOPT_RETURNTRANSFER,TRUE);
                    $data = curl_exec($handle);
                    
                    //We close the CURL connection
                    curl_close($handle);
                    
                }catch(Exception $e)
                {
                    
                    //We pass the error caught from trying to download the map URL content
                    $error = trim($e->getMessage());
                    
                }
                
            //We check to see if fopen is turned on
            }else if(ini_get('allow_url_fopen'))
            {
                
                try
                {
                    
                    //We download the content from the map URL
                    $data = file_get_contents($url);
                
                }catch(Exception $e)
                {
                    
                    //We pass the error caught from trying to download the map URL content
                    $error = trim($e->getMessage());
                
                }
                
            }
            
            return array('data' => $data,'error' => $error);            
            
        }
        
        private function findMapContentByName($type,$array,$short_name = FALSE)
        {
            
            //We go through the array of values
            foreach($array AS $value)
            {
                
                //If we find the type they are looking for, we return the value needed
                if(in_array($type,$value->types))
                {
                    
                    return ($short_name) ? trim($value->short_name) : trim($value->long_name);
                
                }
            
            }
        
        }
    
    }