<?php
	
	function getToken(){
		$curlparam  = array("name" => "generateToken", "param"=>'{"email":"kanani.janak133@gmail.com","pass":"1234567"}'); 
		$curlheader = array();
		$url = 'http://localhost/api/jwt-api/index.php';
		$urltokendata = callfun($curlparam,'',$curlheader,$url);
		if(isset($urltokendata->error) && !empty($urltokendata->error)){
			//return $urltokendata->error->message;
			return 'NOTFOUNDTOKEN';
		}else{
			return $urltokendata->resonse->result->token;
		}
	}
	function AddCustomer(){

		$curlparam  = array("name" => "addCustomer", "param"=>'{"name":"Disha kanani","email":"kk@gmail.com","mobile":"123456789078","addr":"1234567"}'); 
		$curlheader = array();
		$url = 'http://localhost/api/jwt-api/index.php';
		$token = getToken();
		$curlheader = array(
   			 'Content-Type: multipart/form-data',
    		'Authorization: Bearer '.$token);
		$urltokendata = callfun($curlparam,'true',$curlheader,$url);
		
		if(isset($urltokendata->error) && !empty($urltokendata->error)){
			if($urltokendata->error->code==23000){
				return "EMAILDUPLICATE";
			}else{
				return "FAIL";
			}
		}else{
			return "SUCCESS";
		}
	}

	function UpdateCustomer(){
		
		$curlparam  = array("name" => "updateCustomer", "param"=>'{"name":"Disha kanani","customerId":"28","mobile":"123552","addr":"South Bopal"}'); 
		$curlheader = array();
		$url = 'http://localhost/api/jwt-api/index.php';
		$token = getToken();
		$curlheader = array(
   			 'Content-Type: multipart/form-data',
    		'Authorization: Bearer '.$token);
		$urltokendata = callfun($curlparam,'true',$curlheader,$url);
		print_r($urltokendata);
		if(isset($urltokendata->error) && !empty($urltokendata->error)){
			return 'FAIL';
		}else{
			return 'SUCCESS';
		}	
	}

	function DeleteCustomer(){
		
		$curlparam  = array("name" => "deleteCustomer", "param"=>'{"customerId":"28"}'); 
		$curlheader = array();
		$url = 'http://localhost/api/jwt-api/index.php';
		$token = getToken();
		$curlheader = array(
   			 'Content-Type: multipart/form-data',
    		'Authorization: Bearer '.$token);
		$urltokendata = callfun($curlparam,'true',$curlheader,$url);
		
		if(isset($urltokendata->error) && !empty($urltokendata->error)){
			if($urltokendata->error->message=='This record is already deleted')
			{
				return 'RECORDALREADYDELETE';	
			}else{

				return 'FAIL';
			}	
			
		}else{
			return 'SUCCESS';
		}	
	}

	function GetAllCustomer(){
		
		$curlparam  = array("name" => "getAllCustomerDetails", "param"=>'{}'); 
		$curlheader = array();
		$url = 'http://localhost/api/jwt-api/index.php';
		$token = getToken();
		$curlheader = array(
   			 'Content-Type: multipart/form-data',
    		'Authorization: Bearer '.$token);
		$urltokendata = callfun($curlparam,'true',$curlheader,$url);
		print_r($urltokendata);
		exit;
		if(isset($urltokendata->error) && !empty($urltokendata->error)){
			if($urltokendata->error->message=='This record is already deleted')
			{
				return 'RECORDALREADYDELETE';	
			}else{

				return 'FAIL';
			}	
			
		}else{
			return 'SUCCESS';
		}	
	}

	echo GetAllCustomer();






 // print_r($data);
 // exit;                                                                   
//$data_string = json_encode($data); 
// $header = array(++
//     'Content-Type: multipart/form-data',
//     'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NDAyMDY5NjAsImlzcyI6ImxvY2FsaG9zdCIsImV4cCI6MTU0MDIwNzg2MCwidXNlcklkIjoiMyJ9.DtBsiPnpQhpUHI_G6LIUX2bsGxcUxNWAiVN7wduO1Ic');
//print_r($urltokendata);

//if($urltokendata)

function callfun($param,$authorinationflag,$header,$url){
  	$ch = curl_init (); 
 	
 	$setheader = array(); 
 	if($authorinationflag){
		$setheader = $header;	
	}
	if(isset($url) && !empty($url)){
		$seturl = $url;
	}else{
		$seturl = 'http://localhost/api/jwt-api/index.php';
	}
	
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$seturl);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $setheader);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	$result = curl_exec($ch);
	if (curl_error($ch)) {
    	$error_msg = curl_error($ch);
	}
	curl_close($ch);

	if (isset($error_msg)) {
		return $error_msg; 
	}else{
		return json_decode($result); 
	}
}  
?>