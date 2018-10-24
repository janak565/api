<?php 
require_once('constants.php');
	class User {
		private $id;
		private $name;
		private $email;
		private $password;
		private $dob;
		private $active;
		private $created_on;
		private $updated_on;
		private $profile_image;
		private $tableName = 'users';
		private $dbConn;

		function setId($id) { $this->id = $id; }
		function getId() { return $this->id; }
		function setName($name) { $this->name = $name; }
		function getName() { return $this->name; }
		function setEmail($email) { $this->email = $email; }
		function getEmail() { return $this->email; }
		function setDob($dob) { $this->dob = $dob; }
		function getDob() { return $this->dob; }
		function setPassword($password) { $this->password = $password; }
		function getPassword() { return $this->password; }
		function setActive($active) { $this->active = $active; }
		function getActive() { return $this->active; }
		function setCreatedOn($created_on) { $this->created_on = $created_on; }
		function getCreatedOn() { return $this->created_on; }
		function setUpdatedOn($updated_on) { $this->updated_on = $updated_on; }
		function getUpdatedOn() { return $this->updated_on; }
		function setProfileImage($profile_image) { $this->profile_image = $profile_image; }
		function getProfileImage() { return $this->profile_image; }

		public function __construct() {
			$db = new DbConnect();
			$this->dbConn = $db->connect();
		}

		public function getAllCustomers() {
			$stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName);
			$stmt->execute();
			$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $customers;
		}

		public function getUserDetailsById() {

			$sql = "SELECT 
						u.*
					FROM users u 
					WHERE u.id = :id";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':id', $this->id);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			return $user;
		}
		

		public function insert() {
			try {
			$sql = 'INSERT INTO ' . $this->tableName . '(id, name, email, password, dob, active, created_on,profile_image) VALUES(null, :name, :email, :password, :dob, :active, :createdOn,:profile_image)';

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':name', $this->name);
			$stmt->bindParam(':email', $this->email);
			$stmt->bindParam(':password', $this->password);
			$stmt->bindParam(':dob', $this->dob);
			$stmt->bindParam(':active', $this->active);
			$stmt->bindParam(':createdOn', $this->created_on);
			$stmt->bindParam(':profile_image', $this->profile_image);
			
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}catch (PDOException $e){
			
			$data = new Api();
			if($e->getCode()==23000)
			{
			   	$data->throwError($e->getCode(), $e->getMessage());
		
			}
		}
			
		}

		public function update() {
			try {
				$sql = "UPDATE $this->tableName SET";
				if( null != $this->getName()) {
					$sql .=	" name = '" . $this->getName() . "',";
				}

				if( null != $this->getDob()) {
					$sql .=	" dob = '" . $this->getDob() . "',";
				}

				if( null != $this->getProfileImage()) {
					$sql .=	" profile_image = '" . $this->getProfileImage() . "',";
				}

				$sql .=	"updated_on = :updatedOn
						WHERE 
							id = :userId";
								
				$stmt = $this->dbConn->prepare($sql);
				$stmt->bindParam(':userId', $this->id);
				$stmt->bindParam(':updatedOn', $this->updated_on);
				if($stmt->execute()) {
					return true;
				} else {
					return false;
				}
			}catch (PDOException $e){
		   	
				$data = new Api();
		   		$data->throwError($e->getCode(), $e->getMessage());
		}
		}

		public function delete() {
			try {
					
					$stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userId');
					$stmt->bindParam(':userId', $this->id);
					
					if($stmt->execute()) {
						return true;
					} else {
						return false;
					}
			
			}catch (PDOException $e){
		   	
				$data = new Api();
		   		$data->throwError($e->getCode(), $e->getMessage());
			}
	}
}	
 ?>