<?php
include_once '../config.php';
require_once '../dbConfig.php';

if(isset($_POST['event']) && $_POST['event'] == 'deleteEvent' ) { 
        echo deleteEvent($_POST['id']); 
   }


/*
 * delete event
*/
function deleteEvent($id)
{ 
    global $db;
    global $googleOauthURL;
    global $outlookOauthURL;
    $return_arr = array();
    $google_calendar_event_id;
    // Perform query
    if ($result = mysqli_query($db, "Select id,google_calendar_event_id  from bookings WHERE id=$id"))
    {   
        unset($_SESSION['google_calendar_event_id']); 
        unset($_SESSION['last_event_id']); 
        unset($_SESSION['status_response']);
        // output data of each row
        while ($row = $result->fetch_assoc())
        {
            $google_calendar_event_id = $row["google_calendar_event_id"];
            $last_event_id = $row["id"];
        }
        if($_SESSION['login_social_account_type'] == 2){
            $return_arr = array(
                "google_calendar_event_id" => $google_calendar_event_id,
                "url" => $outlookOauthURL
            );
        }else{
            $return_arr = array(
                "google_calendar_event_id" => $google_calendar_event_id,
                "url" => $googleOauthURL
            );
        }


        $_SESSION['google_calendar_event_id'] = $google_calendar_event_id;
        $_SESSION['outlook_calendar_event_id'] = $google_calendar_event_id;
        $_SESSION['last_event_type'] = 'delete';
        $_SESSION['last_event_id'] = $id;

        echo json_encode($return_arr);
    }
    else
    {
        $msg = "Error:  <br>" . mysqli_error($db);
        echo $msg;
    }
}