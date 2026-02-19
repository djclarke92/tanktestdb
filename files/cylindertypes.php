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
	$cc_array['ct_CylinderType'] = "";
	$cc_array['ct_Description'] = "";
	$cc_array['error_msg'] = "";
	$cc_array['info_msg'] = "";
}

function func_check_cc_array( &$cc_array )
{
	$cc_array['error_msg'] = "";
	$cc_array['info_msg'] = "";
	
	
	if ( $cc_array['ct_CylinderType'] == "" )
	{
		$cc_array['error_msg'] = "You must enter the 2 character Cylinder Type.";
		return false;
	}
	else if ( strlen($cc_array['ct_CylinderType']) != 2 )
	{
		$cc_array['error_msg'] = "The Cylinder Type must be 2 characters.";
		return false;
	}
	else if ( $cc_array['ct_Description'] == "" )
	{
		$cc_array['error_msg'] = "You must enter the Description.";
		return false;
	}

	return true;
}


$cc_array = array();
func_clear_cc_array( $cc_array );
$new_type = false;



if ( isset( $_GET['ct_CylinderType']) )
	$cc_array['ct_CylinderType'] = $_GET['ct_CylinderType'];
if ( isset( $_POST['ct_CylinderType']) )
	$cc_array['ct_CylinderType'] = $_POST['ct_CylinderType'];
if ( isset( $_POST['ct_Description']) )
	$cc_array['ct_Description'] = $_POST['ct_Description'];
        

if ( isset($_GET['ct_CylinderType']) )
{
	$cc_array['ct_CylinderType'] = $_GET['ct_CylinderType'];

	if ( ($line=$db->GetFields( 'cylindertypes', 'ct_CylinderType', $cc_array['ct_CylinderType'], "ct_Description")) !== false )
	{	// success
		$cc_array['ct_Description'] = stripslashes($line[0]);
	}
	else
	{
		$cc_array['error_msg'] = sprintf( "Failed to read cylindertypes for CylinderType=%s", $cc_array['ct_CylinderType'] );
	}
}
else if ( isset($_POST['DeleteCylinderType']) )
{
	$cc_array['ct_CylinderType'] = $_POST['ct_CylinderType']; 
	if ( $db->DeleteCylinderType( $cc_array['ct_CylinderType'] ) )
	{
		$cc_array['info_msg'] = sprintf( "CylinderType deleted" );
		func_clear_cc_array( $cc_array );
		$new_type = false;
	}
	else 
	{
		$cc_array['error_msg'] = sprintf( "Failed to delete cylinder type with CylinderType=%s", $cc_array['ct_CylinderType'] );
	}
}
else if ( isset($_GET['AddNewCylinderType']) )
{
    func_clear_cc_array( $cc_array );
    $new_type = true;
}
else if ( isset($_POST['NewCylinderType']) || isset($_POST['UpdateCylinderType']) )
{
	if ( isset($_POST['NewCylinderType']) )
	{
	    $new_type = true;
	}
	
	if ( func_check_cc_array( $cc_array ) )
	{
		if ( $new_type && count($db->ReadCylinderTypes($cc_array['ct_CylinderType'])) != 0 )
		{
			$cc_array['error_msg'] = sprintf( "Cylinder Type '%s' already exists", $cc_array['ct_CylinderType'] );
		}
		else if ( $db->UpdateCylinderTypesTable( $new_type, $cc_array['ct_CylinderType'], $cc_array['ct_Description'] ) )
		{	// success
			//func_clear_cc_array( $cc_array );
			
			$cc_array['info_msg'] = "Cylinder Type details saved successfully.";
			$new_type = false;
		}
		else
		{
			$cc_array['error_msg'] = sprintf( "Failed to update Cylinder Type record %s", $cc_array['ct_CylinderType'] );
		}
	}
}
else if ( isset($_POST['ClearCylinderType']) )
{
	func_clear_cc_array( $cc_array );
}


$cylindertypes_list = $db->ReadCylinderTypes("");


?>

<div class="container" style="margin-top:30px">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-5">
			<h3>Cylinder Types</h3>
		</div>
		<div class="col-sm-1">
			<a href='#cylindertypeslist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="cylindertypeslist" class="collapse <?php ($new_type || $cc_array['ct_CylinderType'] != "" ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCylinderType'>Add New Cylinder Type</a></p>" );
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
            foreach ( $cylindertypes_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?ct_CylinderType=%s'>%s</a></td>", $info['ct_CylinderType'], $info['ct_CylinderType'] );
                printf( "<td><a href='?ct_CylinderType=%s'>%s</a></td>", $info['ct_CylinderType'], $info['ct_Description'] );
               
                printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCylinderType'>Add New Cylinder Type</a></p>" );
            }
            ?>

		</div>

	</div>	<!-- end of row -->
	</div>
	
	<?php
    if ( $cc_array['ct_CylinderType'] != "" || $new_type )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<h3>Cylinder Type Detail</h3>

            <?php 
            if ( $cc_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $cc_array['error_msg'] );
            else if ( $cc_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $cc_array['info_msg'] );
            

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ct_CylinderType'>Cylinder Type: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ct_CylinderType' id='ct_CylinderType' size='2' value='%s'> ", $cc_array['ct_CylinderType'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ct_Description'>Description: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='ct_Description' id='ct_Description' size='25' value='%s'> ", $cc_array['ct_Description'] );
    		printf( "</div>" );
    		printf( "</div>" );


    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateCylinderType' id='UpdateCylinderType' value='Update' %s>Update</button>", ($cc_array['ct_CylinderType'] == "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewCylinderType' id='NewCylinderType' value='New' %s>New</button>", ($cc_array['ct_CylinderType'] != "" || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete CylinderType with type %s ?\")", $cc_array['ct_CylinderType'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteCylinderType' id='DeleteCylinderType' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($cc_array['ct_CylinderType'] == "" || func_disabled_non_admin() != "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearCylinderType' id='ClearCylinderType' value='Clear'>Clear</button>" );
            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

