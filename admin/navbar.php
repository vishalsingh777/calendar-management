<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<style>
</style>
<?php if($_SESSION['login_type'] == 1){ ?>
	<nav id="sidebar" class='mx-lt-5 bg-dark' >
			
			<div class="sidebar-list">
					

					<a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
					<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users"></i></span> Users</a>
					<a href="index.php?page=timeslot" class="nav-item nav-slot"><span class='icon-field'><i class="fa fa-slot"></i></span> Time Slot</a>
			</div>

	</nav>
<?php }?>
				<!-- <a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
			<?php // endif; ?> -->

<script>
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>
