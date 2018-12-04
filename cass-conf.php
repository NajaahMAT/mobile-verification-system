<?php


    $APP_ID = "APP_017773";
    $PASSWORD = "5cfaca1cbc5e9e8068bfd9745a58a0b0";
    $EXTERNAL_TRX_ID = uniqid();
    $PAYMENT_INSTRUMENT_NAME = "MobileAccount";
    $ACCOUNT_ID = "123456";
    $CURRENCY ="LKR";
	$REGISTRATION_CHARGE="1";

/*  If application sms-mt sending https url used urls as below
	$SUBSCRIPTION_URL = "http://localhost:7000/subscription/send";
	$SMS_URL = "http://localhost:7000/sms/send";
    $QUERY_BALANCE_SENDER_URL = "https://localhost:7443/caas/balance/query";
    $DIRECT_DEBIT_SENDER_URL = "https://127.0.0.1:7443/caas/direct/debit";  */
	
	$SUBSCRIPTION_URL = "https://localhost:7447/subscription/send";
	$SMS_URL = "https://api.dialog.lk/sms/send";
    $QUERY_BALANCE_SENDER_URL = "https://api.dialog.lk/caas/balance/query";
    $DIRECT_DEBIT_SENDER_URL = "https://api.dialog.lk/caas/direct/debitt";
	
	
 ?>