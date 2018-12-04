<?php



/********* This is the Core Class **********/
class Core
{
	
	public function sendRequest($jsonStream,$url){

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
		return $res;

	}
}


/*******   Sender   ********/

class SMSReceiver{
	private $version;
	private	$applicationId;		
	private	$sourceAddress;	
	private $message;			
	private	$requestId;			
	private $encoding;			
	private $thejson;			
	
	public function __construct(){

		$array = json_decode(file_get_contents('php://input'), true);
        $this->sourceAddress = $array['sourceAddress'];
        $this->message = $array['message'];
        $this->requestId = $array['requestId'];
        $this->applicationId = $array['applicationId'];
        $this->encoding = $array['encoding'];
        $this->version = $array['version'];
				 if (!((isset($this->sourceAddress) && isset($this->message)))) {
					$response = array('statusCode'=>'E1312', 'statusDetail'=>'Request is Invalid.');
					
		}else{
			 // Success received response
            $responses = array("statusCode" => "S1000", "statusDetail" => "Success");
            header("Content-type: application/json");
            echo json_encode($responses);
            }
	}
	
	// Get the version of the incomming message
	public function getVersion(){
		return $this->version;
	}
	
	// Get the encoding of the incomming message
	public function getEncoding(){
		return $this->encoding;
	}
	
	// Get the Application of the incomming message
	public function getApplicationId(){
		return $this->applicationId;
	}

	// Get the address of the incomming message
	public function getAddress(){
		return $this->sourceAddress;
	}

	// Get the Message of the incomming request	
	public function getMessage(){
		return $this->message;
	}

	// Get the unique requestId of the incomming message	
	public function getRequestId(){
		return $this->requestId;
	}

	// Get the json
	public function getJson(){
		return $this->thejson;
	}
}



class SMSSender  extends Core{
	private $applicationId,
			$password,
			$charging_amount,
			$encoding,
			$version,
			$deliveryStatusRequest,
			$binaryHeader,
			$sourceAddress,
			$serverURL;
	
	/* Send the server name, app password and app id
	*	Dialog Production Severurl : HTTPS : - https://api.dialog.lk/sms/send
	*				     HTTP  : - http://api.dialog.lk:8080/sms/send
	*/		
	public function __construct($serverURL, $applicationId, $password)
	{
		if(!(isset($serverURL, $applicationId, $password)))
			throw new SMSServiceException('Request Invalid.', 'E1312');
		else {
			$this->applicationId = $applicationId;
			$this->password = $password;
			$this->serverURL = $serverURL;
		}
	}
	
	// Broadcast a message to all the subcribed users
	public function broadcast($message){
		return $this->sms($message, array('tel:all'));
	}
	
	// Send a message to the user with a address or send the array of addresses
	public function sms($message, $addresses){
		if(empty($addresses))
			throw new SMSServiceException('Format of the address is invalid.', 'E1325');
		else {
			$jsonStream = (is_string($addresses))?$this->resolveJsonStream($message, array($addresses)):(is_array($addresses)?$this->resolveJsonStream($message, $addresses):null);
			return ($jsonStream!=null)?$this->handleResponse( $this->sendRequest($jsonStream,$this->serverURL) ):false;
		
		}
	}
	
	private function handleResponse($jsonResponse){
	
		$statusCode = $jsonResponse->statusCode;
		$statusDetail = $jsonResponse->statusDetail;
		
		if(empty($jsonResponse))
			throw new SMSServiceException('Invalid server URL', '500');
		else if(strcmp($statusCode, 'S1000')==0)
			return true;
		else
			throw new SMSServiceException($statusDetail, $statusCode);
	}
	
	private function resolveJsonStream($message, $addresses){
		
		$messageDetails = array("message"=>$message,
	   	           				"destinationAddresses"=>$addresses
           					);
		
		if (isset($this->sourceAddress)) {
			$messageDetails= array_merge($messageDetails,array("sourceAddress" => $this->sourceAddress));   
		}
		
		if (isset($this->deliveryStatusRequest)) {
			$messageDetails= array_merge($messageDetails,array("deliveryStatusRequest" => $this->deliveryStatusRequest));
		}
		
		if (isset($this->binaryHeader)) {
			$messageDetails= array_merge($messageDetails,array("binaryHeader" => $this->binaryHeader));
		}	
		
		if (isset($this->version)) {
			$messageDetails= array_merge($messageDetails,array("version" => $this->version)); 
		}	
		
		if (isset($this->encoding)) {
			$messageDetails= array_merge($messageDetails,array("encoding" => $this->encoding)); 
		}
		
		$applicationDetails = array('applicationId'=>$this->applicationId,
						 'password'=>$this->password,);
		
		$jsonStream = json_encode($applicationDetails+$messageDetails);
		
		return $jsonStream;
	}

