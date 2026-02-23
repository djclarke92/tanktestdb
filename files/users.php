<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2015 Dave Clarke
//
//--------------------------------------------------------------------------------------
include_once( "common.php" );

if ( !isset($_SESSION['us_AuthLevel']) )
{	// access not via main page - access denied
    func_unauthorisedaccess();
    return;
}


function func_clear_us_array( &$us_array )
{
	$us_array['us_Username'] = "";
	$us_array['us_Name'] = "";
	$us_array['us_Password'] = "";
	$us_array['us_Password2'] = "";
	$us_array['us_AuthLevel'] = "0";
	$us_array['us_UF_Unused'] = "N";
	$us_array['us_StationName'] = "";
	$us_array['us_StationNumber'] = "";
	$us_array['us_Address1'] = "";
	$us_array['us_Address2'] = "";
	$us_array['us_Address3'] = "";
	$us_array['us_PostCode'] = "";
	$us_array['us_StationEmail'] = "";
	$us_array['us_SignatoryNumber'] = "";
	$us_array['us_Signature'] = "";
	$us_array['us_NextPeriodicCertNo'] = 0;
	$us_array['us_PeriodicCertNoQty'] = 0;
	$us_array['us_Logo'] = "";
	$us_array['error_msg'] = "";
	$us_array['info_msg'] = "";
}

function func_check_us_array( &$us_array )
{
	$us_array['error_msg'] = "";
	$us_array['info_msg'] = "";
	
	if ( $us_array['us_Password'] != "" && $us_array['us_Password2'] == "" )
	{  // handle autocomplete
	    $us_array['us_Password'] = "";
	}
	
	if ( $us_array['us_Username'] == "" )
	{
		$us_array['error_msg'] = "You must enter the Username.";
		return false;
	}
	else if ( strstr($us_array['us_Username'], "@" ) == false || strstr($us_array['us_Username'], "." ) == false )
	{
	    $us_array['error_msg'] = "The Username must be an email address containing '@' and '.'.";
	    return false;
	}
	else if ( $us_array['us_Password'] != "" && $us_array['us_Password'] != $us_array['us_Password2'] )
	{
		$us_array['error_msg'] = "The password and confirmation must match.";
		return false;
	}
	else if ( $us_array['us_Password'] != "" && strlen($us_array['us_Password']) < 6 )
	{
	    $us_array['error_msg'] = "The password must be at least 6 characters in length.";
	    return false;
	}
	else if ( $us_array['us_Name'] == "" )
	{
		$us_array['error_msg'] = "You must enter the Name.";
		return false;
	}
	else if ( $us_array['us_AuthLevel'] == "" )
	{
		$us_array['error_msg'] = "Your must select the Security Level.";
		return false;
	}
	else if ( $us_array['us_SignatoryNumber'] != "" && ($us_array['us_NextPeriodicCertNo'] == 0 || $us_array['us_PeriodicCertNoQty'] == 0) )
	{
		$us_array['error_msg'] = "Your must enter the Periodic Cert No. and Quantity.";
		return false;
	}
	else if ( ($us_array['us_NextPeriodicCertNo'] > 0 && $us_array['us_PeriodicCertNoQty'] == 0) ||
		($us_array['us_NextPeriodicCertNo'] == 0 && $us_array['us_PeriodicCertNoQty'] > 0) )
	{
		$us_array['error_msg'] = "Your must enter the Next Periodic Cert No. and the Quantity.";
		return false;
	}
	
	return true;
}


$us_array = array();
func_clear_us_array( $us_array );
$new_user = false;



if ( isset( $_GET['Username']) )
	$us_array['us_Username'] = $_GET['Username'];
if ( isset( $_POST['us_Username']) )
	$us_array['us_Username'] = $_POST['us_Username'];
if ( isset( $_POST['us_Name']) )
	$us_array['us_Name'] = $_POST['us_Name'];
if ( isset( $_POST['us_Password']) )
	$us_array['us_Password'] = $_POST['us_Password'];
