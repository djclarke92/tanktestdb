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


function func_clear_cu_array( &$cu_array )
{
	$cu_array['cu_CustomerNo'] = 0;
	$cu_array['cu_Surname'] = "";
	$cu_array['cu_Firstname'] = "";
	$cu_array['cu_Email'] = "";
	$cu_array['cu_Address1'] = "";
	$cu_array['cu_Address2'] = "";
	$cu_array['cu_Address3'] = "";
	$cu_array['cu_PostCode'] = "";
	$cu_array['cu_Phone1'] = "";
	$cu_array['cu_Phone2'] = "";
	$cu_array['cu_Notes'] = "";
	$cu_array['error_msg'] = "";
	$cu_array['info_msg'] = "";
	$cu_array['name_filter'] = "";
}

function func_check_cu_array( &$cu_array )
{
	$cu_array['error_msg'] = "";
	$cu_array['info_msg'] = "";
	
	
	if ( $cu_array['cu_Surname'] == "" )
	{
		$cu_array['error_msg'] = "You must enter the Surname.";
		return false;
	}
	
	return true;
}


$cu_array = array();
func_clear_cu_array( $cu_array );
$new_customer = false;


if ( isset($_POST['NameSearch']) && isset($_POST['NameFilter']) )
	$cu_array['name_filter'] = $_POST['NameFilter'];
if ( isset( $_GET['cu_CustomerNo']) )
	$cu_array['cu_CustomerNo'] = $_GET['cu_CustomerNo'];
if ( isset( $_POST['cu_CustomerNo']) )
	$cu_array['cu_CustomerNo'] = $_POST['cu_CustomerNo'];
if ( isset( $_POST['cu_Surname']) )
	$cu_array['cu_Surname'] = $_POST['cu_Surname'];
if ( isset( $_POST['cu_Firstname']) )
	$cu_array['cu_Firstname'] = $_POST['cu_Firstname'];
if ( isset( $_POST['cu_Email']) )
	$cu_array['cu_Email'] = $_POST['cu_Email'];
if ( isset( $_POST['cu_Address1']) )
	$cu_array['cu_Address1'] = $_POST['cu_Address1'];
if ( isset( $_POST['cu_Address2']) )
	$cu_array['cu_Address2'] = $_POST['cu_Address2'];
if ( isset( $_POST['cu_Address3']) )
	$cu_array['cu_Address3'] = $_POST['cu_Address3'];
if ( isset( $_POST['cu_PostCode']) )
	$cu_array['cu_PostCode'] = $_POST['cu_PostCode'];
if ( isset( $_POST['cu_Phone1']) )
	$cu_array['cu_Phone1'] = $_POST['cu_Phone1'];
if ( isset( $_POST['cu_Phone2']) )
	$cu_array['cu_Phone2'] = $_POST['cu_Phone2'];
if ( isset( $_POST['cu_Notes']) )
	$cu_array['cu_Notes'] = $_POST['cu_Notes'];
        

