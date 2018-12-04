	<?php 
	

// ==========================================
// Jobsplus : Mobile Verification 
// ==========================================
//Author: Ilham Safeek
// ==========================================

ini_set('error_log', 'sms-app-error.log');

function __autoload($class_name) {
    include $class_name . '.php';
}



require 'ideamart.php';
include_once 'KLogger.php';
include_once 'cass-conf.php';



//Get data from configuration file
$url = $SUBSCRIPTION_URL;
$smsurl=$SMS_URL;
$directdebiturl =$DIRECT_DEBIT_SENDER_URL;
$querybalanceurl = $QUERY_BALANCE_SENDER_URL;
$applicationId = $APP_ID;
$password = $PASSWORD;
$paymentInstrumentName = $PAYMENT_INSTRUMENT_NAME;
$currency = $CURRENCY;
$accountId = $ACCOUNT_ID;
$externaltrxId = $EXTERNAL_TRX_ID;
$registrationcharge = $REGISTRATION_CHARGE;

   $logger = new Logger();
    $blogger = new KLogger ( "cass_debug.log" , KLogger::DEBUG );
   
   
   
   
   $dbhost = 'localhost';
   $dbuser = 'jobsplus_admin';
   $dbpass = '786.L0V3.786';
   $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   
  
   if(! $conn )
   {
      die('Could not connect: ' . mysql_error());
   }
    
	 
   
   /////////////////////////////////////////////////////////
	function abc(){
	
	session_start();
	$sessionId =$_SESSION['USERID'];
	
	$dbhost = 'localhost';
   $dbuser = 'jobsplus_admin';
   $dbpass = '786.L0V3.786';
   $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   if(! $conn )
   {
      die('Could not connect: ' . mysql_error());
	 
	  
   }
	
	

	
	
	$selectCode =  'SELECT * FROM `verifycode` '.'WHERE Id = "3"';
	   
	   
				mysql_select_db('jobsplus_dbase');
				$selected = mysql_query( $selectCode, $conn );
   
		if(! $selected )
			{
		die('Could not enter data: ' . mysql_error());
			}
   
	
			while($row = mysql_fetch_assoc($selected))
			{
   
					$getcode = $row['code'];
   
								}
				return $getcode;
   
  // mysql_close($conn);
						}
	
	
					$rans = abc();
	
	
	///////////////////////////////////////////////////////////////
	
	
   
   

try{


	// Creating a receiver and intialze it with the incomming data
	$receiver = new SMSReceiver(file_get_contents('php://input'));
	
	//Creating a sender
	$sender = new SMSSender( $smsurl, $applicationId, $password);
	
	// Setting up CAAS
	$cass = new DirectDebitSender( $directdebiturl,$applicationId, $password);
	
	
	$message = $receiver->getMessage(); // Get the message sent to the app
	$address = $receiver->getAddress();	// Get the phone no from which the message was sent 

	
   
   	 $requestId = $receiver->getRequestID(); // get the request ID
    	$applicationId = $receiver->getApplicationId(); // get application ID
    	$encoding = $receiver->getEncoding(); // get the encoding value
   	 $version = $receiver->getVersion(); // get the version







	$logger->WriteLog("[ message=$message , address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version ]");

	$subscriberId = $address;
	
	$blogger->LogDebug("QueryBalanceHandler : Received msisdn=".$subscriberId);


	


// Create the sender object server url
try {
    $bsender = new QueryBalanceSender($querybalanceurl);
    $jsonResponse = $bsender->queryBalance($applicationId, $password, $subscriberId, $paymentInstrumentName, $currency);
} catch (CassException $ex) {
    error_log("CASS query-balance ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
}

		//Get the response data from json
		$responseArray = json_decode($jsonResponse, true);
		$chargeableBalance = $responseArray['chargeableBalance'];
		$statusCode = $responseArray['statusCode'];
		$statusDetail = $responseArray['statusDetail'];
		$accountStatus = $responseArray['accountStatus'];
		$accountType = $responseArray['accountType'];
	
	

	
	// keyword<space> operation
	list($key , $opt)= explode(" ",$message);
	

	if ($opt==$rans) {

		
		try {
		
		///////////////////////////////////////////////
		//currency conversion 
		
		
		function convertCurrency($amount, $from, $to){
    $urls  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
    $data = file_get_contents($urls);
    preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
    $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
    return round($converted, 3);
  }

  # Call function  
  $chargingamount = convertCurrency($registrationcharge, "USD", "LKR");
//////////////////////////////////////////////////////
		
		
		
		
		if($chargeableBalance>=$chargingamount){
			$s = substr($address, 4, 14);
			
			
			
			


		$sql1 = 'SELECT * FROM `members` '.'WHERE mobilenumber = "'.$s.'"';
		mysql_select_db('jobsplus_dbase');
		
		if($result = mysql_query($sql1, $conn)){
		
		if(mysql_num_rows($result)){
		$response=$sender->sms("You have registered your mobile number already !. ",$address);
	
				} else {		

			
			
	$selectCode =  'SELECT * FROM `members` '.'WHERE USERID = "3"';
	   
	   
				mysql_select_db('jobsplus_dbase');
				$select = mysql_query( $selectCode, $conn );
   
		if(! $select )
			{
		die('Could not enter data: ' . mysql_error());
			}
   
	
	 while($row = mysql_fetch_assoc($select))
   {
   
   $mobileverified = $row['mobileverified'];
   
   }
			
		
			
		$sessionId =$_SESSION['USERID'];	

			
		$sql = 'UPDATE `members` '.' SET mobilenumber= "'.$s.'"  ,mobileverified= 1 '.' WHERE USERID = $sessionId';
	   
	   
				$retval = mysql_query( $sql, $conn );
   
		if(! $retval )
			{
		die('Could not enter data: ' . mysql_error());
			}
   
			if($mobileverified==0){
			//Subscription Request	 
		 

		$appid=$applicationId;
		$pw=$password;
		$sub = $address;


		$obj = new ideamart();
		$obj->subcribe($url,$appid,$pw,$address);

		 
		 
		$bal = $chargeableBalance - 1;
		$cass->cass($externaltrxId,$address,1);
		$response=$sender->sms('Your mobile number is verified ! You have charged Rs.'.$chargingamount.' for verification. Your available Balance is '.$bal , $address);
				
   
   
   

			}else{
			
			
			
			$response=$sender->sms('Your mobile number is Updated!' , $address);
			
			
			}
		
		//	mysql_close($conn);
		
		
			
				
				}
			}else{
			
		die('Could not enter data: ' . mysql_error());
		
			}	
				
		}else{
		
		$response=$sender->sms("Sorry, You need Rs.".$chargingamount." to verify your mobile number. kindly recharge your account. ",$address);
		
		}
		
		
		
		
		
	} catch (CassException $e) {
		$response=$sender->sms("You do not have enough money. Kindly recharge your account",$address);
	}

	
	
	
	}else{

		
		$response=$sender->sms('The code you entered is miss match !', $address);
		
	}

}catch(SMSServiceException $e){
	$logger->WriteLog($e->getErrorCode().' '.$e->getErrorMessage());
}

	mysql_close($conn);
	
	
	?>