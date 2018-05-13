<?php
	//For Cross Origion Access The below section should be uncommented. If API is getting accessed from Localhost the below section should be commented.

	/*header("Access-Control-Allow-Origin:*");
	header("Access-Control-Allow-Methods : POST, PUT, GET, OPTIONS, DELETE");
	header("Access-Control-Allow-Headers:Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,Authorization,Content-Type,X-Token");*/
	
	//ini_set('display_errors',1);
	//ini_set('display_errors', 'On');
	//set_error_handler("var_dump");
	class API{
	
		public $data = "";
		
		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "taxi";
		
		private $db = NULL;
	
		public function __construct(){
			$this->dbConnect();// Initiate Database connection
		}
		
		/*
		 *  Database connection 
		*/
		private function dbConnect(){
			$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			if($this->db)
				mysql_select_db(self::DB,$this->db);
		}
		
		//This Function will convert Array Data To XML Format.
		private function array2xml($array, $xml = false){
			if($xml === false){
				$xml = new SimpleXMLElement('<root/>');
			}
			foreach($array as $key => $value){
				if(is_array($value)){
					$this->array2xml($value, $xml->addChild($key));
				}else{
					$xml->addChild($key, $value);
				}
			}
			return $xml->asXML();
		}
		
		//This Function will convert Array Data To JSON Format.
		private function encode_response($response,$format){
			if($format=="XML"){
				header('Content-type: text/xml');
				$response=$this->array2xml($response);
				print_r($response);
			}else{
				header('Content-Type: application/json');
				return json_encode($response);
			}
		}

		private function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
			// Calculate the distance in degrees
			$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
		 
			// Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
			switch($unit) {
				case 'km':
					$distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
					break;
				case 'mi':
					$distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
					break;
				case 'nmi':
					$distance =  $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
			}
			return round($distance, $decimals);
		}
		
		//The API processing will start with this function.
		public function process_api(){
			//Checking if Format parameter is passed in API URL or not. If Format parameter is not passed Error MSG will be sent as response.
			if(!isset($_GET["format"]) || $_GET["format"]==""){
				$response=array("status" => "Failed", "status_code" => 401, "error_message" => "Format Is Missing / Invalid Format");
				print_r($this->encode_response($response,"XML"));
				//exit;
			}else{
				//Getting POST Content
				if($_GET["format"]=="XML"){
					$content = simplexml_load_string(file_get_contents("php://input"));
				}else{
					$content = json_decode(file_get_contents("php://input"));
				}

				//Checking If Username is passed or not. If not Authentication Failed MSG will be sent as response.
				if(!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] == ''){
					$response=array("status" => "Failed", "status_code" => 401, "error_message" => "Authentication Failed. Please check the Username / password");
					print_r($this->encode_response($response,$_GET["format"]));
					//exit;
				}else{
					//Checking if User is present in Database. If result is true we will check for TYPE of API. 
					$login_response=mysql_num_rows(mysql_query("SELECT * FROM LoginDetails WHERE Username='".$_SERVER['PHP_AUTH_USER']."'"));
					if($login_response==1){
						//Setting Current Date and Time.
						$today=date('d-m-Y');
						$time=date('H:i:s');
						//Checking If TYPE is passed or not. If Found Respective CASE will be Excecuted 
						if(isset($content->type)){
							switch ($content->type) {
								case "LOGIN":
										$query=mysql_query("SELECT * FROM LoginDetails WHERE Username='".$_SERVER['PHP_AUTH_USER']."' AND Password='".md5($_SERVER['PHP_AUTH_PW'])."'");
										if(mysql_num_rows($query)==1){
											$response=array("status" => "Success", "status_code" => 200, "message" => "Login Success");
											echo $this->encode_response($response,$_GET["format"]);
											//exit;
										}else{
											$response=array("status" => "Failed", "status_code" => 401, "message" => "Invalid Credentials");
											echo $this->encode_response($response,$_GET["format"]);
											//exit;
										}
									break;
								case "GET_CAR_TYPES":
										$query=mysql_query("SELECT * FROM CarType");
										$car_types=array();
										while($car_type=mysql_fetch_assoc($query)){
											$car_types[]=$car_type;
										}

										$response=array("status" => "Success", "status_code" => 200, "car_types" => $car_types);
										echo $this->encode_response($response,$_GET["format"]);
										//exit;
									break;
								case "GET_NEAR_BY_CABS":
										if(!isset($content->latitude) || $content->latitude == '' || !isset($content->longitude) || $content->longitude == ''){
											$response=array("status" => "Failed", "status_code" => 401, "message" => "Latitude / Longitude detailas are missing.");
											echo $this->encode_response($response,$_GET["format"]);
										}else{
											//Below Query will Calculate the Distance an give the list of driver within the specified distance.
											$query=mysql_query("SELECT `Id`, `FirstName`, `LastName`, `CarName`, `Latitude`, `Longitude`, (SQRT( POW(69.1 * (`Latitude` - ".$content->latitude."), 2) + POW(69.1 * (".$content->longitude." - `Longitude`) * COS(`Latitude` / 57.3), 2)))*1.60934 AS distance FROM DriverDetails WHERE CarTypeId=".$content->car_type." AND OnTrip='0' HAVING distance <= ".$content->km_radius." ORDER BY distance");
											$cabs=array();
											while($cab=mysql_fetch_assoc($query)){
												$cabs[]=$cab;
											}

											$response=array("status" => "Success", "status_code" => 200, "cabs" => $cabs);
											echo $this->encode_response($response,$_GET["format"]);
										}
									break;
								case "ASSIGN_CAB":
										if(!isset($content->customer_name) || $content->customer_name == '' || !isset($content->customer_number) || $content->customer_number == '' || !isset($content->customer_latitude) || $content->customer_latitude == '' || !isset($content->customer_longitude) || $content->customer_longitude == '' || !isset($content->driver_id) || $content->driver_id == ''){
											$response=array("status" => "Failed", "status_code" => 401, "message" => "Customer Name / Customer Number / Customer Latitude / Customer Longitude / Driver Id detailas are missing.");
											echo $this->encode_response($response,$_GET["format"]);
										}else{
											//Generating Booking ID
											$booking_id="TX".rand(11111111,99999999);

											//Inserting the customer details into Bookings Table
											mysql_query("INSERT INTO Bookings (`DriverId`, `BookingId`, `CustomerName`, `CustomerNumber`, `CustomerStartLatitude`, `CustomerStartLongitude`) VALUES ('".$content->driver_id."', '".$booking_id."', '".$content->customer_name."', '".$content->customer_number."', '".$content->customer_latitude."', '".$content->customer_longitude."')");

											//Marking Driver as not available.
											mysql_query("UPDATE DriverDetails SET `OnTrip`='1' WHERE Id='".$content->driver_id."'");

											//Sending Booking ID in Response.
											$response=array("status" => "Success", "status_code" => 200, "message" => "Cab Assigned Successfully", "booking_id" => $booking_id);
											echo $this->encode_response($response,$_GET["format"]);
										}
									break;
								case "START_TRIP":
										if(!isset($content->booking_id) || $content->booking_id == ''){
											$response=array("status" => "Failed", "status_code" => 401, "message" => "Booking Id missing.");
											echo $this->encode_response($response,$_GET["format"]);
										}else{
											//Getting todays Date and Time.
											$date_time=$today." ".$time;

											//Updating the Start time in Bookings Table.
											mysql_query("UPDATE Bookings SET `StartTime`='".$date_time."' WHERE BookingId='".$content->booking_id."'");
											$response=array("status" => "Success", "status_code" => 200, "message" => "Trip Started Successfully.");
											echo $this->encode_response($response,$_GET["format"]);
										}
									break;
								case "END_TRIP":
										if(!isset($content->booking_id) || $content->booking_id == '' || !isset($content->customer_latitude) || $content->customer_latitude == '' || !isset($content->customer_longitude) || $content->customer_longitude == '' || !isset($content->trip_time) || $content->trip_time == ''){
											$response=array("status" => "Failed", "status_code" => 401, "message" => "Booking Id / Customer Latitude / Customer Longitude / Trip Time details are missing.");
											echo $this->encode_response($response,$_GET["format"]);
										}else{
											//Getting the Booking details and Rate Card from DB
											$booking_details=mysql_fetch_assoc(mysql_query("SELECT Bookings.StartTime,Bookings.CustomerStartLatitude,Bookings.CustomerStartLongitude,Bookings.DriverId,CarType.Type,CarType.RatePerMinute,CarType.RatePerKm FROM Bookings JOIN DriverDetails ON Bookings.DriverId=DriverDetails.Id JOIN CarType ON CarType.Id=DriverDetails.CarTypeId WHERE BookingId='".$content->booking_id."'"));

											//Getting the Trip Start time and adding X travel time given by admin in frontend. In Realtime the situation will be different. 
											$trip_start_time = $booking_details["StartTime"];
											$endtime = strtotime( $trip_start_time ) + (60*$content->trip_time);
											$endtime = date("d-m-Y H:i:s", $endtime);

											//Updating End Time and Destination lat long details.
											mysql_query("UPDATE Bookings SET `CustomerEndLatitude`='".$content->customer_latitude."', `CustomerEndLongitude`='".$content->customer_longitude."', `EndTime`='".$endtime."' WHERE BookingId='".$content->booking_id."'");

											//Marking Driver as Available and Updatig the Location details of Driver.
											mysql_query("UPDATE DriverDetails SET `Latitude`='".$content->customer_latitude."', `Longitude`='".$content->customer_longitude."', OnTrip='0' WHERE Id='".$booking_details["DriverId"]."'");

											// Calculate the distance in KM using Haversine formula. Tried pythagorean formula but not getting properdistance. Below is the example of pythagorean formula
											//$distance=SQRT(POW((12.903517 - 12.916516),2) + POW((POW(77.592029,2) - 77.609997),2));
											$distance_in_km=$this->distanceCalculation($booking_details["CustomerStartLatitude"],$booking_details["CustomerStartLongitude"],$content->customer_latitude,$content->customer_longitude);

											//CAlculating Time Price
											$time_price=($content->trip_time*$booking_details["RatePerMinute"]);

											//Calcualting Kilometer Price
											$km_price=($distance_in_km*$booking_details["RatePerKm"]);

											//Calculating Total Price
											$total_price=($time_price+$km_price);

											//Crating Invoice
											mysql_query("INSERT INTO BookingInvoice (`BookingId`, `TimeTaken`, `KmTravelled`, `TimePrice`, `KmPrice`, `TotalPrice`) VALUES('".$content->booking_id."', '".$content->trip_time."', '".$distance_in_km."', '".$time_price."', '".$km_price."', '".$total_price."')");
											$response=array("status" => "Success", "status_code" => 200, "message" => "Trip Ended Successfully.", "Total_distance_travelled" => $distance_in_km, "time_price" => $time_price, "km_price" => $km_price, "total_price" => $total_price);
											echo $this->encode_response($response,$_GET["format"]);
										}
									break;
								//If Type is not found in any of the CASE then Invlid Type Error will be sent as Response.
								default:
									$response=array("status" => "Failed", "status_code" => 401, "error_message" => "Invalid Type");
									echo $this->encode_response($response,$_GET["format"]);
									//exit;
							}
						//If Type paramater is not passed then Type missing error will be sent as Response.
						}else{
							$response=array("status" => "Failed", "status_code" => 401, "error_message" => "Type Is Missing");
							echo $this->encode_response($response,$_GET["format"]);
							//exit;
						}
					//If Username passed in API is not found then Authentication Falied MSG will be sent as Response.
					}else{
						$response=array("status" => "Failed", "status_code" => 401, "error_message" => "Authentication Failed. Please check the Username / password..");
						echo $this->encode_response($response,$_GET["format"]);
						//exit;
					}
				}
			}
		}
	}
		
	$api = new API;
	$api->process_api();
?>