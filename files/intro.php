<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------

include_once( "common.php" );

if ( !isset($_SESSION['us_AuthLevel']) )
{	// access not via main page - access denied
	func_unauthorisedaccess();
	return;
}

?>


<div class="container" style="margin-top:30px">
<?php
if ( $_SESSION['us_AuthLevel'] <= SECURITY_LEVEL_NONE )
{
?>
	<div class="row mb-2">
		<div class="col-sm-1">
			<label for="usr">Username:</label>
		</div>
		<div class="col-sm-3">
			<input type="text" class="form-control" name='Username' id='Username' placeholder="Enter email">
		</div>
		<div class="col-sm-1">
			<label for="pwd">Password:</label>
		</div>
		<div class="col-sm-3">
			<input type="password" class="form-control" name='Password' id='Password' placeholder="Enter password">
		</div>
		<div class="col-sm-1">
			<button type="submit" class='btn btn-outline-dark btn-primary' name='Login' Value='Login' id='Login'>Login</button>
		</div>
	</div>
	<div class="row">
		<div class='col'>
		<?php
		if ( $my_login_msg != "" )
		{
			printf( "<p class='text-danger'>%s</p>", $my_login_msg );
		}
		?>
		</div>
	</div>	<!-- end of row -->
<?php
}
?>


	<div class="row">
		<div class="col-sm-8">
		
			<h2>About TankTestDB</h2>
			<div><img class='img-fluid' src="./images/scuba_tank1.png"></div>
			<p>A web app for recording scuba tank periodic test results</p>
			<ul>
				<li>PHP</li>
				<li>Bootstrap</li>
				<li>MariaDB</li>
			</ul>
			<hr class="d-sm-none">
		</div>
		
	
	</div>	<!-- end of row -->
	
</div>


