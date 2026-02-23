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


function func_clear_ex_array( &$ex_array )
{
	$ex_array['ex_ExaminationNo'] = 0;
	$ex_array['ex_CustomerNo'] = 0;
	$ex_array['ex_CylinderNo'] = 0;
	$ex_array['ex_PaintCondition'] = "";
	$ex_array['ex_Colour'] = "";
	$ex_array['ex_MinorScratches'] = "";
	$ex_array['ex_SeriousScratches'] = "";
	$ex_array['ex_ExternalPass'] = "";
	$ex_array['ex_ExternalFail'] = "";
	$ex_array['ex_Notes'] = "";
	$ex_array['ex_InternalPass'] = "";
	$ex_array['ex_InternalFail'] = "";
	$ex_array['ex_RingFitted'] = "";
	$ex_array['ex_RingColour'] = "";
	$ex_array['ex_TestPressure'] = "";
	$ex_array['ex_WaterCapacity'] = "";
	$ex_array['ex_MPE'] = "";
	$ex_array['ex_AccuracyVerified'] = "";
	$ex_array['ex_BuretReading'] = "";
	$ex_array['ex_HydrostaticPass'] = "";
	$ex_array['ex_HydrostaticFail'] = "";
	$ex_array['ex_RepeatVisual'] = "";
	$ex_array['ex_RepeatVisualFail'] = "";
	$ex_array['ex_ExistingHydroMarkText'] = "";
	$ex_array['ex_ExistingHydroMark'] = "";
	$ex_array['ex_NewHydroMarkText'] = "";
	$ex_array['ex_NewHydroMark'] = "";
	$ex_array['ex_SignatoryUserName'] = "";
	$ex_array['ex_ExaminationDate'] = "";
	$ex_array['ex_PeriodicCertNo'] = "";
	$ex_array['ex_EmailedDate'] = "";
	$ex_array['ex_ReminderDate'] = "";
	$ex_array['ex_AbsenceReason'] = "";
	$ex_array['error_msg'] = "";
	$ex_array['info_msg'] = "";
	$ex_array['name_filter'] = "";
	$ex_array['inspections_exist'] = false;
	$ex_array['start_date'] = "";
	$ex_array['end_date'] = "";
}

