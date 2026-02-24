<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------

function CreatePdf( $db, &$ex_array )
{
	$user = $db->SelectUser( $_SESSION['us_Username'] );
	$customer = $db->ReadCustomers( $ex_array['ex_CustomerNo'], "" );
	$cylinders = $db->ReadCylinders( $ex_array['ex_CylinderNo'], 0, "" );
	$cylinderchecks = $db->ReadCylinderChecks( 0, $cylinders[0]['cy_Material'] );
	$inspections = $db->ReadInspections( $ex_array['ex_ExaminationNo'] );

	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell( 0, 10, 'Cylinder / Document of Certification', 1, 2, 'C' );

	$pdf->ln(4);

	if ( $user['us_Logo'] != "" )
	{
		$file = sprintf( "images/%s", $user['us_Logo'] );
		$pdf->Image( $file, 174, 24, 24 );
	}

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "NZUA Test Station Name:" );
	$pdf->Cell( 56, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s", $user['us_StationName'] );
	$pdf->Cell( 80, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "NZUA Test Station Number:" );
	$pdf->Cell( 60, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s", $user['us_StationNumber'] );
	$pdf->Cell( 20, 6, $txt, 0, 0, 'L' );

	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Date:" );
	$pdf->Cell( 12, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s", $ex_array['ex_ExaminationDate'] );
	$pdf->Cell( 20, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Address:" );
	$pdf->Cell( 20, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s, %s, %s", $user['us_Address1'], $user['us_Address2'], $user['us_Address3'] );
	$pdf->Cell( 80, 6, $txt, 0, 1, 'L' );

	$pdf->ln(1);

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Cylinder Owner:" );
	$pdf->Cell( 35, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s %s", $customer[0]['cu_Firstname'], $customer[0]['cu_Surname'] );
	$pdf->Cell( 60, 6, $txt, 0, 0, 'L' );

	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Phone:" );
	$pdf->Cell( 20, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s", $customer[0]['cu_Phone1'] );
	$pdf->Cell( 40, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Address:" );
	$pdf->Cell( 20, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "%s, %s, %s", $customer[0]['cu_Address1'], $customer[0]['cu_Address2'], $customer[0]['cu_Address3'] );
	$pdf->Cell( 80, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Cylinder Specifications: %s", $cylinders[0]['cy_Specifications'] );
	$pdf->Cell( 90, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Material: %s", $cylinders[0]['ct_Description'] );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Serial No: %s", $cylinders[0]['cy_SerialNo'] );
	$pdf->Cell( 70, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Manufacturer Name: %s", $cylinders[0]['cy_Manufacturer'] );
	$pdf->Cell( 60, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  LAB No: %s", $cylinders[0]['cy_LabNo'] );
	$pdf->Cell( 70, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Date of Manufacture: %s", $cylinders[0]['cy_ManufactureDate'] );
	$pdf->Cell( 60, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Reason for absence: %s", $ex_array['ex_AbsenceReason'] );
	$pdf->Cell( 150, 6, $txt, 0, 0, 'L' );

	
	// new line
	$pdf->Rect( 10, 81, 190, 25 );

	$pdf->SetXY( 12, 82 );
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  External Examination" );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Paint Condition: %s", $ex_array['ex_PaintCondition'] );
	$pdf->Cell( 70, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Colour: %s", $ex_array['ex_Colour'] );
	$pdf->Cell( 60, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Minor abrasions/scratches: %s", ($ex_array['ex_MinorScratches'] == "Y" ? "Yes" : "No") );
	$pdf->Cell( 70, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Serious abrasions/scratches: %s", ($ex_array['ex_SeriousScratches'] == "Y" ? "Yes" : "No") );
	$pdf->Cell( 60, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  External Examination: %s", ($ex_array['ex_ExternalPass'] == "Y" ? "Pass" : "Fail") );
	$pdf->Cell( 70, 6, $txt, 0, 1, 'L' );

	$pdf->ln(4);

	// new line
	$pdf->Rect( 10, 109, 190, 49 );
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  Internal Examination" );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  %s", $cylinders[0]['ct_Description'] );
	$pdf->Cell( 30, 6, $txt, 0, 0, 'L' );

	$count = 0;
	$left = true;
	foreach ( $inspections as $insp )
	{
		$pdf->SetFont('Arial','',12);
		$txt = sprintf( "%s: %s", $insp['cc_Description'], ($insp['in_CheckPositive'] == "Y" ? "Yes" : "No") );
		if ( $left )
			$pdf->SetXY( 30, 115.7+6*$count );
		else
			$pdf->SetXY( 110, 115.7+6*$count );
		$pdf->Cell( 30, 6, $txt, 0, 2, 'L' );

		if ( $left )
			$left = false;
		else
		{
			$left = true;
			$count += 1;
		}
	}

	$pdf->SetXY( 10, 138 );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Other / Notifications: %s", $ex_array['ex_Notes'] );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  Internal Examination: %s", ($ex_array['ex_InternalPass'] ? "Pass" : "Fail") );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Approved ring fitted (ROC): %s", ($ex_array['ex_RingFitted'] ? "Yes" : "No") );
	$pdf->Cell( 70, 6, $txt, 0, 0, 'L' );

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Ring colour: %s", $ex_array['ex_RingColour'] );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	$pdf->SetXY( 10, 162 );

	// new line
	$pdf->Rect( 10, 161, 190, 27 );
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  Hydrostatic Test %s", ($ex_array['ex_TestPressure'] == "" ? "(Not Performed)" : "") );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	if ( $ex_array['ex_TestPressure'] == "" )
	{
		$pdf->Line( 12, 168, 192, 185 );
		$pdf->Line( 12, 185, 192, 168 );
	}

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Test Pressure: %s", $ex_array['ex_TestPressure'] );
	$pdf->Cell( 60, 6, $txt, 0, 0, 'L' );

	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Water Capacity: %s", $ex_array['ex_WaterCapacity'] );
	$pdf->Cell( 60, 6, $txt, 0, 0, 'L' );

	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "MPE: %s cc", $ex_array['ex_MPE'] );
	$pdf->Cell( 30, 6, $txt, 0, 1, 'L' );

	// new line
	$val = "";
	if ( $ex_array['ex_TestPressure'] != "" )
		$val = ($ex_array['ex_AccuracyVerified'] == "Y" ? "Yes" : "No");
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "  Equipment accuracy verified: %s", $val );
	$pdf->Cell( 80, 6, $txt, 0, 0, 'L' );

	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Buret reading at release: %s cc", $ex_array['ex_BuretReading'] );
	$pdf->Cell( 70, 6, $txt, 0, 1, 'L' );

	// new line
	$val = "";
	if ( $ex_array['ex_TestPressure'] > 0 )
		$val = ($ex_array['ex_HydrostaticPass'] == "Y" ? "Pass" : "Fail");
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "  Hydrostatic Test: %s", $val );
	$pdf->Cell( 80, 6, $txt, 0, 0, 'L' );

	$val = "";
	if ( $ex_array['ex_TestPressure'] > 0 )
		$val = ($ex_array['ex_RepeatVisual'] == "Y" ? "Pass" : "Fail");
	$pdf->SetFont('Arial','B',12);
	$txt = sprintf( "Repeat Visual: %s", $val );
	$pdf->Cell( 80, 6, $txt, 0, 1, 'L' );

	$pdf->ln(6);

	// new line
	$pdf->SetFont('Arial','',12);
	$txt = sprintf( "Latest existing hydro mark (logo, numbers, dates). Any alterations to marks noted." );
	$pdf->MultiCell( 45, 6, $txt, 0, 'L', false );

	$pdf->SetXY( 110, 193 );
	$txt = sprintf( "New hydro mark stamped on cylinder." );
	$pdf->MultiCell( 45, 6, $txt, 0, 'L', false );

	$pdf->Rect( 55, 194, 48, 20 );
	$pdf->Rect( 152, 194, 48, 20 );

	$pdf->SetXY( 58, 196 );
	$txt = sprintf( "%s", $ex_array['ex_ExistingHydroMarkText'] );
	$pdf->MultiCell( 40, 6, $txt, 0, 'L', false );

	$pdf->SetXY( 155, 196 );
	$txt = sprintf( "%s", $ex_array['ex_NewHydroMarkText'] );
	$pdf->MultiCell( 40, 6, $txt, 0, 'L', false );

	// new line
	$pdf->SetXY( 10, 220 );
	$txt = sprintf( "Signatory Name: %s", $user['us_Name'] );
	$pdf->Cell( 80, 6, $txt, 0, 0, 'L' );

	$txt = sprintf( "Signatory Number: %s", $user['us_SignatoryNumber'] );
	$pdf->Cell( 80, 6, $txt, 0, 1, 'L' );

	$pdf->ln(4);

	// new line
	$pdf->SetFont('Arial', '', 12 );
	$txt = sprintf( "Signature: " );
	$pdf->Cell( 28, 6, $txt, 0, 0, 'L' );
	$pdf->SetFont('Times', 'I', 14 );
	$txt = sprintf( "%s", $user['us_Signature'] );
	$pdf->Cell( 40, 6, $txt, 0, 1, 'L' );

	// new line
	$pdf->SetXY( 10, 242 );
	$pdf->SetFont('Arial','',10);
	$txt = sprintf( "This Document of Certification must be retained by the cylinder owner and should be produced if required by an 
inspector under the Health and Safety at Work Act 2015, or the cylinder filler. A Document of Certification cannot be 
issued if a cylinder fails the Periodic Test.  The action to be taken in the event of a failure is set out in regulations 
15.56, 15.57 and 15.58 of the Health an Safety at Work (Hazardous Substances) Regulations 2017.");
	$pdf->MultiCell( 0, 6, $txt, 0, 'L', false );

	$pdf->SetXY( 120, 270 );
	$pdf->SetFont('Arial', 'B', 10 );
	$txt = sprintf( "Periodic Certificate No." );
	$pdf->Cell( 28, 6, $txt, 0, 0, 'L' );

	$pdf->SetXY( 162, 269 );
	$pdf->SetTextColor( 255, 0, 0 );
	$pdf->SetFont('Times', 'B', 18 );
	$txt = sprintf( "%s", $ex_array['ex_PeriodicCertNo'] );
	$pdf->Cell( 40, 6, $txt, 0, 0, 'L' );



	$ex_array['ex_Pdf'] = $pdf->Output( 'S' );
}



?>
