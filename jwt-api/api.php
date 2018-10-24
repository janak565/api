<?php 

	class Api extends Rest {
		
		public function __construct() {
			parent::__construct();
		}

		public function generateToken() {

			//check validation of parameter
			if(isset($this->param['email'])){
				$email = $this->validateParameter('email', $this->param['email'], STRING);
			}else{
				$email = $this->validateParameter('email','', STRING);
			}

			if(isset($this->param['pass'])){
				$pass = $this->validateParameter('pass', $this->param['pass'], STRING);
			}else{
				$pass = $this->validateParameter('pass','', STRING);
			}
			
			try {

				$stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":pass", $pass);
				$stmt->execute();
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!is_array($user)) {
					$this->returnResponse(INVALID_USER_PASS, "Email or Password is incorrect.");
				}

				if( $user['active'] == 0 ) {
					$this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
				}

				$paylod = [
					'iat' => time(),
					'iss' => 'localhost',
					'exp' => time() + (15*60),
					'userId' => $user['id']
				];

				$token = JWT::encode($paylod, SECRETE_KEY);
				
				$data = ['token' => $token];
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			} catch (Exception $e) {
				$this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
			}
		}

		public function addCustomer() {
			$name = $this->validateParameter('name', $this->param['name'], STRING, false);
			$email = $this->validateParameter('email', $this->param['email'], STRING, false);
			$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
			$mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);

			$cust = new Customer;
			$cust->setName($name);
			$cust->setEmail($email);
			$cust->setAddress($addr);
			$cust->setMobile($mobile);
			$cust->setCreatedBy($this->userId);
			$cust->setCreatedOn(date('Y-m-d'));

			if(!$cust->insert()) {
				$message = 'Failed to insert.';
			} else {
				$message = "Inserted successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}

		public function getCustomerDetails() {
			// $upload_image_param = array();
			// $upload_image_param['uploadpath']= IMMAGE_UPLODE_PARTH ;
			// $upload_image_param['displaypath']= IMMAGE_DISPLAY_PARTH ;
			// $upload_image_param['maxsize'] = 6;
			// $upload_image_param['filetype'] = array('png','jpeg','gif');
			// $upload_image_param['limit'] = 1;
			// if($this->UploadFile($_FILES['image'],$upload_image_param)){
			// 	echo "sucess";
			// 	exit;
			// }else{
			// 	echo "fail";
			// }

			// print_r($_FILES);
			// exit;
			// if(isset($this->param['customerId'])){
			// 	$customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);
			// }else{
			// 	$customerId = $this->validateParameter('customerId', '', INTEGER);
			// }
			
			$cust = new Customer;
			$cust->setId($customerId);
			$customer = $cust->getCustomerDetailsById();
			if(!is_array($customer)) {
				$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found.']);
			}

			$response['customerId'] 	= $customer['id'];
			$response['cutomerName'] 	= $customer['name'];
			$response['email'] 			= $customer['email'];
			$response['mobile'] 		= $customer['mobile'];
			$response['address'] 		= $customer['address'];
			$response['createdBy'] 		= $customer['created_user'];
			$response['lastUpdatedBy'] 	= $customer['updated_user'];
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		public function getAllCustomerDetails() {
			$cust = new Customer;
			$customer = $cust->getAllCustomers();
			if(!is_array($customer)) {
				$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found.']);
			}
			$this->returnResponse(SUCCESS_RESPONSE, $customer);
		}

		public function updateCustomer() {
			
			if(isset($this->param['customerId'])){
				$customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);
			}else{
				$customerId = $this->validateParameter('customerId',' ', INTEGER);
			}

			if(isset($this->param['name']) && !empty($this->param['name'])){
				$name = $this->validateParameter('name', $this->param['name'], STRING, false);
			}else{
				$name = NULL;
			}

			if(isset($this->param['addr']) && !empty($this->param['addr'])){
				$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
			}else{
				$addr = NULL;
			}

			if(isset($this->param['mobile']) &&  !empty($this->param['mobile'])){
				$mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);
			}else{
				$mobile = NULL;
			}
			
			$cust = new Customer;
			$cust->setId($customerId);
			$cust->setName($name);
			$cust->setAddress($addr);
			$cust->setMobile($mobile);
			$cust->setUpdatedBy($this->userId);
			$cust->setUpdatedOn(date('Y-m-d'));

			if(!$cust->update()) {
				$message = 'Failed to update.';
			} else {
				$message = "Updated successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}

		public function deleteCustomer() {
			$customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);

			$cust = new Customer;
			$cust->setId($customerId);

			if(!$cust->delete()) {
				$message = 'Failed to delete.';
			} else {
				$message = "deleted successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}

		public function UploadFile($files,array $array)
		{

			$uploaded_files = array();

			if(isset($files) && $files['name']!=""){

				//CHANGING PERMISSION OF THE DIRECTORY
				@chmod($array['uploadpath'], 0755);

				if($array['limit']==0 || $array['limit']>@count($files['name'])){
					$array['limit']=@count($files['name']);
				}

				for($a=0;$a<$array['limit'];$a++){

					if(@$array['maxsize']<=0) {$array['maxsize']=5000;}
					$allowedfiletypes = $array['filetype'];
					$max_size = $array['maxsize']*1024*1024;	//in KB

					$filename="";
					if($array['limit']>1){

						$currentfile_extension = end(@explode(".",$files['name'][$a]));

						if(in_array(strtolower($currentfile_extension),$allowedfiletypes)){

							$filename = date("YmdHis").rand(1000,9999).".".$currentfile_extension;

							if($files['size'][$a]<$max_size){	

								if(@move_uploaded_file($files['tmp_name'][$a], $array['uploadpath'].$filename)){

									$uploaded_files[]= $array['displaypath'].$filename;

									//CHANGIN FILE PERMISSION
									@chmod($array['uploadpath'].$filename, 0755);
									return array('response'=>'IMAGESAVE','flagimageupload'=>'sucess');
								}else{
									return array('response'=>'IMAGENOTSAVE','flagimageupload'=>'fail');
								}
							}else{
								return array('response'=>'LIMITFORUPLOADIMAGE','flagimageupload'=>'fail');
							}
						}else{
							return array('response'=>'IMAGENOTVALIDATE','flagimageupload'=>'fail');
						}
					} else {
						
						$arrayimagename = @explode(".",$files['name']);
						$currentfile_extension = end($arrayimagename);

						if(in_array(strtolower($currentfile_extension),$allowedfiletypes)){
							
							$filename = date("YmdHis").rand(1000,9999).".".$currentfile_extension;
							
							if($files['size']<$max_size){

								if(@move_uploaded_file($files['tmp_name'], $array['uploadpath'].$filename)){

									$uploaded_files[]=$array['displaypath'].$filename;
									
									@chmod($array['uploadpath'].$filename, 0755);
									return array('response'=>'IMAGESAVE','flagimageupload'=>'sucess');
								}else{
									return array('response'=>'IMAGENOTSAVE','flagimageupload'=>'fail');
								}
							}else{
								return array('response'=>'LIMITFORUPLOADIMAGE','flagimageupload'=>'fail');
							}
						}else{
							return array('response'=>'IMAGENOTVALIDATE','flagimageupload'=>'fail');
						}
					}
				}
			}
			return $uploaded_files;
			}

			public function DeleteImageFile(array $array)
			{
				$deletepath = $array['deletepath'];
				$deletefilename = $array['deletefilename'];

				if(file_exists($deletepath.$deletefilename)){
					unlink($deletepath.$deletefilename);
					return true;
				}else{
					return false;
				}
			}			

			public function addUser() {
			$name = $this->validateParameter('name', $this->param['name'], STRING, false);
			$email = $this->validateParameter('email', $this->param['email'], STRING, false);
			$password = $this->validateParameter('password', $this->param['password'], STRING, false);
			$dob = $this->validateParameter('dob', $this->param['dob'], STRING, false);
			$active = $this->validateParameter('active', $this->param['active'], INTEGER, false);
			$profile_image = $this->validateParameter('profile_image', $this->param['profile_image'], STRING, false);

			$user = new User;
			$user->setName($name);
			$user->setEmail($email);
			$user->setDob($dob);
			$user->setPassword($password);
			$user->setActive($active);
			$user->setProfileImage($profile_image);
			$user->setCreatedOn(date('Y-m-d'));

			if(!$user->insert()) {
				$message = 'Failed to insert.';
			} else {
				$message = "Inserted successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}

		public function updateUser() {
			$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
			$name = $this->validateParameter('name', $this->param['name'], STRING, false);
			$dob = $this->validateParameter('dob', $this->param['dob'], STRING, false);
			$profile_image = $this->validateParameter('profile_image', $this->param['profile_image'], STRING, false);

			$user = new User;
			$user->setId($userId);
			$user->setName($name);
			$user->setDob($dob);
			$user->setProfileImage($profile_image);
			$user->setUpdatedOn(date('Y-m-d H:i:s'));

			if(!$user->update()) {
				$message = 'Failed to update.';
			} else {
				$message = "Updated successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}

		public function deleteUser() {
			$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);

			$user = new User;
			$user->setId($userId);

			if(!$user->delete()) {
				$message = 'Failed to delete.';
			} else {
				$message = "deleted successfully.";
			}

			$this->returnResponse(SUCCESS_RESPONSE, $message);
		}
		public function getUserDetails() {
			// $upload_image_param = array();
			// $upload_image_param['uploadpath']= 'C:/xampp/htdocs/api/jwt-api/customer_images/' ;
			// $upload_image_param['maxsize'] = 6;
			// $upload_image_param['filetype'] = array('png','jpeg','gif');
			// $upload_image_param['limit'] = 1;
			// if($this->UploadFile($_FILES['image'],$upload_image_param)){
			// 	echo "sucess";
			// 	exit;
			// }else{
			// 	echo "fail";
			// }

			// print_r($_FILES);
			// exit;
			if(isset($this->param['userId'])){
				$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
			}else{
				$userId = $this->validateParameter('userId', '', INTEGER);
			}
			
			$user = new User;
			$user->setId($userId);
			$user = $user->getUserDetailsById();
			if(!is_array($user)) {
				$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'User details not found.']);
			}

			$response['userId'] 	= $user['id'];
			$response['name'] 	= $user['name'];
			$response['email'] 			= $user['email'];
			$response['dob'] 		= $user['dob'];
			$response['profile_image'] 		= $user['profile_image'];
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}




	}


	
 ?>