<?php
include_once 'config.php';
require_once 'dbConfig.php';
/*
 * Generate events list in HTML format
*/

if(isset($_POST['event']) && $_POST['event'] == 'getEvents') {
        echo getEvents($_POST['date']);
   }

function getEvents($date = '')
{ 
    $date = $date ? $date : date("Y-m-d");
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    $eventListHTML = '<h2 class="sidebar__heading">' . date("l", strtotime($date)) . '<br>' . date("F d", strtotime($date)) . '</h2>';

    // Fetch events based on the specific date
   // $date = $_GET['date'];
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $bookings = array();
    $stmt->execute();
    $result = $stmt->get_result();
   
    if ($result->num_rows> 0)
    {
        if(isset($_SESSION['login_id']) && $_SESSION['login_id']){
                $eventListHTML .= "
                    <style>
                        th, td {
                            width: 100px;
                            padding: 7px;
                        }
                        .fifty {
                            width: 50px;
                        }
                        .twohundred {
                            width: 200px;
                        }
                        .twohundredfifty {
                            width: 250px;
                        }
                    </style>
                    <table id='example' class='display' cellspacing='0' width='100%'>
                        <thead>
                            <tr style='background:#ccc;'>
                              
                                <th class='user'>User</th>
                                <th class='food'>Food</th>
                                <th class='remark'>Remark</th>
                                <th class='time'>Time</th>
                              
                            </tr>   
                        </thead>
                        <tbody>   
                ";
        }else{
            $eventListHTML .= "
                <style>
                    th, td {
                        width: 100px;
                        padding: 7px;
                    }
                    .fifty {
                        width: 50px;
                    }
                    .twohundred {
                        width: 200px;
                    }
                    .twohundredfifty {
                        width: 250px;
                    }
                </style>
                <table id='example' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <tr style='background:#ccc;'>
                          
                            <th class='user'>User</th>
                            <th class='food'>Food</th>
                            <th class='remark'>Remark</th>
                            <th class='time'>Time</th>
                        </tr>   
                    </thead>
                    <tbody>   
            ";
        }
        

        while ($row = $result->fetch_assoc())
        {
            $eventListHTML .= "<tr data-toggle='modal' data-target='#viewModal'   data-id=" . $row["id"] . ">";
   
            $eventListHTML .= "    <td class='food'>" . $row["food_type"] . "</td>";
            $eventListHTML .= "    <td class='food'>" . $row["food_type"] . "</td>";
            $eventListHTML .= "    <td class='remark'>" . $row["description"] . "</td>";
            $eventListHTML .= "    <td class='time'>" . $row["timeslot"] . "</td>";
         /*   if(isset($_SESSION['login_id']) && $_SESSION['login_id']){
                $eventListHTML .= "    <td><button class='deletetbtn' onclick='getDelete(" . $row["id"] . ")' >Delete</button></td>";
            }*/
            $eventListHTML .= "</tr>";
        }
        $eventListHTML .= "
            </tbody>
        </table>
    ";
    }
    echo $eventListHTML;
}



?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script >
	function getDelete(id){
            $.ajax({ 
                type:'POST', 
                url:'delete.php', 
                data: { event: "deleteEvent",id: id}, 
                success:function(data){
                	var data = jQuery.parseJSON(data);
                    var googleOauthURL = data.url;
                    location.href = googleOauthURL;                 
                }
            }); 
        }
</script>