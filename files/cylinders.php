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


function func_clear_cy_array( &$cy_array )
{
	$cy_array['cy_CylinderNo'] = 0;
	$cy_array['cy_CustomerNo'] = 0;
	$cy_array['cy_Specifications'] = "";
	$cy_array['cy_SerialNo'] = "";
	$cy_array['cy_Material'] = "";
	$cy_array['cy_Manufacturer'] = "";
	$cy_array['cy_LabNo'] = "";
	$cy_array['cy_ManufactureDate'] = "";
	$cy_array['error_msg'] = "";
	$cy_array['info_msg'] = "";
	$cy_array['name_filter'] = "";
}

function func_check_cy_array( &$cy_array )
{
	$cy_array['error_msg'] = "";
	$cy_array['info_msg'] = "";
	
	
	if ( $cy_array['cy_Specifications'] == "" )
	{
		$cy_array['error_msg'] = "You must enter the Specifications.";
		return false;
	}
	else if ( $cy_array['cy_SerialNo'] == "" )
	{
		$cy_array['error_msg'] = "You must enbetd the Serial No.";
		return false;
	}
	else if ( $cy_array['cy_Material'] == "" )
	{
		$cy_array['error_msg'] = "You must select the Cylinder Material.";
		return false;
	}
	else if ( $cy_array['cy_Manufacturer'] == "" )
	{
		$cy_array['error_msg'] = "You must enter the Manufacturer Name.";
		return false;
	}
	else if ( $cy_array['cy_LabNo'] == "" )
	{
		$cy_array['error_msg'] = "You must enter the LAB No.";
		return false;
	}
	else if ( $cy_array['cy_ManufactureDate'] == "" || !func_is_date_valid($cy_array['cy_ManufactureDate']) )
	{
		$cy_array['error_msg'] = "You must enter the Date of Manufacture.";
		return false;
	}

	return true;
}


$cy_array = array();
func_clear_cy_array( $cy_array );
$new_cylinder = false;



if ( isset($_POST['NameSearch']) && isset($_POST['NameFilter']) )
	$cy_array['name_filter'] = $_POST['NameFilter'];
if ( isset( $_GET['cy_CylinderNo']) )
	$cy_array['cy_CylinderNo'] = $_GET['cy_CylinderNo'];
if ( isset( $_GET['cy_CustomerNo']) )
{
	$cy_array['cy_CustomerNo'] = $_GET['cy_CustomerNo'];
	if ( !isset($_GET['ShowCylinders']) )
	{
		$new_cylinder = true;
	}
}
if ( isset( $_POST['cy_CylinderNo']) )
	$cy_array['cy_CylinderNo'] = $_POST['cy_CylinderNo'];
if ( isset( $_POST['cy_CustomerNo']) )
	$cy_array['cy_CustomerNo'] = $_POST['cy_CustomerNo'];
if ( isset( $_POST['cy_Specifications']) )
	$cy_array['cy_Specifications'] = $_POST['cy_Specifications'];
if ( isset( $_POST['cy_SerialNo']) )
	$cy_array['cy_SerialNo'] = $_POST['cy_SerialNo'];
if ( isset( $_POST['cy_Material']) )
	$cy_array['cy_Material'] = substr($_POST['cy_Material'],0,2);
if ( isset( $_POST['cy_Manufacturer']) )
	$cy_array['cy_Manufacturer'] = $_POST['cy_Manufacturer'];
if ( isset( $_POST['cy_LabNo']) )
	$cy_array['cy_LabNo'] = $_POST['cy_LabNo'];
if ( isset( $_POST['cy_ManufactureDate']) )
	$cy_array['cy_ManufactureDate'] = $_POST['cy_ManufactureDate'];
        

