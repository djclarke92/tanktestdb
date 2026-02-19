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



$delete_event_no = 0;
$delete_all_event_no = 0;



if ( isset($_GET['DeleteEventNo']) )
	$delete_event_no = $_GET['DeleteEventNo'];
if ( isset($_GET['DeleteAllEventNo']) )
	$delete_all_event_no = $_GET['DeleteAllEventNo'];



if ( $delete_event_no != 0 )
{
	$db->DeleteEventNo( $delete_event_no );
}
else if ( $delete_all_event_no != 0 )
{
	$db->DeleteAllEventNo( $delete_all_event_no );
}

$records = $db->GetTableRecordCount();

$db_size = $db->GetDatabaseSize();


$now = getdate();

?>


<div class="container" style="margin-top:30px">
	<!-- *************************************************************************** -->


	<!-- *************************************************************************** -->
	<div class="row">


		<!-- *************************************************************************** -->
        <div class="col-sm-4">
		<div id="row_cfd" class="collapse show">
            <h3>Failures</h3>
            
            <table class='table table-striped'>
            <thead class="thead-light">
              <tr>
              <th>Name</th>
              <th>Last Failure</th>
              </tr>
            </thead>

            <?php 
            $printed = false;
            $devices2[] = array( 'de_DeviceNo'=>-3, 'de_Name'=>'Failed Logins' );
            foreach ( $devices2 as $dd )
            {
                $failures = $db->GetDeviceFailures( $dd['de_DeviceNo'] );
                
            	if ( count($failures) > 0 )
            	{
            		$printed = true;
            		printf( "<tr>" );
            		
            		printf( "<td><b>%s %d</b></td>", $dd['de_Name'], count($failures) );
            		
            		$onclick = sprintf( "return confirm(\"Are you sure you want to delete this event ?\")" );
            		printf( "<td>" );
            		printf( "<a href='?DeleteEventNo=%d' onclick='%s;'>%s</a>", $failures[0]['ev_EventNo'], $onclick, $failures[0]['ev_Timestamp'] );
            		
            		printf( "&nbsp;&nbsp;" );
            		
            		$onclick = sprintf( "return confirm(\"Are you sure you want to delete all failure events ?\")" );
            		printf( "<a href='?DeleteAllEventNo=%d' onclick='%s;'>All</a>", $failures[0]['ev_EventNo'], $onclick );
            		printf( "</td>" );
            		
            		printf( "</tr>" );
            	}
            }
            if ( !$printed )
            {
            	printf( "<tr>" );
            	printf( "<td colspan='2'>No recent failures</td>" );
            	printf( "</tr>" );
            }
            printf( "<tr>" );
            $msg = "";
            if ( file_exists("nimrod.certng") )
                $msg = "Certificate Error";
            else if ( file_exists("nimrod.certag") )
                $msg = "Certificate Aging";
            printf( "<td colspan='2'><span id='CNG_00_00' class='text-danger'><span id='CAG_00_00' class='text-danger'>%s&nbsp;</span></span></td>", $msg );
            printf( "</tr>" );
        ?>
            
            </table>
        </div>
        </div>

		<!-- *************************************************************************** -->
        <div class="col-sm-4">
		<div id="row_cfd" class="collapse show">
            <h3>Database</h3>
            
            <table class='table table-striped'>
            <thead class="thead-light">
              <tr>
              <th>Table</th>
              <th>Records</th>
              </tr>
            </thead>

            <?php 
            foreach( $records as $record )
            {
                printf( "<tr>" );
                
                printf( "<td>%s</td><td>%d</td>", $record['table'], $record['count'] );
                
                printf( "</tr>" );
            }
            ?>

            </table>
        </div>
        </div>
	
	</div> <!-- row -->
	
	<!-- *************************************************************************** -->
	<div class="row">

        <div class="col-sm-8">
            Questions, suggestions or comments ? <a href=''>admin&nbsp;'at'&nbsp;tanktestdb.nz</a>
        </div>
    </div>  <!-- row -->

</div>