	public function setsourceAddress($sourceAddress){
		$this->sourceAddress=$sourceAddress;
	}

	public function setcharging_amount($charging_amount){
		$this->charging_amount=$charging_amount;
	}

	public function setencoding($encoding){
		$this->encoding=$encoding;
	}

	public function setversion($version){
		$this->version=$version;
	}

	public function setbinaryHeader($binaryHeader){
		$this->binaryHeader=$binaryHeader;
	}

	public function setdeliveryStatusRequest($deliveryStatusRequest){
		$this->deliveryStatusRequest=$deliveryStatusRequest;
	}
}


class SMSServiceException extends Exception{
	private $statusCode,
	$statusDetail;

	public function __construct($message, $code){
		parent::__construct($message);

		$this->statusCode = $code;
		$this->statusDetail = $message;
	}

	public function getErrorCode(){
		return $this->statusCode;
	}

	public function getErrorMessage(){
		return $this->statusDetail;
	}
}






/********USSD********/


class UssdReceiver{

    private $sourceAddress; 
    private $message;
    private $requestId;
    private $applicationId;
    private $encoding;
    private $version;
    private $sessionId;
    private $ussdOperation;
    private $vlrAddress;
	private $thejson;
	
    public function __construct(){
        $array = json_decode(file_get_contents('php://input'), true);
        $this->thejson = json_decode(file_get_contents('php://input'), true);
        $this->sourceAddress = $array['sourceAddress'];
        $this->message = $array['message'];
        $this->requestId = $array['requestId'];
        $this->applicationId = $array['applicationId'];
        $this->encoding = $array['encoding'];
        $this->version = $array['version'];
        $this->sessionId = $array['sessionId'];
        $this->ussdOperation = $array['ussdOperation'];

        if (!((isset($this->sourceAddress) && isset($this->message)))) {
            throw new Exception("Some of the required parameters are not provided");
        } else {
            $responses = array("statusCode" => "S1000", "statusDetail" => "Success");
        }
    }

	public function getthejson(){
		return $this->thejson;
	}

    public function getAddress(){
        return $this->sourceAddress;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getRequestID(){
        return $this->requestId;
    }

    public function getApplicationId(){
        return $this->applicationId;
    }

    public function getEncoding(){
        return $this->encoding;
    }

    public function getVersion(){
        return $this->version;
    }

    public function getSessionId(){
        return $this->sessionId;
    }

    public function getUssdOperation(){
        return $this->ussdOperation;
    }
	
	

}


class UssdSender extends Core{
    	private $applicationId,
			$password,
			$charging_amount='',
			$encoding='',
			$version='',
			$deliveryStatusRequest='',
			$binaryHeader='',
			$sourceAddress='',
			$serverURL;

    public function __construct($server,$applicationId,$password){
        $this->serverURL = $server; 
        $this->applicationId = $applicationId; 
        $this->password = $password; 
    }

    public function ussd( $sessionId, $message, $destinationAddress, $ussdOperation='mo-cont'){
						 
        if (is_array($destinationAddress)) { 
            return $this->ussdMany($message,$sessionId, $ussdOperation, $destinationAddress);
				
        } else if (is_string($destinationAddress) && trim($destinationAddress) != "") {
            return $this->ussdMany($message,$sessionId, $ussdOperation, $destinationAddress);
        } else {
            throw new Exception("address should a string or a array of strings");
        }
    }

    private function ussdMany($message,$sessionId, $ussdOperation, $destinationAddress)
	{

        $arrayField = array("applicationId" => $this->applicationId,
            "password" => $this->password,
            "message" => $message,
            "destinationAddress" => $destinationAddress,
            "sessionId" => $sessionId,
            "ussdOperation" => $ussdOperation,
            "encoding" => "440"
			);

        $jsonObjectFields = json_encode($arrayField);
        return $this->sendRequest($jsonObjectFields,$this->serverURL);
    }

    private function handleResponse($resp){
        if ($resp == "") {
            throw new UssdException
            ("Server URL is invalid", '500');
        } else {
            echo $resp;
        }
    }

}



class UssdException extends Exception{ // Ussd Exception Handler

    var $code;
    var $response;
    var $statusMessage;

    public function __construct($message, $code, $response = null){
        parent::__construct($message);
        $this->statusMessage = $message;
        $this->code = $code;
        $this->response = $response;
    }

    public function getStatusCode(){
        return $this->code;
    }

    public function getStatusMessage(){
        return $this->statusMessage;
    }

    public function getRawResponse(){
        return $this->response;
    }

}






/**********Logger*********/

class Logger{
	public function WriteLog($logStream){
		$_LOGFILE = 'LogData.log';
		
		$file = fopen($_LOGFILE, 'a');
		fwrite($file, '['.date('D M j G:i:s T Y').'] '.$logStream.'\n');
		fclose($file);
	}
}






/**************************/

class DirectDebitSender extends core{
    var $server;
    var $applicationId;
    var $password;
			

