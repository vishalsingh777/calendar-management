<?php 
// Include Google calendar api handler class 
include_once 'GoogleCalendarApi.php';

if(!isset($_SESSION)) 
{ 
    session_start(); 
}  
// Include database configuration file 
require_once '../dbConfig.php'; 
require_once '../config.php'; 

$statusMsg = ''; 
$status = 'danger';  
if(isset($_GET['code'])){  
    // Initialize Google Calendar API class 
    $GoogleCalendarApi = new GoogleCalendarApi(); 
    // Get event ID from session 

    $sql = "SELECT MAX(id) as id FROM bookings"; 
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
        $event_id = $row['id'];
      }
    }
  //  $event_id = $_SESSION['last_event_id'];  
   // $last_event_type = $_SESSION['last_event_type']; 
    if(!empty($event_id)){  
       /* if($last_event_type == 'delete'){   
            $log_file = "./delete.log";
            $access_token_sess;
            // Get the access token 
            if(isset($_SESSION['google_access_token'])){
                $access_token_sess = $_SESSION['google_access_token']; 
            }
            if(!empty($access_token_sess)){ 
                $access_token = $access_token_sess; 
            }else{
                $data = $GoogleCalendarApi->GetAccessToken(GOOGLE_CLIENT_ID, REDIRECT_URI, GOOGLE_CLIENT_SECRET, $_GET['code']); 
                $access_token = $data['access_token']; 
            } 
                
                $_SESSION['google_access_token'] = $access_token; 
                error_log("GOOGLE DELETE google_access_token:".$access_token, 3, $log_file);
                if(!empty($access_token)){     
                    try {  
                        // Delete an event on the primary calendar 
                        $google_event_id = $_SESSION['google_calendar_event_id'];      
                        $id = $_SESSION['last_event_id'];      
                        error_log("GOOGLE DELETE google_event_id:".$access_token, 3, $log_file);
                        error_log("GOOGLE DELETE id:".$access_token, 3, $log_file);
                        $deleteEvent = $GoogleCalendarApi->DeleteCalendarEvent($google_event_id, CALENDAR_ID, $access_token);    
                        if($deleteEvent == 1){ 
                           $result =  mysqli_query($db, "DELETE from bookings WHERE id = '".$id."'");
                            if(!$result){
                                $msg= "Error: " . mysqli_error($db);
                                echo $msg;
                            }
                            unset($_SESSION['google_calendar_event_id']); 
                            unset($_SESSION['last_event_id']); 
                            unset($_SESSION['google_access_token']); 
                             
                            $status = 'success'; 
                            $statusMsg = '<p>Event #'.$event_id.' has been deleted to Google Calendar successfully!</p>';  
                        } 
                    } catch(Exception $e) {  echo $e->getMessage() ;
                        //header('Bad Request', true, 400); 
                        //echo json_encode(array( 'error' => 1, 'message' => $e->getMessage() )); 
                        $statusMsg = $e->getMessage(); 
                    } 
                }else{ 
                    $statusMsg = 'Failed to fetch access token!'; 
                }
            
        }else{*/ 
            // Fetch event details from database 
            $sqlQ = "SELECT * FROM bookings WHERE id = ?"; 
            $stmt = $db->prepare($sqlQ);  
            $stmt->bind_param("i", $db_event_id); 
            $db_event_id = $event_id; 
            $stmt->execute(); 
            $result = $stmt->get_result(); 
            $eventData = $result->fetch_assoc(); 
            $startTime = $eventData['timeslot'];
            $startTime =  substr($startTime, 0, 5);
            $startTime =  substr($startTime, 0, 5);
            $endTime   = strtotime($startTime) + 60*60;
            $endTime      = date('H:i', $endTime);
            if(!empty($eventData)){  
                $calendar_event = array( 
                    'summary' => $eventData['title'], 
                    'location' => $eventData['location'], 
                    'description' => $eventData['description'] 
                ); 
                 
                $event_datetime = array( 
                    'event_date' => $eventData['date'], 
                    'start_time' => $startTime, 
                    'end_time' => $endTime 
                ); 
                $access_token_sess;
                // Get the access token 
                if(isset($_SESSION['google_access_token'])){
                    $access_token_sess = $_SESSION['google_access_token']; 
                }
                if(!empty($access_token_sess)){ 
                    $access_token = $access_token_sess; 
                }else{ 
                    $data = $GoogleCalendarApi->GetAccessToken(GOOGLE_CLIENT_ID, REDIRECT_URI, GOOGLE_CLIENT_SECRET, $_GET['code']); 
                    $access_token = $data['access_token']; 
                    $_SESSION['google_access_token'] = $access_token; 
                } 
                 
                if(!empty($access_token)){  
                    try { 
                        // Get the user's calendar timezone 
                        $user_timezone = $GoogleCalendarApi->GetUserCalendarTimezone($access_token); 
                     
                        // Create an event on the  calendar 
                        $google_event_id = $GoogleCalendarApi->CreateCalendarEvent($access_token, CALENDAR_ID, $calendar_event, 0, $event_datetime, $user_timezone); 
                         
                        if($google_event_id){ 
                            // Update google event reference in the database 
                            $sqlQ = "UPDATE bookings SET google_calendar_event_id=? WHERE id=?"; 
                            $stmt = $db->prepare($sqlQ); 
                            $stmt->bind_param("si", $db_google_event_id, $db_event_id); 
                            $db_google_event_id = $google_event_id; 
                            $db_event_id = $event_id; 
                            $update = $stmt->execute(); 
                             
                            unset($_SESSION['last_event_id']); 
                            unset($_SESSION['google_access_token']); 
                             
                            $status = 'success'; 
                            $statusMsg = '<p>Event #'.$event_id.' has been added to Google Calendar successfully!</p>';  
                        } 
                    } catch(Exception $e) { 
                        //header('Bad Request', true, 400); 
                        //echo json_encode(array( 'error' => 1, 'message' => $e->getMessage() )); 
                        $statusMsg = $e->getMessage(); 
                    } 
                }else{ 
                    $statusMsg = 'Failed to fetch access token!'; 
                } 
            }else{ 
                $statusMsg = 'Event data not found!'; 
            } 
        /*}*/
        
    }else{ 
        $statusMsg = 'Event reference not found!'; 
    } 
     
    $_SESSION['status_response'] = array('status' => $status, 'status_msg' => $statusMsg); 
     
    header("Location: ../admin/index.php"); 
    exit(); 
} 
?>