if ( isset($_GET['cu_CustomerNo']) )
{
	$cu_array['cu_CustomerNo'] = $_GET['cu_CustomerNo'];

	if ( ($line=$db->GetFields( 'customers', 'cu_CustomerNo', $cu_array['cu_CustomerNo'], "cu_Surname,cu_Firstname,cu_Email,
			cu_Address1,cu_Address2,cu_Address3,cu_PostCode,cu_Phone1,cu_Phone2,cu_Notes")) !== false )
	{	// success
		$cu_array['cu_Surname'] = stripslashes($line[0]);
		$cu_array['cu_Firstname'] = stripslashes($line[1]);
		$cu_array['cu_Email'] = stripslashes($line[2]);
		$cu_array['cu_Address1'] = stripslashes($line[3]);
		$cu_array['cu_Address2'] = stripslashes($line[4]);
		$cu_array['cu_Address3'] = stripslashes($line[5]);
		$cu_array['cu_PostCode'] = $line[6];
		$cu_array['cu_Phone1'] = $line[7];
		$cu_array['cu_Phone2'] = $line[8];
		$cu_array['cu_Notes'] = stripslashes($line[9]);
	}
	else
	{
		$cu_array['error_msg'] = sprintf( "Failed to read customers for CustomerNo=%d", $cu_array['cu_CustomerNo'] );
	}
}
else if ( isset($_POST['DeleteCustomer']) )
{
	$cu_array['cu_CustomerNo'] = $_POST['cu_CustomerNo']; 
	if ( $db->DeleteCustomer( $cu_array['cu_CustomerNo'] ) )
	{
		$cu_array['info_msg'] = sprintf( "Customer deleted" );
		func_clear_cu_array( $cu_array );
		$new_customer = false;
	}
	else 
	{
		$cu_array['error_msg'] = sprintf( "Failed to delete customer with CustomerNo=%d", $cu_array['cu_CustomerNo'] );
	}
}
else if ( isset($_GET['AddNewCustomer']) )
{
    func_clear_cu_array( $cu_array );
    $new_customer = true;
}
else if ( isset($_POST['NewCustomer']) || isset($_POST['UpdateCustomer']) )
{
	if ( isset($_POST['NewCustomer']) )
	{
	    $new_customer = true;
	}
	
	if ( func_check_cu_array( $cu_array ) )
	{
		if ( $db->UpdateCustomerTable( $new_customer, $cu_array['cu_CustomerNo'], $cu_array['cu_Surname'], $cu_array['cu_Firstname'], $cu_array['cu_Email'],
		    $cu_array['cu_Address1'], $cu_array['cu_Address2'], $cu_array['cu_Address3'], $cu_array['cu_PostCode'], $cu_array['cu_Phone1'], 
			$cu_array['cu_Phone2'], $cu_array['cu_Notes'] ) )
		{	// success
			//func_clear_cu_array( $cu_array );
			
			$cu_array['info_msg'] = "Customer details saved successfully.";
			$new_customer = false;
		}
		else
		{
			$cu_array['error_msg'] = sprintf( "Failed to update Customer record %s", $cu_array['cu_Username'] );
		}
	}
}
else if ( isset($_POST['ClearCustomer']) )
{
	func_clear_cu_array( $cu_array );
}


$customer_list = $db->ReadCustomers(0,$cu_array['name_filter']);


?>

<div class="container" style="margin-top:30px">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-5">
			<h3>Customers</h3>
		</div>
		<div class="col-sm-1">
			<a href='#customerlist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="customerlist" class="collapse <?php ($new_customer || $cu_array['cu_CustomerNo'] != 0 ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<?php 
			printf( "<p>" );
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<a href='?AddNewCustomer'>Add New Customer</a>" );
            }
			printf( "&nbsp;&nbsp;&nbsp;" );
			printf( "<input type='text' class='' name='NameFilter' id='NameFilter' size='10'>&nbsp;");
			printf( "<button type='submit' class='btn-sm btn-outline-dark' name='NameSearch' id='NameSearch' value='NameSearch'>Search</button>");
			printf( "</p>" );
            ?>

    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>CustomerNo</th>
              <th>Surname</th>
              <th>Firstname</th>              
			  <th>Address</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php
            foreach ( $customer_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?cu_CustomerNo=%d'>%03d</a></td>", $info['cu_CustomerNo'], $info['cu_CustomerNo'] );
                printf( "<td><a href='?cu_CustomerNo=%d'>%s</a></td>", $info['cu_CustomerNo'], $info['cu_Surname'] );
                printf( "<td><a href='?cu_CustomerNo=%d'>%s</a></td>", $info['cu_CustomerNo'], $info['cu_Firstname'] );
                printf( "<td><a href='?cu_CustomerNo=%d'>%s</a></td>", $info['cu_CustomerNo'], $info['cu_Address1'] );
               
                printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewCustomer'>Add New Customer</a></p>" );
            }
            ?>

		</div>

	</div>	<!-- end of row -->
	</div>
	
	<?php
    if ( $cu_array['cu_CustomerNo'] != 0 || $new_customer )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-6">
			<h3>Customer Detail</h3>

            <?php 
            if ( $cu_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $cu_array['error_msg'] );
            else if ( $cu_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $cu_array['info_msg'] );
            

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Surname'>Surname: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cu_Surname' id='cu_Surname' size='25' value='%s'> ", $cu_array['cu_Surname'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Firstname'>Firstname: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-4'>" );
    		printf( "<input type='text' class='form-control' name='cu_Firstname' id='cu_Firstname' size='25' value='%s'> ", $cu_array['cu_Firstname'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Email'>Email: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='cu_Email' id='cu_Email' size='30' value='%s'> ", $cu_array['cu_Email'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Address1'>Address #1: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='cu_Address1' id='cu_Address1' size='30' value='%s'> ", $cu_array['cu_Address1'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Address2'>Address #2: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='cu_Address2' id='cu_Address2' size='30' value='%s'> ", $cu_array['cu_Address2'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Address3'>Address #3: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='cu_Address3' id='cu_Address3' size='30' value='%s'> ", $cu_array['cu_Address3'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_PostCode'>Post Code: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='cu_PostCode' id='cu_PostCode' size='6' value='%s'> ", $cu_array['cu_PostCode'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Phone1'>Phone #1: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='cu_Phone1' id='cu_Phone1' size='20' value='%s'> ", $cu_array['cu_Phone1'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Phone2'>Phone #2: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='cu_Phone2' id='cu_Phone2' size='20' value='%s'> ", $cu_array['cu_Phone2'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='cu_Notes'>Notes: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col'>" );
    		printf( "<input type='text' class='form-control' name='cu_Notes' id='cu_Notes' size='30' value='%s'> ", $cu_array['cu_Notes'] );
    		printf( "</div>" );
    		printf( "</div>" );


    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
     		printf( "<input type='hidden' class='form-control' name='cu_CustomerNo' id='cu_CustomerNo' size='30' value='%d'> ", $cu_array['cu_CustomerNo'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateCustomer' id='UpdateCustomer' value='Update' %s>Update</button>", ($cu_array['cu_Surname'] == "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewCustomer' id='NewCustomer' value='New' %s>New</button>", ($cu_array['cu_Surname'] != "" || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete customer with surname %s ?\")", $cu_array['cu_Surname'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteCustomer' id='DeleteCustomer' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($cu_array['cu_Surname'] == "" || func_disabled_non_admin() != "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearCustomer' id='ClearCustomer' value='Clear'>Clear</button>" );

            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<a href='?PageMode=Cylinders&ShowCylinders=1&cy_CustomerNo=%d'>Show Cylinders</a>", $cu_array['cu_CustomerNo'] );

            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

