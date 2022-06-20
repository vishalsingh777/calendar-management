<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
  // This file contains the EventList class, which generates a 
  // list of upcoming events to display on the website.
  
  require_once("../config.php");
  
  class Office365Service {
    private static $authority = "https://login.microsoftonline.com";
    private static $authorizeUrl = '/common/oauth2/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code&scope=';
    private static $tokenUrl = "/common/oauth2/token";
    private static $outlookApiUrl = "https://graph.microsoft.com/beta";
    
    // Set this to true to enable Fiddler capture.
    // Note that if you have this set to true and you are not running Fiddler
    // on the web server, requests will silently fail.
    private static $enableFiddler = false;
    
 
    
    
    // Sends a request to the token endpoint to exchange an auth code
    // for an access token.
    public static function getTokenFromAuthCode($authCode, $redirectUri) {
      // Build the form data to post to the OAuth2 token endpoint
      $token_request_data = array(
        "grant_type" => "authorization_code",
        "code" => $authCode,
        "redirect_uri" => $redirectUri,
        "resource" => "https://graph.microsoft.com/",
        "client_id" => OUTLOOK_CLIENT_ID,
        "client_secret" => OUTLOOK_CLIENT_SECRET 
      );
      
      // Calling http_build_query is important to get the data
      // formatted as Azure expects.
      $token_request_body = http_build_query($token_request_data);
      error_log("Request body: ".$token_request_body);
      
      $curl = curl_init(self::$authority.self::$tokenUrl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      if (self::isFailure($httpCode)) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Token request returned HTTP error '.$httpCode);
      }
      
      // Check error
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      
      curl_close($curl);
      
      // The response is a JSON payload, so decode it into
      // an array.
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
    
    // Sends a request to the token endpoint to get a new access token
    // from a refresh token.
    public static function getTokenFromRefreshToken($refreshToken) {
      // Build the form data to post to the OAuth2 token endpoint
      $token_request_data = array(
        "grant_type" => "refresh_token",
        "refresh_token" => $refreshToken,
        "resource" => "https://outlook.office365.com/",
        "client_id" => ClientReg::$clientId,
        "client_secret" => ClientReg::$clientSecret
      );
        
      $token_request_body = http_build_query($token_request_data);
      error_log("Request body: ".$token_request_body);
      
      $curl = curl_init(self::$authority.self::$tokenUrl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      if (self::isFailure($httpCode)) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Token request returned HTTP error '.$httpCode);
      }
      
      // Check error
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      
      curl_close($curl);
      
      // The response is a JSON payload, so decode it into
      // an array.
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
    
    
    // Use the Calendar API to add an event to the default calendar.
    public static function addEventToCalendar($access_token, $subject, $description, $location, $startTime, $endTime) {

      $event = array(
        "subject" => $subject,
        "location" => array("DisplayName" => $location),
        "start" => array("dateTime" => $startTime, "timeZone" =>  "Singapore Standard Time"),
        "end" => array("dateTime" => $endTime, "timeZone" => "Singapore Standard Time"),
        "body" => array("ContentType" => "HTML", "Content" => $description)
      );
      
      $event = json_encode($event);

      $createEventUrl = self::$outlookApiUrl."/me/events";
      
      $response = self::makeApiCall($access_token, "POST", $createEventUrl, $event);
      // If the call succeeded, the response should be a JSON representation of the
      // new event. Try getting the Id property and return it.
      if ($response['id']) {
        return $response['id'];
      }
      
      else {
        error_log("ERROR: ".$response);
        return $response;
      }
    }

    // Use the Calendar API to add an event to the default calendar.
    public static function deleteEventToCalendar($access_token,$event_id='') {
      if (!empty($event_id)) {
        
        // Generate the JSON payload
        $createEventUrl = self::$outlookApiUrl."/Me/Events/".$event_id;
        
        $response = self::makeApiCall($access_token, "DELETE", $createEventUrl);
        
        return 'TRUE';
      }
      return 'TRUE';
    }

    
    // Make an API call.
    public static function makeApiCall($access_token, $method, $url, $payload = NULL) {
      // Generate the list of headers to always send.
      $headers = array(
        "User-Agent: myapp/1.0",         // Sending a User-Agent header is a best practice.
        "Authorization: Bearer ".$access_token, // Always need our auth token!
        "Accept: application/json",             // Always accept JSON response.
        "client-request-id: ".self::makeGuid(), // Stamp each new request with a new GUID.
        "return-client-request-id: true"        // Tell the server to include our request-id GUID in the response.
      );
      
      $curl = curl_init($url);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      switch(strtoupper($method)) {
        case "GET":
          // Nothing to do, GET is the default and needs no
          // extra headers.
          error_log("Doing GET");
          break;
        case "POST":
          error_log("Doing POST");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "PATCH":
          error_log("Doing PATCH");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "DELETE":
          error_log("Doing DELETE");
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
          break;
        default:
          error_log("INVALID METHOD: ".$method);
          exit;
      }
      
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        curl_close($curl);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      else {
        error_log("Response: ".$response);
        curl_close($curl);
        return json_decode($response, true);
      }
    }
    
    // This function convert a dateTime from local TZ to UTC, then
    // encodes it in the format expected by the Outlook APIs.
    public static function encodeDateTime($dateTime) {
      $dateTime = new DateTime();
      $utcDateTime = $dateTime->setTimeZone(new DateTimeZone("UTC"));
      
      $dateFormat = "Y-m-d\TH:i:s\Z";
      return date_format($utcDateTime, $dateFormat);
    }
    
    // This function generates a random GUID.
    public static function makeGuid(){
        if (function_exists('com_create_guid')) {
          error_log("Using 'com_create_guid'.");
          return strtolower(trim(com_create_guid(), '{}'));
        }
        else {
          error_log("Using custom GUID code.");
          $charid = strtolower(md5(uniqid(rand(), true)));
          $hyphen = chr(45);
          $uuid = substr($charid, 0, 8).$hyphen
                 .substr($charid, 8, 4).$hyphen
                 .substr($charid, 12, 4).$hyphen
                 .substr($charid, 16, 4).$hyphen
                 .substr($charid, 20, 12);
                 
          return $uuid;
        }
    }
    
    public static function isFailure($httpStatus){
      // Simplistic check for failure HTTP status
      return ($httpStatus >= 400);
    }
  }
  
?>
