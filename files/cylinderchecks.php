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


function func_clear_cc_array( &$cc_array )
{
	$cc_array['cc_CylinderCheckNo'] = 0;
	$cc_array['cc_CylinderType'] = "";
	$cc_array['cc_Description'] = "";
	$cc_array['error_msg'] = "";
	$cc_array['info_msg'] = "";
}

function func_check_cc_array( &$cc_array )
{
	$cc_array['error_msg'] = "";
	$cc_array['info_msg'] = "";
	
	
	if ( $cc_array['cc_CylinderType'] == "" )
	{
		$cc_array['error_msg'] = "You must select the 2 character Cylinder Type.";
		return false;
	}
	else if ( strlen($cc_array['cc_CylinderType']) != 2 )
	{
		$cc_array['error_msg'] = "The Cylinder Type must be 2 characters.";
		return false;
	}
	else if ( $cc_array['cc_Description'] == "" )
	{
		$cc_array['error_msg'] = "You must enter the Description.";
		return false;
	}

	return true;
}


$cc_array = array();
func_clear_cc_array( $cc_array );
$new_check = false;



if ( isset( $_GET['cc_CylinderCheckNo']) )
	$cc_array['cc_CylinderCheckNo'] = $_GET['cc_CylinderCheckNo'];
if ( isset( $_POST['cc_CylinderCheckNo']) )
	$cc_array['cc_CylinderCheckNo'] = $_POST['cc_CylinderCheckNo'];
if ( isset( $_POST['cc_CylinderType']) )
	$cc_array['cc_CylinderType'] = substr($_POST['cc_CylinderType'],0,2);
if ( isset( $_POST['cc_Description']) )
	$cc_array['cc_Description'] = $_POST['cc_Description'];
        

if ( isset($_GET['cc_CylinderCheckNo']) )
{
	$cc_array['cc_CylinderCheckNo'] = $_GET['cc_CylinderCheckNo'];

	if ( ($line=$db->GetFields( 'cylinderchecks', 'cc_CylinderCheckNo', $cc_array['cc_CylinderCheckNo'], "cc_CylinderType,cc_Description")) !== false )
	{	// success
	    $cc_array['cc_CylinderType'] = $line[0];
		$cc_array['cc_Description'] = stripslashes($line[1]);
	}
	else
	{
		$cc_array['error_msg'] = sprintf( "Failed to read cylinderchecks for CylinderCheckNo=%d", $cc_array['cc_CylinderCheckNo'] );
	}
}
else if ( isset($_POST['DeleteCylinderCheck']) )
{
	$cc_array['cc_CylinderCheckNo'] = $_POST['cc_CylinderCheckNo']; 
	if ( $db->DeleteCylinderCheck( $cc_array['cc_CylinderCheckNo'] ) )
	{
		$cc_array['info_msg'] = sprintf( "CylinderCheck deleted" );
		func_clear_cc_array( $cc_array );
		$new_check = false;
	}
	else 
	{
		$cc_array['error_msg'] = sprintf( "Failed to delete cylinder check with CylinderCheck=%d", $cc_array['cc_CylinderCheckNo'] );
	}
}
else if ( isset($_GET['AddNewCylinderCheck']) )
{
    func_clear_cc_array( $cc_array );
    $new_check = true;
}
else if ( isset($_POST['NewCylinderCheck']) || isset($_POST['UpdateCylinderCheck']) )
{
	if ( isset($_POST['NewCylinderCheck']) )
	{
	    $new_check = true;
	}
	
	if ( func_check_cc_array( $cc_array ) )
	{
		if ( $db->UpdateCylinderChecksTable( $new_check, $cc_array['cc_CylinderCheckNo'], $cc_array['cc_CylinderType'], $cc_array['cc_Description'] ) )
		{	// success
			//func_clear_cc_array( $cc_array );
			
			$cc_array['info_msg'] = "Cylinder Check details saved successfully.";
			$new_check = false;
		}
		else
		{
			$cc_array['error_msg'] = sprintf( "Failed to update Cylinder Check record %d", $cc_array['cc_CylinderCheckNo'] );
		}
	}
}
else if ( isset($_POST['ClearCylinderCheck']) )
{
	func_clear_cc_array( $cc_array );
}


$cylinderchecks_list = $db->ReadCylinderChecks(0,"");
$cylindertypes_list = $db->ReadCylinderTypes("");

?>

<div class="container" style="margin-top:30px">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-5">
			<h3>Cylinder Checks</h3>
		</div>
		<div class="col-sm-1">
			<a href='#cylindercheckslist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="cylindercheckslist" class="collapse <?php ($new_check || $cc_array['cc_CylinderCheckNo'] != 0 ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCylinderCheck'>Add New Cylinder Check</a></p>" );
            }
            ?>

    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>Cylinder Type</th>
              <th>Description</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $cylinderchecks_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?cc_CylinderCheckNo=%d'>%s</a></td>", $info['cc_CylinderCheckNo'], $info['cc_CylinderType'] );
                printf( "<td><a href='?cc_CylinderCheckNo=%d'>%s</a></td>", $info['cc_CylinderCheckNo'], $info['cc_Description'] );
               
                printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCylinderCheck'>Add New Cylinder Check</a></p>" );
            }
            ?>

		</div>

	</div>	<!-- end of row -->
	</div>
	
	<?php
    if ( $cc_array['cc_CylinderCheckNo'] != 0 || $new_check )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<h3>Cylinder Check Detail</h3>

            <?php 
            if ( $cc_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $cc_array['error_msg'] );
            else if ( $cc_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $cc_array['info_msg'] );
            

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
			printf( "<input type='hidden' class='form-control' name='cc_CylinderCheckNo' id='cc_CylinderCheckNo' value='%d'>", $cc_array['cc_CylinderCheckNo'] );
    		printf( "<label for='cc_CylinderType'>Cylinder Type: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<select type='text' class='form-control custom-select' name='cc_CylinderType' id='cc_CylinderType' size='1'> " );    		
    		printf( "<option >" );
    		foreach( $cylindertypes_list as $info )
    		{
        		printf( "<option %s>%s %s", ($cc_array['cc_CylinderType'] == $info['ct_CylinderType'] ? "selected" : ""), $info['ct_CylinderType'], $info['ct_Description'] );
        	}
    		printf( "</select>" );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cc_Description'>Description: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cc_Description' id='cc_Description' size='25' value='%s'> ", $cc_array['cc_Description'] );
    		printf( "</div>" );
    		printf( "</div>" );


    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateCylinderCheck' id='UpdateCylinderCheck' value='Update' %s>Update</button>", ($cc_array['cc_CylinderCheckNo'] == 0 ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewCylinderCheck' id='NewCylinderCheck' value='New' %s>New</button>", ($cc_array['cc_CylinderCheckNo'] != 0 || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete CylinderCheck with type '%s %s' ?\")", $cc_array['cc_CylinderType'], $cc_array['cc_Description'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteCylinderCheck' id='DeleteCylinderCheck' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($cc_array['cc_CylinderCheckNo'] == 0 || func_disabled_non_admin() != "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearCylinderCheck' id='ClearCylinderCheck' value='Clear'>Clear</button>" );
            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