if ( isset( $_POST['us_Password2']) )
	$us_array['us_Password2'] = $_POST['us_Password2'];
if ( isset( $_POST['us_AuthLevel']) )
    $us_array['us_AuthLevel'] = substr($_POST['us_AuthLevel'],0,1);
if ( isset( $_POST['us_UF_Unused']) )
    $us_array['us_UF_Unused'] = "Y";
if ( isset( $_POST['us_StationName']) )
	$us_array['us_StationName'] = $_POST['us_StationName'];
if ( isset( $_POST['us_StationNumber']) )
	$us_array['us_StationNumber'] = $_POST['us_StationNumber'];
if ( isset( $_POST['us_Address1']) )
	$us_array['us_Address1'] = $_POST['us_Address1'];
if ( isset( $_POST['us_Address2']) )
	$us_array['us_Address2'] = $_POST['us_Address2'];
if ( isset( $_POST['us_Address3']) )
	$us_array['us_Address3'] = $_POST['us_Address3'];
if ( isset( $_POST['us_PostCode']) )
	$us_array['us_PostCode'] = $_POST['us_PostCode'];
if ( isset( $_POST['us_StationEmail']) )
	$us_array['us_StationEmail'] = $_POST['us_StationEmail'];
if ( isset( $_POST['us_SignatoryNumber']) )
	$us_array['us_SignatoryNumber'] = $_POST['us_SignatoryNumber'];
if ( isset( $_POST['us_Signature']) )
	$us_array['us_Signature'] = $_POST['us_Signature'];
if ( isset( $_POST['us_NextPeriodicCertNo']) )
	$us_array['us_NextPeriodicCertNo'] = $_POST['us_NextPeriodicCertNo'];
if ( isset( $_POST['us_PeriodicCertNoQty']) )
	$us_array['us_PeriodicCertNoQty'] = $_POST['us_PeriodicCertNoQty'];
if ( isset($_POST['us_Logo']) )
	$us_array['us_Logo'] = $_POST['us_Logo'];
        

