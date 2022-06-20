<?php include '../dbConfig.php';
require_once '../functions.php'; 
if(!isset($_SESSION['login_id'])){
    header("location:login.php");
}
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
 function build_calendar($month, $year) {
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    $daysOfWeek = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
    $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
    $numberDays = date('t',$firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday'];
    $datetoday = date('Y-m-d'); 
    if($dayOfWeek == 0){
        $dayOfWeek = 6;
    }else{
        $dayOfWeek = $dayOfWeek - 1;
    }
    $prev_month = date('m', mktime(0, 0, 0, $month-1, 1, $year)) ;
    $prev_year = date('Y', mktime(0, 0, 0, $month-1, 1, $year)) ;
    $next_month = date('m', mktime(0, 0, 0, $month+1, 1, $year)) ;
    $next_year = date('Y', mktime(0, 0, 0, $month+1, 1, $year)) ;

    $calendar = "<table class='table table-bordered'>"; 
    $calendar .= "<center><h2>$monthName $year</h2>"; 
    $calendar .= "<a class='changemonth btn btn-primary btn-xs' href='?month=".$prev_month."&year=".$prev_year."'>Pre Month</a>&nbsp"; 
    $calendar .= "<a class='changemonth btn btn-primary btn-xs' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a>&nbsp"; 
    $calendar .= "<a class='changemonth btn btn-primary btn-xs' href='?month=".$next_month."&year=".$next_year."'>Next Month</a></center><br>"; 
    $calendar.= "<tr>"; 

    // Create the calendar headers 
    foreach($daysOfWeek as $day) { 
         $calendar .= "<th class='header'>$day</th>"; 
    } 
    $currentDay = 1;
    $calendar .= "</tr><tr>";

    if($dayOfWeek > 0) { 
        for($k=0;$k<$dayOfWeek;$k++){ 
            $calendar .= "<td class='empty'></td>"; 
        } 
    }
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    while ($currentDay <= $numberDays) { 
        //Seventh column (Saturday) reached. Start a new row. 
        if ($dayOfWeek == 7) { 
            $dayOfWeek = 0; 
            $calendar .= "</tr><tr>"; 
        } 
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT); 
        $date = "$year-$month-$currentDayRel"; 
        $dayname = strtolower(date('l', strtotime($date))); 
        $eventNum = 0; 
        $today = $date==date('Y-m-d')? "today" : "";
        if($date<date('Y-m-d')){
        $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>N/A</button>";
        }else{
            $totalbookings = checkSlots($mysqli, $date);
            if ($totalbookings > 0) {
                $calendar.="<td class='$today' onclick='getEvents(\" " . $date . "\")';><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Book</a><small class= 'event-data'' ><i> $totalbookings Event</i></small>";
            }else{
                $calendar.="<td class='$today' onclick='getEvents(\" " . $date . "\")';><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Book</a><small class= 'event-data''><i> No Event</i></small>";
            }
           //   $calendar.="<td class='$today'><h4>$currentDay</h4> <button type='button' class='calendar-open' data-toggle='modal' data-target='#calendar-modal'>Add Event</button><small><i> No Event</i></small>"; 
        } 
        $calendar .="</td>"; 
        //Increment counters 
        $currentDay++; 
        $dayOfWeek++; 
    } 
    //Complete the row of the last week in month, if necessary 
    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek; 
        for($l=0;$l<$remainingDays;$l++){ 
            $calendar .= "<td class='empty'></td>"; 
        } 
    } 

    $calendar .= "</tr>"; 
    $calendar .= "</table>";
    return $calendar;
}

function checkSlots($mysqli, $date)
{
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $totalbookings = 0;
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $totalbookings++;
            }
            $stmt->close();
        }
    }

    return $totalbookings;
}

?>
<style>
   
</style>

<head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
      
        <link type="text/css" rel="stylesheet" href="style.css"/>
    </head>
<div class="containe-fluid">

    <div class="row">
        <div class="col-lg-12">
            
        </div>
    </div>

    <div class="row mt-3 ml-3 mr-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                    <?php echo "Welcome back " . $_SESSION['login_name'] . "!" ?>
                                        
                    </div>
                    <hr>
                    
                  </div>
                </div>
    </div>
<hr>
<?php if ($_SESSION['login_type'] == 2): ?>
    <div id="mySidenav" class="sidenav">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <div id="event_list"> 
                <?php  //echo getEvents($date = ''); ?> 

    </div>
</div>
<section class="calendar__days"> 
    <div id="calendar"> 
         <?php 
         if(isset($_GET['month']) && isset($_GET['year'])){
                  $month = $_GET['month']; 
                  $year = $_GET['year']; 
                  echo build_calendar($month,$year);
        }else{
              $dateComponents = getdate(); 
              $month = $dateComponents['mon']; 
              $year = $dateComponents['year']; 
              echo build_calendar($month,$year);
        } 
         ?> 
        </div> 
  </section>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>
        function getEvents(date){ 
            $.ajax({ 
                type:'POST', 
                url:'../functions.php', 
                dataType: 'html',
                data: { event: "getEvents",date: date},
                success:function(html){
                    document.getElementById("mySidenav").style.width = "500px";
                    //document.getElementById("event_list").style.width = "250px";
                    $('#event_list').html(html); 
                } 
            }); 
            // Add date to event form 
            //$('#event_date').val(date); 
        } 
    </script>
    <script>
        function closeNav() {
          document.getElementById("mySidenav").style.width = "0";
        }
    </script>
<?php
endif; ?>


</div>
<script>
    
</script>