function func_check_ex_array( &$ex_array )
{
	$ex_array['error_msg'] = "";
	$ex_array['info_msg'] = "";
	
	
	if ( $ex_array['ex_CustomerNo'] == 0 )
	{
		$ex_array['error_msg'] = "You must select the Customer.";
		return false;
	}
	else if ( $ex_array['ex_CylinderNo'] == 0 )
	{
		$ex_array['error_msg'] = "You must select the Cylinder being examined.";
		return false;
	}
	else if ( $ex_array['ex_PaintCondition'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Paint Condition.";
		return false;
	}
	else if ( $ex_array['ex_Colour'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Paint Colour.";
		return false;
	}
	else if ( ($ex_array['ex_ExternalPass'] == "N" || $ex_array['ex_ExternalPass'] == "") && ($ex_array['ex_ExternalFail'] == "N" || $ex_array['ex_ExternalFail'] == "") )
	{
		$ex_array['error_msg'] = "You must select External Pass or Fail.";
		return false;
	}
	else if ( ($ex_array['ex_InternalPass'] == "N" || $ex_array['ex_InternalPass'] == "") && ($ex_array['ex_InternalFail'] == "N" || $ex_array['ex_InternalFail'] == "") )
	{
		$ex_array['error_msg'] = "You must select Internal Pass or Fail.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] != "" && ($ex_array['ex_HydrostaticPass'] == "N" || $ex_array['ex_HydrostaticPass'] == "") && ($ex_array['ex_HydrostaticFail'] == "N" || $ex_array['ex_HydrostaticFail'] == "") )
	{
		$ex_array['error_msg'] = "You must select Hydrostatic Pass or Fail.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] != "" && ($ex_array['ex_RepeatVisual'] == "N" || $ex_array['ex_RepeatVisual'] == "") && ($ex_array['ex_RepeatVisual'] == "N" || $ex_array['ex_RepeatVisualFail'] == "") )
	{
		$ex_array['error_msg'] = "You must select Repeat Viausl Pass or Fail.";
		return false;
	}
	else if ( $ex_array['ex_PeriodicCertNo'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Periodic Certificate Number.";
		return false;
	}
	else if ( $ex_array['ex_ExaminationDate'] == "" || !func_is_date_valid($ex_array['ex_ExaminationDate']) )
	{
		$ex_array['error_msg'] = "You must enter the Examination Date.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] != "" && $ex_array['ex_WaterCapacity'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Water Capacity.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] != "" && $ex_array['ex_MPE'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the MPE value.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] != "" && $ex_array['ex_BuretReading'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Buret reading at release.";
		return false;
	}
	else if ( $ex_array['ex_TestPressure'] == "" && ($ex_array['ex_BuretReading'] != "" || $ex_array['ex_WaterCapacity'] != "" || $ex_array['ex_BuretReading'] != "" || 
		$ex_array['ex_HydrostaticPass'] != "" || $ex_array['ex_RepeatVisual'] != "") )
	{
		$ex_array['error_msg'] = "You must enter the Test Pressure.";
		return false;
	}
	else if ( !$ex_array['inspections_exist'] )
	{
		$ex_array['error_msg'] = "You must check at least one Internal Inspection.";
		return false;
	}
	else if ( $ex_array['ex_RingFitted'] == "Y" && $ex_array['ex_RingColour'] == "" )
	{
		$ex_array['error_msg'] = "You must enter the Ring Colour if one is fitted.";
		return false;
	}
	else if ( $ex_array['ex_RingColour'] != "" && $ex_array['ex_RingFitted'] != "Y" )
	{
		$ex_array['error_msg'] = "You must check 'Ring Fitted' if you enter the Ring Colour.";
		return false;
	}
	else if ( $ex_array['ex_SeriousScratches'] == "Y" && $ex_array['ex_MinorScratches'] == "Y" )
	{
		$ex_array['error_msg'] = "You cannot check both 'Serious abrasions/scratches' and 'Minor abrasions/scratches'.";
		return false;
	}

	return true;
}


$ex_array = array();
func_clear_ex_array( $ex_array );
$new_exam = false;


if ( isset($_POST['DateSearch']) && isset($_POST['StartDate']) )
{
	$ex_array['start_date'] = $_POST['StartDate'];
	if ( !func_is_date_valid($ex_array['start_date']))
		$ex_array['start_date'] = "";
}
if ( isset($_POST['DateSearch']) && isset($_POST['EndDate']) )
{
	$ex_array['end_date'] = $_POST['EndDate'];
	if ( !func_is_date_valid($ex_array['end_date']) )
		$ex_array['end_date'] = "";
}
if ( isset($_POST['NameSearch']) && isset($_POST['NameFilter']) )
{
	$ex_array['name_filter'] = $_POST['NameFilter'];
	$new_exam = true;
}
if ( isset( $_GET['ex_ExaminationNo']) )
	$ex_array['ex_ExaminationNo'] = $_GET['ex_ExaminationNo'];
if ( isset( $_GET['ex_CustomerNo']) )
{
	$ex_array['ex_CustomerNo'] = $_GET['ex_CustomerNo'];
	$new_exam = true;
}
if ( isset( $_GET['ex_CylinderNo']) )
{
	$ex_array['ex_CylinderNo'] = $_GET['ex_CylinderNo'];
	$new_exam = true;
}
if ( isset( $_POST['ex_ExaminationNo']) )
	$ex_array['ex_ExaminationNo'] = $_POST['ex_ExaminationNo'];
if ( isset( $_POST['ex_CustomerNo']) )
	$ex_array['ex_CustomerNo'] = $_POST['ex_CustomerNo'];
if ( isset( $_POST['ex_CylinderNo']) )
	$ex_array['ex_CylinderNo'] = $_POST['ex_CylinderNo'];
if ( isset( $_POST['ex_PaintCondition']) )
	$ex_array['ex_PaintCondition'] = $_POST['ex_PaintCondition'];
if ( isset( $_POST['ex_Colour']) )
	$ex_array['ex_Colour'] = $_POST['ex_Colour'];
if ( isset( $_POST['ex_MinorScratches']) )
	$ex_array['ex_MinorScratches'] = "Y";
else
	$ex_array['ex_MinorScratches'] = "N";
if ( isset( $_POST['ex_SeriousScratches']) )
	$ex_array['ex_SeriousScratches'] = "Y";
else
	$ex_array['ex_SeriousScratches'] = "N";
if ( isset( $_POST['ex_ExternalPass']) )
	$ex_array['ex_ExternalPass'] = "Y";
else	
	$ex_array['ex_ExternalPass'] = "N";
if ( isset( $_POST['ex_ExternalFail']) )
	$ex_array['ex_ExternalFail'] = "Y";
if ( isset( $_POST['ex_Notes']) )
	$ex_array['ex_Notes'] = $_POST['ex_Notes'];
if ( isset( $_POST['ex_InternalPass']) )
	$ex_array['ex_InternalPass'] = "Y";
else
	$ex_array['ex_InternalPass'] = "N";
if ( isset( $_POST['ex_InternalFail']) )
	$ex_array['ex_InternalFail'] = "Y";
if ( isset( $_POST['ex_RingFitted']) )
	$ex_array['ex_RingFitted'] = "Y";
if ( isset( $_POST['ex_RingColour']) )
	$ex_array['ex_RingColour'] = $_POST['ex_RingColour'];
if ( isset( $_POST['ex_TestPressure']) )
	$ex_array['ex_TestPressure'] = $_POST['ex_TestPressure'];
if ( isset( $_POST['ex_WaterCapacity']) )
	$ex_array['ex_WaterCapacity'] = $_POST['ex_WaterCapacity'];
if ( isset( $_POST['ex_MPE']) )
	$ex_array['ex_MPE'] = $_POST['ex_MPE'];
if ( isset( $_POST['ex_AccuracyVerified']) )
	$ex_array['ex_AccuracyVerified'] = "Y";
else
	$ex_array['ex_AccuracyVerified'] = "N";
if ( isset( $_POST['ex_BuretReading']) )
	$ex_array['ex_BuretReading'] = $_POST['ex_BuretReading'];
if ( isset( $_POST['ex_HydrostaticPass']) )
	$ex_array['ex_HydrostaticPass'] = "Y";
else
	$ex_array['ex_HydrostaticPass'] = "N";
if ( isset( $_POST['ex_HydrostaticFail']) )
	$ex_array['ex_HydrostaticFail'] = "Y";
if ( isset( $_POST['ex_RepeatVisual']) )
	$ex_array['ex_RepeatVisual'] = "Y";
else
	$ex_array['ex_RepeatVisual'] = "N";
if ( isset( $_POST['ex_RepeatVisualFail']) )
	$ex_array['ex_RepeatVisualFail'] = "Y";
if ( isset( $_POST['ex_ExistingHydroMarkText']) )
	$ex_array['ex_ExistingHydroMarkText'] = $_POST['ex_ExistingHydroMarkText'];
if ( isset( $_POST['ex_ExistingHydroMark']) )
	$ex_array['ex_ExistingHydroMark'] = $_POST['ex_ExistingHydroMark'];
if ( isset( $_POST['ex_NewHydroMarkText']) )
	$ex_array['ex_NewHydroMarkText'] = $_POST['ex_NewHydroMarkText'];
if ( isset( $_POST['ex_NewHydroMark']) )
	$ex_array['ex_NewHydroMark'] = $_POST['ex_NewHydroMark'];
if ( isset( $_POST['ex_SignatoryUserName']) )
	$ex_array['ex_SignatoryUserName'] = $_POST['ex_SignatoryUserName'];
if ( isset( $_POST['ex_ExaminationDate']) )
{
	$ex_array['ex_ExaminationDate'] = $_POST['ex_ExaminationDate'];
	if ( !func_is_date_valid($ex_array['ex_ExaminationDate']) )
		$ex_array['ex_ExaminationDate'] = "";
}
if ( isset( $_POST['ex_PeriodicCertNo']) )
	$ex_array['ex_PeriodicCertNo'] = $_POST['ex_PeriodicCertNo'];
if ( isset( $_POST['ex_EmailedDate']) )
{
	$ex_array['ex_EmailedDate'] = $_POST['ex_EmailedDate'];
	if ( !func_is_date_valid($ex_array['ex_EmailedDate']) )
		$ex_array['ex_EmailedDate'] = "";
}
if ( isset( $_POST['ex_ReminderDate']) )
{
	$ex_array['ex_ReminderDate'] = $_POST['ex_ReminderDate'];
	if ( !func_is_date_valid($ex_array['ex_ReminderDate']) )
		$ex_array['ex_ReminderDate'] = "";
}
if ( isset($_POST['ex_AbsenceReason']) )
	$ex_array['ex_AbsenceReason'] = $_POST['ex_AbsenceReason'];

if ( $ex_array['ex_TestPressure'] == "" )
{
	if ( $ex_array['ex_HydrostaticPass'] == "N" )
	{
		$ex_array['ex_HydrostaticPass'] = "";
		$ex_array['ex_HydrostaticFail'] = "";
	}
	if ( $ex_array['ex_RepeatVisual'] == "N" )
	{
		$ex_array['ex_RepeatVisual'] = "";
		$ex_array['ex_RepeatVisualFail'] = "";
	}
}

// check for inspections
if ( isset($ex_array['ex_CylinderNo']) )
{	
	$cylinderchecks_list = $db->ReadCylinderChecks(0,"");
	$exam_cylinder = $db->ReadCylinders($ex_array['ex_CylinderNo'], 0, "" );

	foreach ( $cylinderchecks_list as $info )
	{
		if ( $info['cc_CylinderType'] == $exam_cylinder[0]['cy_Material'] )
		{
			$var = sprintf( "ex_CylinderCheckNo%03d", $info['cc_CylinderCheckNo'] );
			if ( isset($_POST[$var]) )
			{
				$ex_array[$var] = "Y"; 
				$ex_array['inspections_exist'] = true;
			}
		}
	}
} 
        

if ( isset( $_POST['GeneratePdf']) )
{
	CreatePdf( $db, $ex_array );
} 
else if ( isset($_GET['ex_ExaminationNo']) && $_GET['ex_ExaminationNo'] != 0 )
{
	$ex_array['ex_ExaminationNo'] = $_GET['ex_ExaminationNo'];

	if ( ($line=$db->GetFields( 'examinations', 'ex_ExaminationNo', $ex_array['ex_ExaminationNo'], "ex_CustomerNo,ex_CylinderNo,ex_PaintCondition,ex_Colour,ex_MinorScratches,
			ex_SeriousScratches,ex_ExternalPass,ex_Notes,ex_InternalPass,ex_RingFitted,ex_RingColour,ex_TestPressure,ex_WaterCapacity,ex_MPE,ex_AccuracyVerified,ex_BuretReading,
			ex_HydrostaticPass,ex_RepeatVisual,ex_ExistingHydroMark,ex_NewHydroMark,ex_SignatoryUserName,ex_ExaminationDate,ex_PeriodicCertNo,ex_EmailedDate,ex_ReminderDate,
			ex_AbsenceReason,ex_ExistingHydroMarkText,ex_NewHydroMarkText")) !== false )
	{	// success
	    $ex_array['ex_CustomerNo'] = $line[0];
		$ex_array['ex_CylinderNo'] = $line[1];
		$ex_array['ex_PaintCondition'] = stripslashes($line[2]);
		$ex_array['ex_Colour'] = stripslashes($line[3]);
		$ex_array['ex_MinorScratches'] = $line[4];
		$ex_array['ex_SeriousScratches'] = $line[5];
		$ex_array['ex_ExternalPass'] = $line[6];
		$ex_array['ex_Notes'] = stripslashes($line[7]);
		$ex_array['ex_InternalPass'] = $line[8];
		$ex_array['ex_RingFitted'] = $line[9];
		$ex_array['ex_RingColour'] = stripslashes($line[10]);
		$ex_array['ex_TestPressure'] = $line[11];
		$ex_array['ex_WaterCapacity'] = $line[12];
		$ex_array['ex_MPE'] = $line[13];
		$ex_array['ex_AccuracyVerified'] = $line[14];
		$ex_array['ex_BuretReading'] = $line[15];
		$ex_array['ex_HydrostaticPass'] = $line[16];
		$ex_array['ex_RepeatVisual'] = $line[17];
		$ex_array['ex_ExistingHydroMark'] = stripslashes($line[18]);
		$ex_array['ex_NewHydroMark'] = stripslashes($line[19]);
		$ex_array['ex_SignatoryUserName'] = stripslashes($line[20]);
		$ex_array['ex_ExaminationDate'] = func_convert_date_format($line[21]);
		$ex_array['ex_PeriodicCertNo'] = (intval($line[22]) > 0 ? $line[22] : "");
		$ex_array['ex_EmailedDate'] = (func_convert_date_format($line[23]) == "00/00/0000" ? "" : func_convert_date_format($line[23]));
		$ex_array['ex_ReminderDate'] = (func_convert_date_format($line[24]) == "00/00/0000" ? "" : func_convert_date_format($line[24]));
		$ex_array['ex_AbsenceReason'] = stripslashes($line[25]);
		$ex_array['ex_ExistingHydroMarkText'] = stripslashes($line[26]);
		$ex_array['ex_NewHydroMarkText'] = stripslashes($line[27]);

		$ex_array['ex_ExternalFail'] = ($ex_array['ex_ExternalPass'] == "Y" ? "N" : "Y");
		$ex_array['ex_InternalFail'] = ($ex_array['ex_InternalPass'] == "Y" ? "N" : "Y");
		if ( $ex_array['ex_TestPressure'] != "" )
		{
			$ex_array['ex_HydrostaticFail'] = ($ex_array['ex_HydrostaticPass'] == "Y" ? "N" : "Y");
			$ex_array['ex_RepeatVisualFail'] = ($ex_array['ex_RepeatVisual'] == "Y" ? "N" : "Y");
		}
		else
		{
			$ex_array['ex_HydrostaticFail'] = "";
			$ex_array['ex_RepeatVisualFail'] = "";
		}

		// read the inspections
		$inspections = $db->ReadInspections( $ex_array['ex_ExaminationNo'] );
		foreach( $inspections as $info )
		{
			$var = sprintf( "ex_CylinderCheckNo%03d", $info['in_CylinderCheckNo'] );
			$ex_array[$var] = $info['in_CheckPositive'];
		}
	}
	else
	{
		$ex_array['error_msg'] = sprintf( "Failed to read examinations for ExaminationNo=%d", $ex_array['ex_ExaminationNo'] );
	}
}
else if ( isset($_POST['DeleteExamination']) )
{
	$ex_array['ex_ExaminationNo'] = $_POST['ex_ExaminationNo']; 
	if ( $db->DeleteExamination( $ex_array['ex_ExaminationNo'] ) )
	{
		$db->DeleteInspections( $ex_array['ex_ExaminationNo']);
		
		$ex_array['info_msg'] = sprintf( "Examination deleted" );
		func_clear_ex_array( $ex_array );
		$new_exam = false;
	}
	else 
	{
		$ex_array['error_msg'] = sprintf( "Failed to delete examination with ExaminationNo=%d", $ex_array['ex_ExaminationNo'] );
	}
}
else if ( isset($_GET['AddNewExamination']) )
{
    func_clear_ex_array( $ex_array );
    $new_exam = true;
}
else if ( isset($_POST['NewExamination']) || isset($_POST['UpdateExamination']) )
{
	$new_exam = false;
	if ( isset($_POST['NewExamination']) )
	{
	    $new_exam = true;
	}
	
	if ( func_check_ex_array( $ex_array ) )
	{
		if ( $exam_no=$db->UpdateExaminationsTable( $new_exam, $ex_array['ex_ExaminationNo'], $ex_array['ex_CustomerNo'], $ex_array['ex_CylinderNo'],$ex_array['ex_PaintCondition'],$ex_array['ex_Colour'],$ex_array['ex_MinorScratches'],
			$ex_array['ex_SeriousScratches'],$ex_array['ex_ExternalPass'],$ex_array['ex_Notes'],$ex_array['ex_InternalPass'],$ex_array['ex_RingFitted'],$ex_array['ex_RingColour'],$ex_array['ex_TestPressure'],$ex_array['ex_WaterCapacity'],$ex_array['ex_MPE'],$ex_array['ex_AccuracyVerified'],$ex_array['ex_BuretReading'],
			$ex_array['ex_HydrostaticPass'],$ex_array['ex_RepeatVisual'],$ex_array['ex_ExistingHydroMark'],$ex_array['ex_NewHydroMark'],$ex_array['ex_SignatoryUserName'],$ex_array['ex_ExaminationDate'],$ex_array['ex_PeriodicCertNo'],$ex_array['ex_EmailedDate'],$ex_array['ex_ReminderDate'],
			$ex_array['ex_AbsenceReason'], $ex_array['ex_ExistingHydroMarkText'], $ex_array['ex_NewHydroMarkText'] ) )
		{	// success
			//func_clear_ex_array( $ex_array );
			if ( $new_exam )
			{
				$ex_array['ex_ExaminationNo'] = $exam_no;
			}

			// delete all inspections every time
			$db->DeleteInspections( $ex_array['ex_ExaminationNo'] );

			// save the inspections
			foreach ( $cylinderchecks_list as $info )
			{
				if ( $info['cc_CylinderType'] == $exam_cylinder[0]['cy_Material'] )
				{
					$var = sprintf( "ex_CylinderCheckNo%03d", $info['cc_CylinderCheckNo'] );
					
					if ( !$db->UpdateInspectionsTable( true, $ex_array['ex_ExaminationNo'], $info['cc_CylinderCheckNo'], (isset($_POST[$var]) ? "Y" : "N") ) )
					{	// insert failed
						$ex_array['error_msg'] = sprintf( "Failed to save inspections record %d,%d", $ex_array['ex_ExaminationNo'], $info['cc_CylinderCheckNo'] );
					} 
				}
			}

			// consume the periodic cert no
			$db->SetNextPeriodicCertNo();

			$ex_array['info_msg'] = "Examination details saved successfully.";
			$new_exam = false;
		}
		else
		{
			$ex_array['error_msg'] = sprintf( "Failed to update Examinations record %d", $ex_array['ex_ExaminationNo'] );
		}
	}
}
else if ( isset($_POST['ClearExamination']) )
{
	func_clear_ex_array( $ex_array );
}
else if ( isset($_POST['EmailPdf']) )
{

}

$cylinderchecks_list = $db->ReadCylinderChecks(0,"");
$cylindertypes_list = $db->ReadCylinderTypes("");
if ( $ex_array['ex_CustomerNo'] == 0 )
{
	$examinations_list = $db->ReadExaminations(0, $ex_array['start_date'], $ex_array['end_date']);
}
if ( $ex_array['ex_CustomerNo'] == 0 )
{
	$customers_list = $db->ReadCustomers(0, $ex_array['name_filter']);
}
if ( $ex_array['ex_CustomerNo'] != 0 )
{
	$cylinders_list = $db->ReadCylindersForCustomer( $ex_array['ex_CustomerNo'] );
	$exam_cylinder = $db->ReadCylinders( $ex_array['ex_CylinderNo'], 0, "" );
	$customers = $db->ReadCustomers( $ex_array['ex_CustomerNo'], "" );
}

if ( $new_exam && $ex_array['ex_ExaminationDate'] == "" )
{
	$dd = getdate();
	$ex_array['ex_ExaminationDate'] = sprintf("%02d/%02d/%d", $dd['mday'], $dd['mon'], $dd['year'] );
	$ex_array['ex_InternalFail'] = "";
	$ex_array['ex_ExternalFail'] = "";

	$info = $db->GetNextPeriodicCertNo();
	if ( $info !== false )
	{
		$ex_array['ex_PeriodicCertNo'] = $info['us_NextPeriodicCertNo'];
	}
}


?>

<div class="container" style="margin-top:30px">

	<?php
	if ( $ex_array['ex_CustomerNo'] == 0 )
	{
	?>
	<!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-7">
			<h3>Examinations</h3>
		</div>
		<div class="col-sm-1">
			<a href='#examinationslist' data-toggle='collapse' class='small'><i>Hide/Show</i></a>
        </div>
    </div>

	<div id="examinationslist" class="collapse <?php ($new_exam || $ex_array['ex_ExaminationNo'] != 0 ? printf("") : printf("show"))?>">

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-3">
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewExamination'>Add New Examination</a></p>" );
            }
            ?>
		</div>
		<div class="col-sm-6">
			<?php
			printf( "<input type='text' class='' name='StartDate' id='StartDate' size='8' value='%s'>&nbsp;", $ex_array['start_date'] );
			printf( "<input type='text' class='' name='EndDate' id='EndDate' size='8' value='%s'>&nbsp;", $ex_array['end_date'] );
			printf( "<button type='submit' class='btn-sm btn-outline-dark' name='DateSearch' id='DateSearch' value='DateSearch'>Date Search</button>");
			?>
		</div>
	</div>	<!-- end of row -->

    <!-- *************************************************************************** -->
	<div class="row">
		<div class="col-sm-9">

		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
			  <th>Examination No</th>
              <th>Customer</th>
              <th>Cylinder</th>
			  <th>Exam Date</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $examinations_list as $info )
            {
                printf( "<tr>" );
                
				printf( "<td><a href='?ex_ExaminationNo=%d'>%04d</a></td>", $info['ex_ExaminationNo'], $info['ex_ExaminationNo'] );
                printf( "<td><a href='?ex_ExaminationNo=%d'>%s, %s</a></td>", $info['ex_ExaminationNo'], $info['cu_Surname'], $info['cu_Firstname'] );
                printf( "<td><a href='?ex_ExaminationNo=%d'>%s, %s (%s)</a></td>", $info['ex_ExaminationNo'], $info['cy_Specifications'] , $info['cy_SerialNo'], $info['cy_Material'] );               
                printf( "<td><a href='?ex_ExaminationNo=%d'>%s</a></td>", $info['ex_ExaminationNo'], $info['ex_ExaminationDate'] );
                
				printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>
			
			<?php 
            if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
            {
               printf( "<p><a href='?AddNewExamination'>Add New Examination</a></p>" );
            }
            ?>

		</div>

	</div>	<!-- end of row -->
	</div>
	<?php
	}
	?>

	<?php
    if ( $ex_array['ex_CustomerNo'] == 0 && $new_exam )
    {
    ?>
	<!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-10">
			<h3>Select an existing Customer</h3>
		</div>
    </div>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-8">

			<?php
			printf( "<p>" );
			printf( "<input type='text' class='' name='NameFilter' id='NameFilter' size='10'>&nbsp;");
			printf( "<button type='submit' class='btn-sm btn-outline-dark' name='NameSearch' id='NameSearch' value='NameSearch'>Search</button>");
			printf( "</p>" );
			?>

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
                
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $info['cu_CustomerNo'], $info['cu_Surname'] );
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $info['cu_CustomerNo'], $info['cu_Firstname'] );               
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $info['cu_CustomerNo'], $info['cu_Address1'] );
                
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
    if ( $ex_array['ex_CylinderNo'] == 0 && $ex_array['ex_CustomerNo'] != 0 && $new_exam )
    {
    ?>
	<!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-8">
			<h3>Select an existing Cylinder for this Customer</h3>
		</div>
    </div>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-10">

    		<table class='table table-striped'>
    		<thead class="thead-light">
              <tr>
              <th>Specifications</th>
              <th>Serial No</th>
			  <th>Material</th>
              </tr>
            </thead>
 			<tbody>
 			
            <?php 
            foreach ( $cylinders_list as $info )
            {
                printf( "<tr>" );
                
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d&ex_CylinderNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $ex_array['ex_CustomerNo'], 
					$info['cy_CylinderNo'], $info['cy_Specifications'] );
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d&ex_CylinderNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $ex_array['ex_CustomerNo'], 
					$info['cy_CylinderNo'], $info['cy_SerialNo'] );               
                printf( "<td><a href='?ex_ExaminationNo=%d&ex_CustomerNo=%d&ex_CylinderNo=%d'>%s</a></td>", $ex_array['ex_ExaminationNo'], $ex_array['ex_CustomerNo'], 
					$info['cy_CylinderNo'], $info['cy_Material'] );
                
				printf( "</tr>" );
            }
            ?>
			</tbody>
			</table>

			<?php
			printf( "<p><a href='index.php?PageMode=Cylinders&cy_CustomerNo=%d'>Add New Cylinder</a></p>", $ex_array['ex_CustomerNo'] );	
			?>
			
		</div>

	</div>	<!-- end of row -->

	<?php
	}
	?>


	<?php
    if ( $ex_array['ex_ExaminationNo'] != 0 || ($new_exam && $ex_array['ex_CustomerNo'] != 0 && $ex_array['ex_CylinderNo'] != 0) )
    {
    ?>

    <!-- *************************************************************************** -->
	<div class="row">

		<div class="col-sm-10">
			<h3>Examination Detail <?php printf( "for Cylinder %s %s %s", $exam_cylinder[0]['cy_Specifications'], $exam_cylinder[0]['cy_SerialNo'], $exam_cylinder[0]['cy_Material'] );?></h3>
			<h4><?php printf( "Customer %s, %s", $exam_cylinder[0]['cu_Surname'], $exam_cylinder[0]['cu_Firstname'] ); ?></h4>

            <?php 
            if ( $ex_array['error_msg'] != "" )
                printf( "<p class='text-danger'>%s</p>", $ex_array['error_msg'] );
            else if ( $ex_array['info_msg'] != "" )
                printf( "<p class='text-info'>%s</p>", $ex_array['info_msg'] );
            
			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "External Examination" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-3'>" );
			printf( "<input type='hidden' class='form-control' name='ex_ExaminationNo' id='ex_ExaminationNo' value='%d'>", $ex_array['ex_ExaminationNo'] );
			printf( "<input type='hidden' class='form-control' name='ex_CustomerNo' id='ex_CustomerNo' value='%d'>", $ex_array['ex_CustomerNo'] );
			printf( "<input type='hidden' class='form-control' name='ex_CylinderNo' id='ex_CylinderNo' value='%d'>", $ex_array['ex_CylinderNo'] );
    		printf( "<label for='ex_PaintCondition'>Paint Condition: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='ex_PaintCondition' id='ex_PaintCondition' size='20' value='%s' %s> ", $ex_array['ex_PaintCondition'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_Colour'>Colour: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_Colour' id='ex_Colour' size='10' value='%s' %s> ", $ex_array['ex_Colour'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_MinorScratches'>Minor abrasions/scratches: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_MinorScratches' id='ex_MinorScratches' onclick='onclickMinorScratches' %s %s> ", ($ex_array['ex_MinorScratches'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_SeriousScratches'>Serious abrasions/scratches: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_SeriousScratches' id='ex_SeriousScratches' onclick='onclickSeriousScratches' %s %s> ", ($ex_array['ex_SeriousScratches'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_ExternalPass'>External Examination: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_ExternalPass' id='ex_ExternalPass' onclick='onclickExternalPass()' %s %s> Pass", ($ex_array['ex_ExternalPass'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_ExternalFail' id='ex_ExternalFail' onclick='onclickExternalFail()' %s %s> Fail", ($ex_array['ex_ExternalFail'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_AbsenceReason'>Reason for absence: </label>" );
    		printf( "</div>" );
			printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='ex_AbsenceReason' id='ex_AbsenceReason' size='60' value='%s' %s> ", $ex_array['ex_AbsenceReason'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );    		
			printf( "</div>" );

			printf( "</div>" );
			printf( "</div>" );	// end of external exam


			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Internal Examination" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			// inspections here
			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Inspections (%s)", $exam_cylinder[0]['ct_Description'] );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			foreach ( $cylinderchecks_list as $info )
			{
				if ( $info['cc_CylinderType'] == $exam_cylinder[0]['cy_Material'] )
				{
					$var = sprintf( "ex_CylinderCheckNo%03d", $info['cc_CylinderCheckNo'] );
					printf( "<div class='row mt-2'>" ); 
					printf( "<div class='col-sm-3'>" );
					printf( "<label for='%s'>%s: </label>", $var, $info['cc_Description'] );
					printf( "</div>" );
					printf( "<div class='col-sm-1'>" );
					printf( "<input type='checkbox' class='form-check-input' name='%s' id='%s' %s %s>", $var, $var, (isset($ex_array[$var]) && $ex_array[$var] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
					printf( "</div>" );
		    		printf( "</div>" );
				}
			}
			
			printf( "</div>" );
			printf( "</div>" );	// end of inspections


			printf( "<div class='row mt-1'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_Notes'>Other / Notifications: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-6'>" );
    		printf( "<input type='text' class='form-control' name='ex_Notes' id='ex_Notes' size='60' value='%s' %s> ", $ex_array['ex_Notes'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_InternalPass'>Internal Examination: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_InternalPass' id='ex_InternalPass' onclick='onclickInternalPass()' %s %s> Pass", ($ex_array['ex_InternalPass'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_InternalFail' id='ex_InternalFail' onclick='onclickInternalFail()' %s %s> Fail", ($ex_array['ex_InternalFail'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "</div>" );
			printf( "</div>" );	// end of internal exam


			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Hydrostatic Test" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_TestPressure'>Test Pressure: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='text' class='form-control' name='ex_TestPressure' id='ex_TestPressure' size='10' value='%s' %s> ", $ex_array['ex_TestPressure'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );

			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_WaterCapacity'>WaterCapacity: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='text' class='form-control' name='ex_WaterCapacity' id='ex_WaterCapacity' size='10' value='%s' %s> ", $ex_array['ex_WaterCapacity'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );

			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_MPE'>MPE (cc): </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_MPE' id='ex_MPE' size='10' value='%s' %s>", $ex_array['ex_MPE'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_AccuracyVerified'>Accuracy Verified: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_AccuracyVerified' id='ex_AccuracyVerified' %s %s>", ($ex_array['ex_AccuracyVerified'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_BuretReading'>Buret reading at release (cc): </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_BuretReading' id='ex_BuretReading' size='10' value='%s' %s>", $ex_array['ex_BuretReading'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_HydrostaticPass'>Hydrostatic Test: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_HydrostaticPass' id='ex_HydrostaticPass' onclick='onclickHydrostaticPass()' %s %s> Pass", ($ex_array['ex_HydrostaticPass'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_HydrostaticFail' id='ex_HydrostaticFail' onclick='onclickHydrostaticFail()' %s %s> Fail", ($ex_array['ex_HydrostaticFail'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-2'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_RepeatVisual'>Repeat Visual: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_RepeatVisual' id='ex_RepeatVisual' onclick='onclickRepeatVisual()' %s %s> Pass", ($ex_array['ex_RepeatVisual'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_RepeatVisualFail' id='ex_RepeatVisualFail' onclick='onclickRepeatVisualFail()' %s %s> Fail", ($ex_array['ex_RepeatVisualFail'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "</div>" );
			printf( "</div>" );	// end of hydrostatic test


			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Ring Fitted" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_RingFitted'>Approved Ring Fitted (ROC): </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-1'>" );
    		printf( "<input type='checkbox' class='form-check-input' name='ex_RingFitted' id='ex_RingFitted' %s %s> ", ($ex_array['ex_RingFitted'] == "Y" ? "checked" : ""), ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_RingColour'>Colour: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_RingColour' id='ex_RingColour' size='10' value='%s' %s> ", $ex_array['ex_RingColour'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "</div>" );
			printf( "</div>" );	// end of ring fitted


			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Hydro Mark" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-9'>" );
			printf( "<b>Hydro marks (logo, numbers, dates). Any alterations to the existing marks to be noted.</b>" );
			printf( "</div>" );
			printf( "</div>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_ExistingHydroMarkText'>Existing Mark: <br><i>Type here</i></label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<textarea type='text' class='form-input' rows='3' name='ex_ExistingHydroMarkText' id='ex_ExistingHydroMarkText' %s>%s</textarea>", ($ex_array['ex_EmailedDate'] != "" ? "readonly" : ""), $ex_array['ex_ExistingHydroMarkText'] );
    		printf( "</div>" );
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_NewHydroMarkText'>New Mark: <br><i>Type here</i></label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<textarea type='text' class='form-input' rows='3' name='ex_NewHydroMarkText' id='ex_NewHydroMarkText' %s>%s</textarea>", ($ex_array['ex_EmailedDate'] != "" ? "readonly" : ""), $ex_array['ex_NewHydroMarkText'] );
    		printf( "</div>" );
			printf( "</div>" );

			printf( "<div class='row'>" ); 
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_ExistingHydroMarkDraw'>Existing Mark: <br><i>Draw Here</i><br><button type='button' id='ex_EraseExistingMark' class='btn-sm btn-outline-dark' onClick='eraseNewMark()'>Erase</button></label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
			printf( "<canvas id='ex_ExistingHydroMarkDraw' width='200' height='80' style='border:1px solid #000;'></canvas>" );
			printf( "<input type='hidden' name='ex_ExistingHydroMark' id='ex_ExistingHydroMark' value='%s'>", $ex_array['ex_ExistingHydroMark'] );
    		printf( "</div>" );
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_NewHydroMarkDraw'>New Mark: <br><i>Draw Here</i><br><button type='button' id='ex_EraseNewMark' class='btn-sm btn-outline-dark' onClick='eraseNewMark()'>Erase</button></label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
			printf( "<canvas id='ex_NewHydroMarkDraw' width='200' height='80' style='border:1px solid #000;'></canvas>" );
			printf( "<input type='hidden' name='ex_NewHydroMark' id='ex_NewHydroMark' value='%s'>", $ex_array['ex_NewHydroMark'] );
    		printf( "</div>" );
			printf( "</div>" );


			printf( "</div>" );
			printf( "</div>" );	// end of hydro mark


			printf( "<div class='card'>" );
  			printf( "<div class='card-header'>" );
		    printf( "Signatory" );
	  		printf( "</div>" );
  			printf( "<div class='card-body'>" );

			printf( "<div class='row mt-1'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_SignatoryName'>Signatory Name: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-3'>" );
    		printf( "<input type='text' class='form-control' name='ex_SignatoryName' id='ex_SignatoryName' size='20' value='%s' disabled> ", $_SESSION['us_Name'] );
    		printf( "</div>" );
			printf( "<div class='col-sm-2'>" );
    		printf( "<label for='ex_SignatoryNumber'>Signatory No.: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_SignatoryNumber' id='ex_SignatoryNumber' size='10' value='%s' disabled> ", $_SESSION['us_SignatoryNumber'] );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-1'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_ExaminationDate'>Examination Date: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_ExaminationDate' id='ex_ExaminationDate' size='10' value='%s' %s> ", $ex_array['ex_ExaminationDate'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_PeriodicCertNo'>Periodic Cert No.: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_PeriodicCertNo' id='ex_PeriodicCertNo' size='10' value='%s' %s> ", $ex_array['ex_PeriodicCertNo'], ($ex_array['ex_EmailedDate'] != "" ? "readonly" : "") );
    		printf( "</div>" );
    		printf( "</div>" );

			printf( "<div class='row mt-1'>" ); 
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_EmailedDate'>Customer Emailed Date: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_EmailedDate' id='ex_EmailedDate' size='10' value='%s'> ", $ex_array['ex_EmailedDate'] );
    		printf( "</div>" );
			printf( "<div class='col-sm-3'>" );
    		printf( "<label for='ex_ReminderDate'>Reminder Emailed Date: </label>" );
    		printf( "</div>" );
    		printf( "<div class='col-sm-2'>" );
    		printf( "<input type='text' class='form-control' name='ex_ReminderDate' id='ex_ReminderDate' size='10' value='%s' disabled> ", $ex_array['ex_ReminderDate'] );
    		printf( "</div>" );
    		printf( "</div>" );


			printf( "</div>" );
			printf( "</div>" );	// end of signatory


    		printf( "<div class='row mb-2 mt-2'>" ); 
			printf( "<div class='col'>" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='UpdateExamination' id='UpdateExamination' value='Update' onClick='saveCanvasData();' %s>Update</button>", ($ex_array['ex_ExaminationNo'] == 0 ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='NewExamination' id='NewExamination' value='New' %s>New</button>", ($ex_array['ex_ExaminationNo'] != 0 || func_disabled_non_admin() ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            $onclick = sprintf( "return confirm(\"Are you sure you want to delete Examination #%d ?\")", $ex_array['ex_ExaminationNo'] );
            printf( "<button type='submit' class='btn btn-outline-dark' name='DeleteExamination' id='DeleteCylinderCheck' value='Delete' onclick='%s' %s>Delete</button>", $onclick, 
                ($ex_array['ex_ExaminationNo'] == 0 || func_disabled_non_admin() != "" ? "disabled" : "") );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='ClearExamination' id='ClearExamination' value='Clear'>Clear</button>" );
            printf( "&nbsp;&nbsp;&nbsp;" );
            printf( "<button type='submit' class='btn btn-outline-dark' name='GeneratePdf' id='GeneratePdf' value='GeneratePdf'>Generate PDF</button>" );
			if ( file_exists("report.pdf") )
			{
				printf( "&nbsp;<a href='report.pdf' target='_blank'>View PDF</a>");
			}
			printf( "&nbsp;&nbsp;&nbsp;" );
			$disabled = "";
			if ( $ex_array['ex_EmailedDate'] != "" || $customers[0]['cu_Email'] == "" )
				$disabled = "disabled";
            printf( "<button type='submit' class='btn btn-outline-dark' name='EmailPdf' id='EmailPdf' value='EmailPdf' %s>Email PDF</button>", $disabled );
            printf( "</div>" );
    		printf( "</div>" );
            ?>

		</div>
	</div>	<!-- end of row -->

	<?php 
    }
    ?>
    
</div>

<?php
    if ( $ex_array['ex_ExaminationNo'] != 0 || ($new_exam && $ex_array['ex_CustomerNo'] != 0 && $ex_array['ex_CylinderNo'] != 0) )
    {
    ?>
<script>
var lineWidth = 3;
var lineColour = "black";

function myCanvas( canvasId, canvasDataId ) {
	this.canvas = document.getElementById(canvasId);
	this.context = this.canvas.getContext('2d');
	let isDrawing = false;
	let x = 0;
	let y = 0;
	var offsetX;
	var offsetY;

	
	this.canvas.addEventListener('touchstart', handleStart);
	this.canvas.addEventListener('touchend', handleEnd);
	this.canvas.addEventListener('touchcancel', handleCancel);
	this.canvas.addEventListener('touchmove', handleMove);
	this.canvas.addEventListener('mousedown', (e) => {
		x = e.offsetX;
		y = e.offsetY;
		isDrawing = true;
	});

	this.canvas.addEventListener('mousemove', (e) => {
		if (isDrawing) {
			drawLine(this.context, x, y, e.offsetX, e.offsetY);
			x = e.offsetX;
			y = e.offsetY;
			}
	});

	this.canvas.addEventListener('mouseup', (e) => {
		if (isDrawing) {
			drawLine(this.context, x, y, e.offsetX, e.offsetY);
			x = 0;
			y = 0;
			isDrawing = false;
		}
	});
	

	const ongoingTouches = [];

	function handleStart(evt) {
		evt.preventDefault();
		const touches = evt.changedTouches;
		offsetX = this.canvas.getBoundingClientRect().left;
		offsetY = this.canvas.getBoundingClientRect().top;
		for (let i = 0; i < touches.length; i++) {
			ongoingTouches.push(copyTouch(touches[i]));
		}
	}

	function handleMove(evt) {
		evt.preventDefault();
		const touches = evt.changedTouches;
		for (let i = 0; i < touches.length; i++) {
			const color = lineColour;
			const idx = ongoingTouchIndexById(touches[i].identifier);
			if (idx >= 0) {
				this.context.beginPath();
				this.context.moveTo(ongoingTouches[idx].clientX - offsetX, ongoingTouches[idx].clientY - offsetY);
				this.context.lineTo(touches[i].clientX - offsetX, touches[i].clientY - offsetY);
				this.context.lineWidth = lineWidth;	
				this.context.strokeStyle = color;
				this.context.lineJoin = "round";
				this.context.closePath();
				this.context.stroke();
				ongoingTouches.splice(idx, 1, copyTouch(touches[i]));  // swap in the new touch record
			}
		}
	}

	function handleEnd(evt) {
		evt.preventDefault();
		const touches = evt.changedTouches;
		for (let i = 0; i < touches.length; i++) {
			const color = lineColour;
			let idx = ongoingTouchIndexById(touches[i].identifier);
			if (idx >= 0) {
				this.context.lineWidth = lineWidth;
				this.context.fillStyle = color;
				ongoingTouches.splice(idx, 1);  // remove it; we're done
			}
		}
	}

}

function clearArea( cls ) {
	cls.context.setTransform(1, 0, 0, 1, 0, 0);
	cls.context.clearRect(0, 0, cls.context.canvas.width, cls.context.canvas.height);
}


function drawLine(context, x1, y1, x2, y2) {
	context.beginPath();
	context.strokeStyle = lineColour;;
	context.lineWidth = lineWidth;
	context.lineJoin = "round";
	context.moveTo(x1, y1);
	context.lineTo(x2, y2);
	context.closePath();
	context.stroke();
}

function handleCancel(evt) {
  evt.preventDefault();
  const touches = evt.changedTouches;
  for (let i = 0; i < touches.length; i++) {
    let idx = ongoingTouchIndexById(touches[i].identifier);
    ongoingTouches.splice(idx, 1);  // remove it; we're done
  }
}

function copyTouch({ identifier, clientX, clientY }) {
  return { identifier, clientX, clientY };
}

function ongoingTouchIndexById(idToFind) {
  for (let i = 0; i < ongoingTouches.length; i++) {
    const id = ongoingTouches[i].identifier;
    if (id === idToFind) {
      return i;
    }
  }
  return -1;    // not found
}

var canvas1;
var canvas2;

function initCanvas() {
	canvas1 = new myCanvas('ex_ExistingHydroMarkDraw');
	canvas2 = new myCanvas('ex_NewHydroMarkDraw');


	const img1 = document.getElementById('ex_ExistingHydroMark');
  	base_image1 = new Image();
  	base_image1.src = img1.value;
  	base_image1.onload = function(){
    	canvas1.context.drawImage(base_image1, 0, 0);
  	}

	const img2 = document.getElementById('ex_NewHydroMark');
  	base_image2 = new Image();
  	base_image2.src = img2.value;
  	base_image2.onload = function(){
    	canvas2.context.drawImage(base_image2, 0, 0);
  	}
}

function saveCanvasData() {
	saveImageData( canvas1,'ex_ExistingHydroMark');
	saveImageData( canvas2,'ex_NewHydroMark');
}

function saveImageData(cls,dataId) {
	var image_data = cls.canvas.toDataURL("image/png");
	var id = document.getElementById(dataId);
	if ( id )
		id.value = image_data; // Place the image data in to the form
	else
		console.log("no dataId");
}

function eraseExistingMark() {
	clearArea( canvas1 );
}

function eraseNewMark() {
	clearArea( canvas2 );
}

document.addEventListener("DOMContentLoaded", initCanvas);

</script>
<?php } ?>