if ( isset($_GET['Username']) )
{
	if ( ($line=$db->GetFields( 'users', 'us_Username', $us_array['us_Username'], "us_Username,us_Name,us_AuthLevel,us_Features,us_StationName,us_StationNumber,
			us_Address1,us_Address2,us_Address3,us_PostCode,us_StationEmail,us_SignatoryNumber,us_Signature,us_NextPeriodicCertNo,us_PeriodicCertNoQty,us_Logo")) !== false )
	{	// success
		$us_array['us_Username'] = stripslashes($line[0]);
		$us_array['us_Name'] = stripslashes($line[1]);
		$us_array['us_AuthLevel'] = $line[2];
		$us_array['us_UF_Unused'] = substr( $line[3], E_UF_UNUSED, 1 );
		$us_array['us_StationName'] = $line[4];
		$us_array['us_StationNumber'] = $line[5];
		$us_array['us_Address1'] = $line[6];
		$us_array['us_Address2'] = $line[7];
		$us_array['us_Address3'] = $line[8];
		$us_array['us_PostCode'] = $line[9];
		$us_array['us_StationEmail'] = $line[10];
		$us_array['us_SignatoryNumber'] = $line[11];
		$us_array['us_Signature'] = stripslashes($line[12]);
		$us_array['us_NextPeriodicCertNo'] = $line[13];
		$us_array['us_PeriodicCertNoQty'] = $line[14];
		$us_array['us_Logo'] = stripslashes($line[15]);
	}
	else
	{
		$us_array['error_msg'] = sprintf( "Failed to read users table for Username=%s", $us_array['us_Username'] );
	}
}
else if ( isset($_POST['DeleteUser']) )
{
	$us_array['us_Username'] = $_POST['us_Username']; 
	if ( $db->DeleteUser( $us_array['us_Username'] ) )
	{
		$us_array['info_msg'] = sprintf( "User deleted" );
		func_clear_us_array( $us_array );
		$new_user = false;
	}
	else 
	{
		$us_array['error_msg'] = sprintf( "Failed to delete user with Username=%s", $us_array['us_Username'] );
	}
}
else if ( isset($_GET['AddNewUser']) )
{
    func_clear_us_array( $us_array );
    $new_user = true;
}
else if ( isset($_POST['NewUser']) || isset($_POST['UpdateUser']) )
{
	if ( isset($_POST['NewUser']) )
	{
	    $new_user = true;
	}
	
	if ( $new_user && $us_array['us_Password'] == "" && $us_array['us_AuthLevel'] > SECURITY_LEVEL_NONE )
	{
	    $us_array['error_msg'] = "The password and confirmation must be entered for new users.";
	}
	else if ( $new_user && $db->SelectUser($us_array['us_Username']) !== false )
	{
	    $us_array['error_msg'] = sprintf( "Username %s already exists in the database.", $us_array['us_Username'] );
	}
	else if ( func_check_us_array( $us_array ) )
	{
	    $features = "";
	    $features .= $us_array['us_UF_Unused'];
	    $features .= "NNNNNNNNN";
		if ( $db->UpdateUserTable( $new_user, $us_array['us_Username'], $us_array['us_Name'], $us_array['us_Password'], $us_array['us_AuthLevel'], $features, $us_array['us_StationName'], 
		    $us_array['us_StationNumber'], $us_array['us_Address1'], $us_array['us_Address2'], $us_array['us_Address3'], $us_array['us_PostCode'], $us_array['us_StationEmail'], $us_array['us_SignatoryNumber'], 
			$us_array['us_Signature'], $us_array['us_NextPeriodicCertNo'], $us_array['us_PeriodicCertNoQty'], $us_array['us_Logo'] ) )
		{	// success
			//func_clear_us_array( $us_array );
			
		    if ( $us_array['us_Username'] == $_SESSION['us_Username'] )
		    { // update the auth details
		        $_SESSION['us_Features'] = $features;
		    }

			$us_array['info_msg'] = "User details saved successfully.";
			$new_user = false;
		}
		else
		{
			$us_array['error_msg'] = sprintf( "Failed to update User record %s", $us_array['us_Username'] );
		}
	}
}
else if ( isset($_POST['ClearUser']) )
{
	func_clear_us_array( $us_array );
}


$user_list = $db->ReadUsers();


?>

<div class="container" style="margin-top:30px">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-5">
			<h3>User Management</h3>
		</div>
		<div class="col-sm-1">
			<a href='#userslist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="userslist" class="collapse <?php ($new_user || $us_array['us_Username'] != "" ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>Username</th>
              <th>Name</th>
              <th>Access</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $user_list as $info )
            {
                if ( $_SESSION['us_AuthLevel'] != SECURITY_LEVEL_ADMIN && $_SESSION['us_Username'] != $info['us_Username'] )
                    continue;
                
                printf( "<tr>" );
                
                printf( "<td><a href='?Username=%s'>%s</a></td>", $info['us_Username'], $info['us_Username'] );
                printf( "<td><a href='?Username=%s'>%s</a></td>", $info['us_Username'], $info['us_Name'] );
                printf( "<td><a href='?Username=%s'>%s</a></td>", $info['us_Username'], func_get_security_level_desc($info['us_AuthLevel']) );
                
                printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
                printf( "<p><a href='?AddNewUser'>Add New User</a></p>" );
            }
            ?>

		</div>

	</div>	<!-- end of row -->
	</div>
	
	<?php
    if ( $us_array['us_Username'] != "" || $new_user )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<h3>User Detail</h3>

            <?php 
            if ( $us_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $us_array['error_msg'] );
            else if ( $us_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $us_array['info_msg'] );
            

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Username'>Username: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_Username' id='us_Username' size='25' value='%s'> ", $us_array['us_Username'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Name'>Name: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_Name' id='us_Name' size='25' value='%s'> ", $us_array['us_Name'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Password'>Password: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='password' class='form-control' name='us_Password' id='us_Password' size='12' value='%s'> ", $us_array['us_Password'] );
    		printf( "</div>" );

			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Password2'>Confirmation: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='password' class='form-control' name='us_Password2' id='us_Password2' size='12' value='%s'> ", $us_array['us_Password2'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_AuthLevel'>Security Level: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
            printf( "<select class='form-control custom-select' size='1' name='us_AuthLevel' id='us_AuthLevel' %s>", func_disabled_non_admin() );
            printf( "<option ></option>" );
            printf( "<option %s>%d. None</option>", ($us_array['us_AuthLevel'] == SECURITY_LEVEL_NONE ? "selected" : ""), SECURITY_LEVEL_NONE );
            printf( "<option %s>%d. Guest</option>", ($us_array['us_AuthLevel'] == SECURITY_LEVEL_GUEST ? "selected" : ""), SECURITY_LEVEL_GUEST );
            printf( "<option %s>%d. Admin</option>", ($us_array['us_AuthLevel'] == SECURITY_LEVEL_ADMIN ? "selected" : ""), SECURITY_LEVEL_ADMIN );
            printf( "</select>" );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_StationName'>Station Name: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-5'>" );
    		printf( "<input type='text' class='form-control' name='us_StationName' id='us_StationName' size='30' value='%s'> ", $us_array['us_StationName'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_StationNumber'>Station Number: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='us_StationNumber' id='us_StationNumber' size='6' value='%s'>", $us_array['us_StationNumber'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Address1'>Address #1: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_Address1' id='us_Address1' size='30' value='%s'> ", $us_array['us_Address1'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Address2'>Address #2: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_Address2' id='us_Address2' size='30' value='%s'> ", $us_array['us_Address2'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Address3'>Address #3: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_Address3' id='us_Address3' size='30' value='%s'> ", $us_array['us_Address3'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_PostCode'>Post Code: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_PostCode' id='us_PostCode' size='6' value='%s'> ", $us_array['us_PostCode'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_StationEmail'>Station Email: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='us_StationEmail' id='us_StationEmail' size='30' value='%s'> ", $us_array['us_StationEmail'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_SignatoryNumber'>Signatory Number: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='us_SignatoryNumber' id='us_SignatoryNumber' size='6' value='%s'> ", $us_array['us_SignatoryNumber'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Signature'>Signature: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='us_Signature' id='us_Signature' size='30' value='%s'> ", $us_array['us_Signature'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_Logo'>Logo Filename: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-5'>" );
    		printf( "<input type='text' class='form-control' name='us_Logo' id='us_Logo' size='30' value='%s'> ", $us_array['us_Logo'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='us_NextPeriodicCertNo'>Next Periodic Cert No.: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='us_NextPeriodicCertNo' id='us_NextPeriodcCertNo' size='10' value='%s'> ", $us_array['us_NextPeriodicCertNo'] );
    		printf( "</div>" );
			printf( "<div class='col-sm-1'>" );
    		printf( "<label for='us_PeriodicCertNoQty'>Qty: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='us_PeriodicCertNoQty' id='us_PeriodcCertNoQty' size='10' value='%s'> ", $us_array['us_PeriodicCertNoQty'] );
    		printf( "</div>" );
    		printf( "</div>" );


    		printf( "<div class='row mt-2'>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "</div>" );
    		printf( "<div class='col form-check'>" );
    		printf( "<label class='form-check-label'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='us_UF_Unused' id='us_UF_Unused' %s> ", ($us_array['us_UF_Unused'] == "Y" ? "checked" : "") );
    		printf( "Unused Option</label>" );
    		printf( "</div>" );
    		printf( "</div>" );
    		
    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateUser' id='UpdateUser' value='Update' %s>Update</button>", ($us_array['us_Username'] == "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewUser' id='NewUser' value='New' %s>New</button>", ((!$new_user && $us_array['us_Username'] != "") || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete user with username %s ?\")", $us_array['us_Username'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteUser' id='DeleteUser' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($us_array['us_Username'] == "" || func_disabled_non_admin() != "" || $us_array['us_Username'] == $_SESSION['us_Username'] ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearUser' id='ClearUser' value='Clear'>Clear</button>" );
            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

