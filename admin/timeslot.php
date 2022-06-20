<?php
include '../dbConfig.php';
$msg ='1';
$qry = $db->query("SELECT * from timeslot limit 1");
if($qry->num_rows > 0){
	foreach($qry->fetch_array() as $k => $val){
		$time_diff = $val; 
	}
}
 ?>
<div class="container-fluid">
	
	<div class="card col-lg-12">
		<div class="card-body">
			<form action="" id="save-time" class="save-time">
				<div class="form-group">
					<label for="time" class="control-label">Time Slot Diffrence</label>
					<select name="time_diff" id="time_diff">
					  <option value="30">30 Mintues</option>
					  <option value="60">1 Hours</option>
					  <option value="90">1:30 Hours</option>
					  <option value="120">2 Hours</option>
					</select>
				</div>
				<center>
					<button class="btn btn-info btn-primary btn-block col-md-2">Save</button>
				</center>
			</form>
		</div>
	</div>

<script>

	$('#save-time').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_time',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			error:err=>{
				console.log(err)
			},
			success:function(resp){
				if(resp == 1){
					alert_toast('Data successfully saved.','success')
					setTimeout(function(){
						location.reload()
					},1000)
				}
			}
		})

	})
</script>

</div>