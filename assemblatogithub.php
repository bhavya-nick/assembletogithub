<?php 

/**
* @license    GNU/GPL, see LICENSE.php
* @contact    bhavya.nick@gmail.com
* @copyright  Copyright (C) 2009 - 2013 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license    GNU/GPL, see LICENSE.php 
 */

/**
 * This script will create issues(tickets) in github account from csv of assembla ticket
 *
 * All you need to do is export the Assembla ticket list (CSV format only)
 * and run this script
 * 
 * This is not complete script, it will just import the tickets, nothing more than that
 * for importing assignee, status and other details you need to map all the field values with relevant name is github
 * 
 * Mapping means assignee=XXX in assembla is assignee=ZZ in github
 *  
 *  @author bhavya
 */

$data = csv_to_array('CSV_FILE_PATH', ',');

$username='xxxxxxxxx'; //change this username to your github account username
$password='xxxxxxxxxx'; // attached password

$organization	=  'xxxxxxxx'; //if creating tickets in repository which lies under an organization then it is mendatory to add the organization name 
$repo		    =  'xxxxx'; // repository name in which you need to import tickets

$owner		=  empty($organization) ? $username : $organization;
$url 		= "https://api.github.com/repos/$owner/$repo/issues";

foreach($data as $record){
	$issue = prepareIssue($record);
	requestAPI($url, "POST", $username, $password, $issue);
}


function csv_to_array($filename='', $delimiter=',')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}

function requestAPI($url, $method, $username, $password, $data=array()) 
	{		
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    
	    if($method == "POST"){
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    } 
	    
	    curl_setopt($ch, CURLOPT_HEADER, true);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);   
	    $response = curl_exec($ch);
	    
	    $header_size 		 = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
      	    $result['header'] 	 = substr($response, 0, $header_size);
      	    $result['body'] 	 = substr( $response, $header_size );
      	    $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
       	    $result['last_url']  = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
       	
            curl_close($ch);
	    return $result;
	}
	
	function prepareIssue($data)
	{
		$issue		  		=  array();
		$issue['title']  		=  $data['summary'];
//		$issue['body']  		=  $data['summary'];
//		$issue['assignee']	 	=  $data['assigned_to'];
//		$issue['milestone']	  	=  
//		$labels				=  mapping required
		
		//unset empty values
		foreach ($issue as $key => $param){
			if(empty($param)){
				unset($issue[$key]);
			}
		}

		return json_encode($issue);
	}
