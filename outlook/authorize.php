<?php 
// Include Outlook calendar api handler class 
require_once('Office365Service.php');
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
    $code = $_GET['code'];
    // Get event ID from session 
    $session_state = $_GET['session_state'];

   // $event_id = $_SESSION['last_event_id'];  
    //$last_event_type = $_SESSION['last_event_type'];
    $sql = "SELECT MAX(id) as id FROM bookings"; 
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
        $event_id = $row['id'];
      }
    } 
     
       $log_file = "./outlook.log";
          
       // logging error message to given log file
       error_log("EVENT TYPE:".$last_event_type, 3, $log_file);
       error_log("EVENT ID:".$event_id, 3, $log_file);
    if(!empty($event_id)){  

        /*if($last_event_type == 'delete'){
            $access_token_sess;
            // Get the access token 
            if(isset($_SESSION['outlook_access_token'])){
                $access_token_sess = $_SESSION['outlook_access_token']; 
            }
            if(!empty($access_token_sess)){  
                $access_token = $access_token_sess; 
            }else{
                $data = Office365Service::getTokenFromAuthCode($code, OUTLOOK_REDIRECT_URI);
                $access_token = $data['access_token']; 
            }

            if(!empty($access_token)){   
                try { 
                    // Create an event on the primary calendar 
                    $outlook_event_id = $_SESSION['outlook_calendar_event_id']; 
                    $deleteEvent = Office365Service::deleteEventToCalendar($access_token, $outlook_event_id);
                    $id = $_SESSION['last_event_id'];  
                    if($deleteEvent == TRUE){ 
                       $result =  mysqli_query($db, "DELETE from bookings WHERE id = '".$id."'");
                        if(!$result){
                            $msg= "Error: <br>" . mysqli_error($db);
                            echo $msg;
                        }
                         
                        unset($_SESSION['outlook_calendar_event_id']); 
                        unset($_SESSION['last_event_id']); 
                        unset($_SESSION['last_event_type']); 
                        unset($_SESSION['outlook_access_token']); 
                         
                        $status = 'success'; 
                        $statusMsg = '<p>Event #'.$event_id.' has been deleted to Outlook Calendar successfully!</p>';  
                    } 
                } catch(Exception $e) { 
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
             
            if(!empty($eventData)){ 
                $subject = $eventData['title'];
                $location = $eventData['location'];
                $description = $eventData['description'];
                $event_date = $eventData['date'];
                $startTime = $eventData['timeslot'];
                $startTime =  substr($startTime, 0, 5);
                $startTime =  substr($startTime, 0, 5);
                $endTime   = strtotime($startTime) + 60*60;
                $endTime      = date('H:i', $endTime);

                $startTime = date('Y-m-d H:i:s', strtotime("$event_date $startTime"));
                $endTime = date('Y-m-d H:i:s', strtotime("$event_date $endTime"));
                $access_token_sess;
                // Get the access token 
                if(isset($_SESSION['outlook_access_token'])){
                    $access_token_sess = $_SESSION['outlook_access_token']; 
                }
                if(!empty($access_token_sess)){ 
                    $access_token = $access_token_sess; 
                }else{ 
                    $data =  Office365Service::getTokenFromAuthCode($code, OUTLOOK_REDIRECT_URI);
                    $access_token = $data['access_token']; 
                    $_SESSION['outlook_access_token'] = $access_token; 
                } 
    
                if(!empty($access_token)){ 
                    try { 
                        // Create an event on the  calendar 
                        $outlook_event_id = Office365Service::addEventToCalendar($access_token, $subject, $description, $location, $startTime, $endTime); 
                         
                        if($outlook_event_id){ 
                            // Update google event reference in the database 
                            $sqlQ = "UPDATE bookings SET google_calendar_event_id=? WHERE id=?"; 
                            $stmt = $db->prepare($sqlQ); 
                            $stmt->bind_param("si", $db_google_event_id, $db_event_id); 
                            $db_google_event_id = $outlook_event_id; 
                            $db_event_id = $event_id; 
                            $update = $stmt->execute(); 
                             
                            unset($_SESSION['last_event_id']); 
                            unset($_SESSION['outlook_access_token']); 
                             
                            $status = 'success'; 
                            $statusMsg = '<p>Event #'.$event_id.' has been added to Outlook Calendar successfully!</p>';  
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
       // }
        
    }else{ 
        $statusMsg = 'Event reference not found!'; 
    } 
     
    $_SESSION['status_response'] = array('status' => $status, 'status_msg' => $statusMsg); 
     
    header("Location: ../admin/index.php"); 
    exit(); 
} 
?>