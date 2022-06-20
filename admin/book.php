<?php
require_once '../dbConfig.php'; 
require_once '../config.php'; 
require_once '../outlook/Office365Service.php'; 

if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
$mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['timeslot'];
            }
            $stmt->close();
        }
    }
}else{
    $date = date(m/d/Y);
}

if(isset($_POST['submit'])){
    $_SESSION['postData'] = $_POST; 
    $title = !empty($_POST['title'])?trim($_POST['title']):''; 
    $description = !empty($_POST['description'])?trim($_POST['description']):''; 
    $user_name = !empty($_SESSION['login_username'])?trim($_SESSION['login_username']):''; 
    $food_type = !empty($_POST['food_type'])?trim($_POST['food_type']):''; 
    $location =  $location = 'Sungei Kadut'; 
    $date = $date; 
    $timeslot = !empty($_POST['timeslot'])?trim($_POST['timeslot']):''; 
    $cal_type = !empty($_POST['cal_type'])?trim($_POST['cal_type']):''; 

    $stmt = $mysqli->prepare("select * from bookings where date = ? AND timeslot=?");
    $stmt->bind_param('ss', $date, $timeslot);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Already Booked</div>";
        }else{ 
            $stmt = $mysqli->prepare("INSERT INTO bookings (title, timeslot, user_name, location, food_type, description, date) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssss', $title, $timeslot, $user_name, $location, $food_type, $description, $date);
            $stmt->execute(); 
            $msg = "<div class='alert alert-success'>Booking Successfull</div>";
            $bookings[] = $timeslot;
            $event_id = $stmt->insert_id; 
            $social_account_type = $_SESSION['login_social_account_type'];
            unset($_SESSION['postData']); 
             unset($_SESSION['last_event_id']);
            // Store event ID in session 
            $_SESSION['last_event_id'] = $event_id; 
            $_SESSION['last_event_type'] = "save";  
            $log_file = "./social_account_type_book.log";
            error_log(" social_account_type_book:".$social_account_type, 3, $log_file);
            if($social_account_type == 2){   
                error_log("Location:".$outlookOauthURL, 3, $log_file);
                header("Location: $outlookOauthURL");  
            }else{ 
                error_log("Location:".$googleOauthURL, 3, $log_file);
                header("Location: $googleOauthURL");  
            }
            $stmt->close();
            $mysqli->close();
        }
    }
}

$duration = 60;
$sql = "SELECT `time_diff` FROM `timeslot`";
$result = $db->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $duration = $row['time_diff'];
  }
} else {
  $duration = 60;
}
$cleanup = 0;
$start = "12:00";
$end = "21:30";
function timeslots($duration, $cleanup, $start, $end){
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT".$duration."M");
    $cleanupInterval = new DateInterval("PT".$cleanup."M");
    $slots = array();
    
    for($intStart = $start; $intStart<$end; $intStart->add($interval)->add($cleanupInterval)){
        $endPeriod = clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end){
            break;
        }
        
        $slots[] = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
        
    }
    
    return $slots;
}


?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <div class="container">
        <h1 class="text-center">Book for Date: <?php echo date('m/d/Y', strtotime($date)); ?></h1><hr>
        <div class="row">
            <div class="col-md-12">
               <?php echo(isset($msg))?$msg:""; ?>
            </div>
            <?php $timeslots = timeslots($duration, $cleanup, $start, $end); 
                foreach($timeslots as $ts){
            ?>
            <div class="col-md-2">
                <div class="form-group">
                   <?php if(in_array($ts, $bookings)){ ?>
                   <button class="btn btn-danger"><?php echo $ts; ?></button>
                   <?php }else{ ?>
                   <button class="btn btn-success book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>
                   <?php }  ?>
                </div>
            </div>
            <?php } ?>
            </div>
    </div>
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Booking for: <span id="slot"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="" method="post">
                               <div class="form-group">
                                    <label for="">Time Slot</label>
                                    <input readonly type="text" class="form-control" id="timeslot" name="timeslot">
                                    <input readonly type="hidden" class="form-control" id="date" name="date" value="<?php echo date('d/m/Y', strtotime($date)); ?>">
                                    <input readonly type="hidden" class="form-control" id="location" name="location" value="Sungei Kadut">
                                </div>
                                <div class="form-group">
                                    <label for="">Title</label>
                                    <input readonly type="text" class="form-control" id="timeslot" name="title" value="F&B Booking">
                                </div>
                                <div class="form-group">
                                    <label>Food Option</label>
                                    <select class="form-control" name="food_type">
                                      <option value="western">Western Delights</option>
                                      <option value="chinese">Chinese Cuisine</option>
                                      <option value="special">Special Cuisine</option>
                                      <option value="malay">Malay Cuisine</option>
                                      <option value="others">Others</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="description" id ="description" class="form-control"></textarea>
                                </div>
                                <div class="form-group pull-right">
                                    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
    $(".book").click(function(){
        var timeslot = $(this).attr('data-timeslot');
        $("#slot").html(timeslot);
        $("#timeslot").val(timeslot);
        $("#myModal").modal("show");
    });
    </script>
  </body>

</html>