    public function __construct($server,$applicationId,$password){
        $this->server = $server;
        $this->applicationId = $applicationId;
        $this->password = $password;
    }

    /*
        Get parameters form the application
        check one or more addresses
        Send them to cassMany
    **/
    public function cass( $externalTrxId, $subscriberId, $amount){
       
        if (is_array($subscriberId)) {
            return $this->cassMany( $externalTrxId, $subscriberId,  $amount);
        } else if (is_string($subscriberId) && trim($subscriberId) != "") {
            return $this->cassMany( $externalTrxId, $subscriberId,  $amount);
        } else {
            throw new Exception("Address should be a string or a array of strings");
        }
    }
	

    private function cassMany($externalTrxId, $subscriberId, $amount){
        $arrayField = array(
				        	"applicationId" => $this->applicationId, 
				            "password" => $this->password,
				            "externalTrxId" => $externalTrxId,
				            "subscriberId" => $subscriberId,
				            "amount" => $amount
				        );
        $jsonObjectFields = json_encode($arrayField); 
        return $this->handleResponse(json_decode($this->sendRequest($jsonObjectFields,$this->server)));
    }
	

    private function handleResponse($jsonResponse){
    
        if(empty($jsonResponse))
            throw new CassException('Invalid server URL', '500');
        
        $statusCode = $jsonResponse->statusCode;
        $statusDetail = $jsonResponse->statusDetail;
        
        if(strcmp($statusCode, 'S1000')==0)
            return 'ok';
        else
            throw new CassException($statusDetail, $statusCode);
    }

}



/********************************************************************///////

class QueryBalanceSender{
    var $server;

    public function __construct($server){
        $this->server = $server; // Assign server url
        $this->logger = new KLogger ( "cass_debug.log" , KLogger::DEBUG );
    }

    /*
        Get parameters form the application
        check one or more addresses
        Send them to queryBalanceMany
    **/

    public function queryBalance($applicationId, $password, $subscriberId, $paymentInstrumentName, $currency){
        if (is_array($subscriberId)) {
            return $this->queryBalanceMany($applicationId, $password, $subscriberId, $paymentInstrumentName, $currency);
        } else if (is_string($subscriberId) && trim($subscriberId) != "") {
            return $this->queryBalanceMany($applicationId, $password, $subscriberId, $paymentInstrumentName, $currency);
        } else {
        
        	function exception_handler($exception) {
  		echo "Uncaught exception: " , $exception->getMessage(), "\n";
		}
        	set_exception_handler('exception_handler');
            throw new Exception("address should a string or a array of strings");
        }
    }










    /*
        Get parameters form the queryBalance
        Assign them to an array according to json format
        encode that array to json format
        Send json to sendRequest
    **/

    private function queryBalanceMany($applicationId, $password, $subscriberId, $paymentInstrumentName, $currency){

        $arrayField = array("applicationId" => $applicationId, // set the fields as array with parameter fields
            "password" => $password,
            "subscriberId" => $subscriberId,
            "paymentInstrumentName" => $paymentInstrumentName,
          
            "currency" => $currency);

        $jsonObjectFields = json_encode($arrayField); // encode the fields to json
        return $this->bsendRequest($jsonObjectFields);
    }

    /*
        Get the json request from queryBalanceMany
        use curl methods to send queryBalance
        Send the response to handleResponse
    **/

    private function bsendRequest($jsonObjectFields){ //Use curl commands for send json request
        $this->logger->LogDebug("QueryBalanceSender sendRequest() : Request=".$jsonObjectFields);
        $ch = curl_init($this->server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObjectFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch); // Send the json request
        curl_close($ch);

        $this->logger->LogDebug("DirectDebitSender sendRequest() : Response=".$res);
        if ($res == "") { // Check get the response successfully
            throw new CassException
            ("Server URL is invalid", '500');
        } else {
            return $res; //Return Success response
        }
    }
}



/***************************************************************************/






class CassException extends Exception{ //Cass Exception Handler

    var $code;
    var $response;
    var $statusMessage;

    public function __construct($message, $code, $response = null){
        parent::__construct($message);
        $this->statusMessage = $message;
        $this->code = $code;
        $this->response = $response;
    }

    public function getStatusCode(){
        return $this->code;
    }

    public function getStatusMessage(){
        return $this->statusMessage;
    }

    public function getRawResponse(){
        return $this->response;
    }

}



/**************************/
//---------------Subscription----------------------

class ideamart{
public function subcribe($url,$appid,$pw,$sub){

    $arrayField = array("applicationId" => $appid,
        "password" => $pw,
        "subscriberId" => $sub,
        "version"=>"1.0",
        "action"=>"1"
    );

    $jsonStream = json_encode($arrayField);

    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $jsonStream);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res);
}
}




?>