if ( isset($_GET['cy_CylinderNo']) && $_GET['cy_CylinderNo'] != 0 )
{
	$cy_array['cy_CylinderNo'] = $_GET['cy_CylinderNo'];

	if ( ($line=$db->GetFields( 'cylinders', 'cy_CylinderNo', $cy_array['cy_CylinderNo'], "cy_CustomerNo,cy_Specifications,cy_SerialNo,cy_Material,
			cy_Manufacturer,cy_LabNo,cy_ManufactureDate")) !== false )
	{	// success
	    $cy_array['cy_CustomerNo'] = $line[0];
		$cy_array['cy_Specifications'] = $line[1];
		$cy_array['cy_SerialNo'] = $line[2];
		$cy_array['cy_Material'] = $line[3];
		$cy_array['cy_Manufacturer'] = $line[4];
		$cy_array['cy_LabNo'] = $line[5];
		$cy_array['cy_ManufactureDate'] = func_convert_date_format($line[6]);
	}
	else
	{
		$cy_array['error_msg'] = sprintf( "Failed to read cylinders for CylinderNo=%d", $cy_array['cy_CylinderNo'] );
	}
}
else if ( isset($_POST['DeleteCylinder']) )
{
	$cy_array['cy_CylinderNo'] = $_POST['cy_CylinderNo']; 
	if ( $db->DeleteCylinder( $cy_array['cy_CylinderNo'] ) )
	{
		$cy_array['info_msg'] = sprintf( "Cylinder deleted" );
		func_clear_cy_array( $cy_array );
		$new_cylinder = false;
	}
	else 
	{
		$cy_array['error_msg'] = sprintf( "Failed to delete cylinder with CylinderNo=%d", $cy_array['cy_CylinderNo'] );
	}
}
else if ( isset($_GET['AddNewCylinder']) )
{
    func_clear_cy_array( $cy_array );
    $new_cylinder = true;
}
else if ( isset($_POST['NewCylinder']) || isset($_POST['UpdateCylinder']) )
{
	if ( isset($_POST['NewCylinder']) )
	{
	    $new_cylinder = true;
	}
	
	if ( func_check_cy_array( $cy_array ) )
	{
		if ( $db->UpdateCylindersTable( $new_cylinder, $cy_array['cy_CylinderNo'], $cy_array['cy_CustomerNo'], $cy_array['cy_Specifications'], $cy_array['cy_SerialNo'],
			$cy_array['cy_Material'], $cy_array['cy_Manufacturer'], $cy_array['cy_LabNo'], $cy_array['cy_ManufactureDate'] ) )
		{	// success
			//func_clear_cy_array( $cy_array );
			
			$cy_array['info_msg'] = "Cylinder details saved successfully.";
			$new_cylinder = false;
		}
		else
		{
			$cy_array['error_msg'] = sprintf( "Failed to update Cylinder record %d", $cy_array['cy_CylinderNo'] );
		}
	}
}
else if ( isset($_POST['ClearCylinder']) )
{
	func_clear_cy_array( $cy_array );
}


$cylinders_list = $db->ReadCylinders(0,$cy_array['cy_CustomerNo'], $cy_array['name_filter']);
$cylindertypes_list = $db->ReadCylinderTypes("");
$customers_list = $db->ReadCustomers( $cy_array['cy_CustomerNo'], $cy_array['name_filter'] );

?>

<div class="container" style="margin-top:30px">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<h3>Cylinders <?php if ( $cy_array['cy_CustomerNo'] != 0 ) printf("for %s, %s", $customers_list[0]['cu_Surname'], $customers_list[0]['cu_Firstname'] );?></h3>
		</div>
		<div class="col-sm-1">
			<a href='#cylinderslist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="cylinderslist" class="collapse <?php ($new_cylinder || $cy_array['cy_CylinderNo'] != 0 ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-8">
			<?php 
			printf( "<p>" );
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<a href='?AddNewCylinder'>Add New Cylinder</a>" );
            }
			printf( "&nbsp;&nbsp;&nbsp;Surname " );
			printf( "<input type='text' class='' name='NameFilter' id='NameFilter' size='8'>&nbsp;");
			printf( "<button type='submit' class='btn-sm btn-outline-dark' name='NameSearch' id='NameSearch' value='NameSearch'>Search</button>");
            ?>

    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>Specifications</th>
              <th>Serial No</th>
			  <th>Material</th>
			  <th>Customer</th>
			  <th>Address</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $cylinders_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?cy_CylinderNo=%d'>%s</a></td>", $info['cy_CylinderNo'], $info['cy_Specifications'] );
                printf( "<td><a href='?cy_CylinderNo=%d'>%s</a></td>", $info['cy_CylinderNo'], $info['cy_SerialNo'] );
                printf( "<td><a href='?cy_CylinderNo=%d'>%s</a></td>", $info['cy_CylinderNo'], $info['cy_Material'] );
                printf( "<td><a href='?cy_CylinderNo=%d'>%s, %s</a></td>", $info['cy_CylinderNo'], $info['cu_Surname'], $info['cu_Firstname'] );
                printf( "<td><a href='?cy_CylinderNo=%d'>%s</a></td>", $info['cy_CylinderNo'], $info['cu_Address1'] );
               
                printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCylinder'>Add New Cylinder</a></p>" );
            }
            ?>
		</div>

	</div>	<!-- end of row -->
	</div>
	

		<?php
    if ( $cy_array['cy_CustomerNo'] == 0 && $new_cylinder )
    {
    ?>
	<!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-5">
			<h3>Select an existing Customer</h3>
		</div>
    </div>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">

    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>Surname</th>
              <th>Firstname</th>
			  <th>Address</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $customers_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?cy_CylinderNo=%d&cy_CustomerNo=%d'>%s</a></td>", $cy_array['cy_CylinderNo'], $info['cu_CustomerNo'], $info['cu_Surname'] );
                printf( "<td><a href='?cy_CylinderNo=%d&cy_CustomerNo=%d'>%s</a></td>", $cy_array['cy_CylinderNo'], $info['cu_CustomerNo'], $info['cu_Firstname'] );               
                printf( "<td><a href='?cy_CylinderNo=%d&cy_CustomerNo=%d'>%s</a></td>", $cy_array['cy_CylinderNo'], $info['cu_CustomerNo'], $info['cu_Address1'] );
                
				printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
		</div>

	</div>	<!-- end of row -->

	<?php
	}
	?>


	<?php
    if ( $cy_array['cy_CylinderNo'] != 0 || ($new_cylinder && $cy_array['cy_CustomerNo'] != 0) )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
            <?php 
			printf( "<h4>Cylinder Detail for %s, %s</h4>", $customers_list[0]['cu_Surname'], $customers_list[0]['cu_Firstname'] );

            if ( $cy_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $cy_array['error_msg'] );
            else if ( $cy_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $cy_array['info_msg'] );
            

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
			printf( "<input type='hidden' class='form-control' name='cy_CylinderNo' id='cy_CylinderNo' value='%d'>", $cy_array['cy_CylinderNo'] );
			printf( "<input type='hidden' class='form-control' name='cy_CustomerNo' id='cy_CustomerNo' value='%d'>", $cy_array['cy_CustomerNo'] );
    		printf( "<label for='cy_Specifications'>Specifications: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cy_Specifications' id='cy_Specifications' size='25' value='%s'> ", $cy_array['cy_Specifications'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cy_SerialNo'>SerialNo: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cy_SerialNo' id='cy_SerialNo' size='25' value='%s'> ", $cy_array['cy_SerialNo'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cy_Material'>Material: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
			printf( "<select type='text' class='form-control custom-select' name='cy_Material' id='cy_Material' size='1'> " );    		
    		printf( "<option >" );
    		foreach( $cylindertypes_list as $info )
    		{
        		printf( "<option %s>%s %s", ($cy_array['cy_Material'] == $info['ct_CylinderType'] ? "selected" : ""), $info['ct_CylinderType'], $info['ct_Description'] );
        	}
    		printf( "</select>" );    		
			printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cy_Manufacturer'>Manufacturer: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cy_Manufacturer' id='cy_Manufacturer' size='25' value='%s'> ", $cy_array['cy_Manufacturer'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cy_LabNo'>LabNo</label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cy_LabNo' id='cy_LabNo' size='25' value='%s'> ", $cy_array['cy_LabNo'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cy_ManufactureDate'>Manufacture Date: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='cy_ManufactureDate' id='cy_ManufactureDate' size='10' value='%s'> ", $cy_array['cy_ManufactureDate'] );
    		printf( "</div>" );
    		printf( "</div>" );


    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateCylinder' id='UpdateCylinder' value='Update' %s>Update</button>", ($cy_array['cy_CylinderNo'] == 0 ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewCylinder' id='NewCylinder' value='New' %s>New</button>", ($cy_array['cy_CylinderNo'] != 0 || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete Cylinder #%d ?\")", $cy_array['cy_CylinderNo'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteCylinder' id='DeleteCylinder' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($cy_array['cy_CylinderNo'] == 0 || func_disabled_non_admin() != "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearCylinder' id='ClearCylinder' value='Clear'>Clear</button>" );
            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

