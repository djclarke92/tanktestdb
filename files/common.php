<?php
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------


if ( !include_once( "site_config.php" ) )
{
	die("Configuration file 'files/site_config.php' not found !");
}

define( "THIS_DATABASE_VERSION", 100 );

define( "SECURITY_LEVEL_NONE", 0 );
define( "SECURITY_LEVEL_GUEST", 1 );
define( "SECURITY_LEVEL_USER", 2 );
define( "SECURITY_LEVEL_ADMIN", 9 );

// define user options access
define( "E_UF_UNUSED", 0 );
define( "E_UFD_UNUSED", "Unused" );



function func_session_init()
{
	// site admin settings
	if ( !isset($_SESSION['us_AuthLevel']) )
		$_SESSION['us_AuthLevel'] = "";
	if ( !isset($_SESSION['remote_addr']) )
		$_SESSION['remote_addr'] = "";
	if ( !isset($_SESSION['page_mode']) )
		$_SESSION['page_mode'] = "";

	if ( !isset($_SESSION['us_Username']) )
		$_SESSION['us_Username'] = "";
	if ( !isset($_SESSION['us_Name']) )
		$_SESSION['us_Name'] = "";
	if ( !isset($_SESSION['us_SignatoryNumber']) )
		$_SESSION['us_SignatoryNumber'] = "";
	
	date_default_timezone_set( 'Pacific/Auckland' );
}

// check if we need to add new tables or columns
function func_check_database( $db )
{
    $version = false;
    $query = sprintf( "select ev_Value from events where ev_DeviceNo=-2" );
    $result = $db->RunQuery( $query );
    if ( $line = mysqli_fetch_row($result) )
    {
        $version = $line[0];
    }
    $db->FreeQuery($result);
    
    if ( $version === false || $version < 100 )
    {   // we have some work to do
        
        // add us_Features column
//        $query = sprintf( "alter table users add us_Features char(10) not null default 'NNNNNNNNNN'" );
//        $result = $db->RunQuery( $query );
//        if ( func_db_warning_count($db) != 0 )
//        {   // error
//            ReportDBError("Failed to add us_Features column", $db->db_link );
//        }
        

        $version = func_update_database_version( $db, 100 );
    }
 
}

function func_db_warning_count( $db )
{
    $count = -1;
    $query = sprintf( "select @@warning_count" );
    $result = $db->RunQuery( $query );
    if ( $line = mysqli_fetch_row($result) )
    {
        $count = $line[0];
    }
    $db->FreeQuery($result);
    
    return $count;
}

function func_update_database_version( $db, $ver )
{
    // update the database version
    $query = sprintf( "update events set ev_value=%d where ev_DeviceNo=-2", $ver );
    $result = $db->RunQuery( $query );
    if ( mysqli_affected_rows($db->db_link) <= 0 )
    {	// error
        $query = sprintf( "insert into events (ev_DeviceNo,ev_Value) values(-2,%d)", $ver );
        $result = $db->RunQuery( $query );
        if ( mysqli_affected_rows($db->db_link) < 0 )
        {	// error
            ReportDBError("Failed to update database version", $db->db_link );
        }
    }
    
    return $ver;
}

function func_user_logout()
{
	// clear all the session variables
	$_SESSION = array();

	// delete the session cookie
	if ( isset($_COOKIE[session_name()]) )
	{
		setcookie( session_name(), '', time()-42000, '/' );
	}

	session_destroy();

	session_start();

	func_session_init();

	$_SESSION['us_AuthLevel'] = SECURITY_LEVEL_NONE;

	$_SESSION['us_Name'] = "";
	$_SESSION['us_Username'] = "";
	$_SESSION['us_SignatoryNumber'] = "";
}


function ReportDBError($message, $db)
{
	printf( "<div class='error'>%s: %s</div>", $message, mysqli_error($db) );
}

// convert dd/mm/yyyy into dd/mm/yy
function func_MakeShortDate( $dd )
{
	return substr( $dd, 0, 6 ) . substr( $dd, 8, 2 );
}

function func_is_external_connection()
{
	if ( strncmp( $_SERVER['REMOTE_ADDR'], "192.168.", 8 ) == 0 || strncmp( $_SERVER['REMOTE_ADDR'], "127.0.0", 7 ) == 0 || $_SERVER['REMOTE_ADDR'] == "::1" )
	{
		return false;
	}
	
	return true;
}

// convert a db date ccyy-mm-dd into dd/mm/ccyy format
function func_convert_date_format( $date_str )
{
	$dd = "";

	$date_str = rtrim(ltrim($date_str));

	if ( substr( $date_str, 2, 1 ) == "/" )
	{	// convert to db format
		$expl = explode( "/", $date_str );
		$dd = sprintf( "%04d-%02d-%02d", $expl[2], $expl[1], $expl[0] );
	}
	else if ( substr( $date_str, 4, 1 ) == "-" )
	{	// convert to display format
		$expl = explode( "-", $date_str );
		$dd = sprintf( "%02d/%02d/%04d", $expl[2], $expl[1], $expl[0] );

		if ( $expl[2] == 0 )
		{	// date is 0000-00-00
			$dd = "";
		}
	}

	return $dd;
}

// date is in dd/mm/ccyy format
function func_is_date_valid( $d1 )
{
	$expl = explode( "/", $d1 );
	$d = $expl[0];
	$m = $expl[1];
	$y = $expl[2];
	if ( strlen($d1) != 10 )
	{
		$d = 0;
		$m = 0;
		$y = 0;
	}

	if ( checkdate( $m, $d, $y ) )
		return true;
	else
		return false;
}



class MySQLDB
{
	//database handle
	var $db_link;

	//returns true on success, false on error
	function Open($dbHost, $dbUserName, $dbPassword, $dbName)
	{
		$count = 3;
		
		while ( ($this->db_link = mysqli_connect($dbHost, $dbUserName, $dbPassword)) === false && $count > 0 )
		{	// retry, nimrod may be restarting
			$count -= 1;
			sleep( 2 );
		}
		
		if ( $this->db_link === false )
		{
			ReportDBError("Failed to connect to database on $dbHost with username $dbUserName", $this->db_link);
			
			return false;
		}

		if(mysqli_select_db($this->db_link, $dbName) === false)
		{
			ReportDBError("Failed to use $dbName database", $this->db_link);
			return false;
		}

		return true;
	}

	//returns true on success, false on error
	function Close()
	{
		if(mysqli_close($this->db_link) === false)
		{
			ReportDBError("Failed to close database", $this->db_link);
			return false;
		}

		return true;
	}

	//returns result on success, false on error.  Should call FreeQuery on the result.
	function RunQuery($sql)
	{
		$result = mysqli_query($this->db_link, $sql);
		if($result === false)
		{
			ReportDBError("Failed to run query '$sql'", $this->db_link);
			return false;
		}
		return $result;
	}

	//frees memory associated with a query
	function FreeQuery($result)
	{
		if(mysqli_free_result($result) === false)
			ReportDBError("Failed to free result memory", $this->db_link);
	}

	function GetLastInsertId()
	{
		$query = "SELECT LAST_INSERT_ID()";
		$result = $this->RunQuery($query);
		if ( $line = mysqli_fetch_row($result) )
		{
			$this->FreeQuery($result);
			return $line[0];
		}

		$this->FreeQuery($result);

		return false;
	}

	function GetDBError()
	{
		return mysqli_error();
	}

	function GetFields( $table, $name, $no, $fields )
	{
		$query = sprintf( "select %s from %s where %s='%s'", $fields, $table, $name, $no );
		$result = $this->RunQuery($query);
		if ( $line = mysqli_fetch_row($result) )
		{	// found
			$this->FreeQuery($result);
			return $line;
		}

		$this->FreeQuery($result);

		return false;
	}

	function GetDatabaseSize()
	{
		$mb = -1;
		
		$query = sprintf( "select round(sum(data_length + index_length) / 1024 / 1024,1) from information_schema.tables where table_schema='nimrod'" );
		$result = $this->RunQuery($query);
		if ( $line = mysqli_fetch_row($result) )
		{	// found
			$mb = $line[0];
			
			$this->FreeQuery($result);
		}
		
		return $mb;
	}
	

	//*******************************************
	//
	//	events table
	//
	//*******************************************
	function ReadEventCount( $de_no )
	{
		$info = false;
		$query = sprintf( "select count(*) from events where ev_DeviceNo=%d", $de_no );

		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			$info = $line[0];
		}

		$this->FreeQuery($result);

		return $info;
	}
	
	function SaveUserLoginAttempt( $user, $success )
	{
	    $query = sprintf( "insert into events (ev_DeviceNo,ev_Timestamp,ev_Description,ev_Value) values(-3,now(),'%s',%d)",
	        addslashes($user), $success );
	    $result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) < 1 )
	    {	// failed
	    }
	}
	
	// TODO: validate the date and time format ?
	function ReadEventsTable( $sdate, $stime, $duration )
	{
	    $info = array();
	    
	    $datetime = time();
	    $expl1 = explode( "/", $sdate );
	    $expl2 = explode( ":", $stime );
	    if ( isset($expl1[0]) && isset($expl1[1]) && isset($expl1[2]) && isset($expl2[0]) && isset($expl2[1]) )
	    {
	        $year = $expl1[2];
	        $mon = $expl1[1];
	        $day = $expl1[0];
	        $hour = $expl2[0];
	        $min = $expl2[1];
    	    $datetime = mktime( $hour, $min, 0, $mon, $day, $year );
	    }
		else if ( isset($expl1[0]) && isset($expl1[1]) && isset($expl1[2]) )
		{
	        $year = $expl1[2];
	        $mon = $expl1[1];
	        $day = $expl1[0];
	        $hour = 23;
	        $min = 59;
    	    $datetime = mktime( $hour, $min, 0, $mon, $day, $year );
		}
	    
	    //$seconds = func_get_duration( $duration );
		$query = sprintf( "select ev_EventNo,ev_Timestamp,ev_DeviceNo,ev_IOChannel,ev_EventType,ev_Value,ev_Description
			from events where ev_Timestamp>=from_unixtime(%d) and ev_Timestamp<=from_unixtime(%d)
			order by ev_Timestamp desc", $datetime, time() );
		
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'ev_EventNo'=>$line[0], 'ev_Timestamp'=>$line[1], 'ev_DeviceNo'=>$line[2],
				'ev_IOChannel'=>$line[3], 'ev_EventType'=>$line[4], 'ev_Value'=>$line[5],
				'ev_Description'=>stripslashes($line[6]) );
		}
	    
	    $this->FreeQuery($result);
	    
	    return $info;
	}
	
	function DeleteEventNo( $ev_no )
	{
		$query = sprintf( "delete from events where ev_EventNo=%d limit 1", $ev_no );
		$result = $this->RunQuery( $query );
		if ( mysqli_affected_rows($this->db_link) == 1 )
		{	// success
			return true;
		}
		
		return false;
	}

	function DeleteAllEventNo( $ev_no )
	{
		$de_no = -1;
		$di_no = -1;
		
		$query = sprintf( "select ev_DeviceNo,ev_IOChannel from events where ev_EventNo=%d", $ev_no );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			$de_no = $line[0];
			$di_no = $line[1];
		}

		$this->FreeQuery($result);
		
		if ( $de_no > 0 && $di_no >= 0 )
		{
   			$query = sprintf( "delete from events where ev_DeviceNo=%d and ev_IOChannel=%d and ev_EventType=%d", $de_no, $di_no, E_ET_DEVICE_NG );
			$result = $this->RunQuery( $query );
			if ( mysqli_affected_rows($this->db_link) == 1 )
			{	// success
				return true;
			}
		}
		else if ( $de_no == -3 )
		{ // failed login attempts
		    $query = sprintf( "delete from events where ev_DeviceNo=%d and ev_Value=0", $de_no );
		    $result = $this->RunQuery( $query );
		    if ( mysqli_affected_rows($this->db_link) == 1 )
		    {	// success
		        return true;
		    }
		}
		
		return false;
	}
	
	function CleanupEventsTable()
	{
		$query = sprintf( "DELETE FROM events WHERE ev_Timestamp < DATE_SUB(NOW(), INTERVAL 13 MONTH)" );
		$result = $this->RunQuery( $query );
		if ( mysqli_affected_rows($this->db_link) > 0 )
		{
			$query = sprintf( "optimize table events" );
			$result = $this->RunQuery( $query );
				
			return true;
		}
		
		return false;
	}


	//*******************************************
	//
	//	users table
	//
	//*******************************************
	function ReadUsers()
	{
	    $info = array();
	    $query = sprintf( "Select us_Username,us_name,us_Password,us_AuthLevel,us_Features,us_StationName,us_StationNumber,
			us_Address1,us_Address2,us_Address3,us_PostCode,us_StationEmail,us_SignatoryNumber,us_Signature,us_NextPeriodicCertNo,us_PeriodicCertNoQty,
			us_Logo from users" );
	    $result = $this->RunQuery( $query );
	    while ( $line = mysqli_fetch_row($result) )
	    {
	        $info[] = array( 'us_Username'=>stripslashes($line[0]), 'us_Name'=>stripslashes($line[1]), 'us_Password'=>$line[2], 'us_AuthLevel'=>$line[3],
	            'us_Features'=>$line[4], 'us_StationName'=>stripslashes($line[5]), 'us_StationNumber'=>$line[6], 'us_Address1'=>stripslashes($line[7]), 'us_Address2'=>stripslashes($line[8]),
				'us_Address3'=>stripslashes($line[9]), 'us_PostCode'=>$line[10], 'us_StationEmail'=>stripslashes($line[11]), 'us_SignatoryNumber'=>$line[12], 
				'us_Signature'=>stripslashes($line[13]), 'us_NextPeriodicCertNo'=>$line[14], 'us_PeriodicCertNoQty'=>$line[15], 'us_Logo'=>stripslashes($line[16]) );
	    }
	    
	    $this->FreeQuery($result);
	    
	    return $info;
	}
	
	function DeleteUser( $username )
	{
	    $count = 0;
	    $query = sprintf( "select count(*) from users" );
	    $result = $this->RunQuery( $query );
	    if ( $line = mysqli_fetch_row($result) )
	    {
	        $count = $line[0];
	    }
	    
	    $this->FreeQuery($result);
	    
	    // cannot delete the last user
	    if ( $count <= 1 )
	    {
	        return false;
	    }

		$count = 0;
		$query = sprintf( "select count(*) from examinations where ex_SignatoryUserName='%s'", addslashes($username) );
	    $result = $this->RunQuery( $query );
	    if ( $line = mysqli_fetch_row($result) )
	    {
	        $count = $line[0];
	    }
	    
	    $this->FreeQuery($result);
	    
	    if ( $count > 0 )
	    {	// user has some examinations
	        return false;
	    }

	    
	    $query = sprintf( "DELETE FROM users WHERE us_Username='%s'", addslashes($username) );
	    $result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;
	}
	
	function UpdateUserTable( $newuser, $username, $name, $password, $auth_level, $features, $station_name, $station_number, $addr1, $addr2, $addr3, 
		$post_code, $station_email, $signatory_number, $signature, $next_certno, $certno_qty, $logo )
	{
	    $hash = hash( "sha256", $password, FALSE );
	    
	    if ( $newuser )
	    {
	        $query = sprintf( "insert into users (us_Username,us_Name,us_Password,us_AuthLevel,us_Features,us_StationName,us_StationNumber,
				us_Address1,us_Address2,us_Address3,us_PostCode,us_StationEmail,us_SignatoryNumber,us_Signature,us_NextPeriodicCertNo,us_PeriodicCertNoQty,
				us_Logo)  
                values('%s','%s','%s',%d,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%d,%d,'%s')",
	            addslashes($username), addslashes($name), $hash, $auth_level, $features, addslashes($station_name), $station_number, addslashes($addr1), 
				addslashes($addr2), addslashes($addr3), $post_code, addslashes($station_email), $signatory_number, addslashes($signature),
				$next_certno, $certno_qty, addslashes($logo) );
	    }
	    else
	    {
	        $query = sprintf( "update users set us_Name='%s',us_AuthLevel=%d,us_Features='%s',us_StationName='%s',us_StationNumber='%s',us_Address1='%s',us_Address2='%s',
				us_Address3='%s',us_PostCode='%s',us_StationEmail='%s',us_SignatoryNumber='%s',us_Signature='%s',us_NextPeriodicCertNo=%d,us_PeriodicCertNoQty=%d,
				us_Logo='%s'",
	            addslashes($name), $auth_level, $features, addslashes($station_name), $station_number, addslashes($addr1), addslashes($addr2), addslashes($addr3), 
				$post_code, addslashes($station_email), $signatory_number, addslashes($signature), $next_certno, $certno_qty, addslashes($logo) );
	        
	        if ( $password != "" )
	            $query .= sprintf( ",us_Password='%s'", $hash );
	        
	        $query .= sprintf( " where us_Username='%s'", $username );
	    }
	    
	    $result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;
	}
	
	function SelectUser( $username )
	{
	    $info = false;
	    
        $query = sprintf( "select us_Username,us_Name,us_Password,us_AuthLevel,us_Features,us_StationName,us_StationNumber,us_Address1,us_Address2,us_Address3,
			us_PostCode,us_StationEmail,us_SignatoryNumber,us_Signature,us_NextPeriodicCertNo,us_PeriodicCertNoQty,us_Logo  
			from users where us_Username='%s'", addslashes($username) );
        $result = $this->RunQuery( $query );
        if ( $line = mysqli_fetch_row($result) )
        {  
           $info = array( 'us_Username'=>stripslashes($line[0]), 'us_Name'=>stripslashes($line[1]), 'us_Password'=>$line[2], 'us_AuthLevel'=>$line[3],
               	'us_Features'=>$line[4], 'us_StationName'=>stripslashes($line[5]), 'us_StationNumber'=>$line[6], 'us_Address1'=>stripslashes($line[7]), 'us_Address2'=>stripslashes($line[8]), 
				'us_Address3'=>stripslashes($line[9]), 'us_PostCode'=>$line[10], 'us_StationEmail'=>stripslashes($line[11]), 'us_SignatoryNumber'=>$line[12], 
				'us_Signature'=>stripslashes($line[13]), 'us_NextPeriodicCertNo'=>$line[14], 'us_PeriodicCertNoQty'=>$line[15], 'us_Logo'=>stripslashes($line[16]) ); 
        }
        
        $this->FreeQuery($result);
	    
	    return $info;
	}

	function GetNextPeriodicCertNo()
	{
		$info = false;

		$query = sprintf( "select us_NextPeriodicCertNo,us_PeriodicCertNoQty from users where us_Username='%s'", addslashes($_SESSION['us_Username']) );
        $result = $this->RunQuery( $query );
        if ( $line = mysqli_fetch_row($result) )
		{
			$info = array( 'us_NextPeriodicCertNo'=>$line[0], 'us_PeriodicCertNoQty'=>$line[1] );
		}

		$this->FreeQuery($result);

		return $info;
	}

	function SetNextPeriodicCertNo()
	{
		$info = $this->GetNextPeriodicCertNo();
		if ( $info !== false && $info['us_PeriodicCertNoQty'] > 0 )
		{
			$query = sprintf( "update users set us_NextPeriodicCertNo=us_NextPeriodicCertNo+1,us_PeriodicCertNoQty=us_PeriodicCertNoQty-1 where us_Username='%s'", addslashes($_SESSION['us_Username']) );
			$result = $this->RunQuery( $query );
	    	if ( mysqli_affected_rows($this->db_link) >= 0 )
	    	{	// success
	        	return true;
	    	}
		}

		return false;
	}
	
	//*******************************************
	//
	//	customers table
	//
	//*******************************************
	function ReadCustomers( $cust_no, $name_filter )
	{
	    $info = array();
	    $query = sprintf( "Select cu_CustomerNo,cu_Surname,cu_Firstname,cu_Email,cu_Address1,cu_Address2,cu_Address3,cu_PostCode,
			cu_Phone1,cu_Phone2,cu_Notes from customers " );
		if ( $cust_no != 0 )
		{
			$query .= sprintf( " where cu_CustomerNo=%d", $cust_no );
		} 
		else if ( $name_filter != "" ) 
		{
			$query .= sprintf( " where cu_Surname like '%s%%'", $name_filter );
		}
		$query .= " order by cu_Surname,cu_Firstname";
	    $result = $this->RunQuery( $query );
	    while ( $line = mysqli_fetch_row($result) )
	    {
	        $info[] = array( 'cu_CustomerNo'=>$line[0], 'cu_Surname'=>stripslashes($line[1]), 'cu_Firstname'=>stripslashes($line[2]), 'cu_Email'=>stripslashes($line[3]),
	            'cu_Address1'=>stripslashes($line[4]), 'cu_Address2'=>stripslashes($line[5]), 'cu_Address3'=>stripslashes($line[6]), 'cu_PostCode'=>$line[7], 
				'cu_Phone1'=>$line[8], 'cu_Phone2'=>$line[9], 'cu_Notes'=>stripslashes($line[10]) );
	    }
	    
	    $this->FreeQuery($result);
	    
	    return $info;
	}
	
	function DeleteCustomer( $customer_no )
	{
		// do not delete if there are examination records
		$query = sprintf( "select count(*) from examinations where ex_CustomerNo=%d", $customer_no );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
	    {
			if ( $line[0] > 0 )
			{
				return false;
			}
		}

	    $query = sprintf( "DELETE FROM customers WHERE cu_CustomerNo='%d'", $customer_no );
	    $result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;
	}
	
	function UpdateCustomerTable( $newcustomer, $customer_no, $surname, $firstname, $email, $addr1, $addr2, $addr3, 
		$post_code, $phone1, $phone2, $notes )
	{
	    if ( $newcustomer )
	    {
	        $query = sprintf( "insert into customers (cu_Surname,cu_Firstname,cu_Email,
				cu_Address1,cu_Address2,cu_Address3,cu_PostCode,cu_Phone1,cu_Phone2,cu_Notes)  
                values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
	            addslashes($surname), addslashes($firstname), addslashes($email), addslashes($addr1), 
				addslashes($addr2), addslashes($addr3), $post_code, $phone1, $phone2, addslashes($notes) );
	    }
	    else
	    {
	        $query = sprintf( "update customers set cu_Surname='%s',cu_Firstname='%s',cu_Email='%s',cu_Address1='%s',cu_Address2='%s',
				cu_Address3='%s',cu_PostCode='%s',cu_Phone1='%s',cu_Phone2='%s',cu_Notes='%s' 
				where cu_CustomerNo=%d",
	            addslashes($surname), addslashes($firstname), addslashes($email), addslashes($addr1), addslashes($addr2), addslashes($addr3), 
				$post_code, $phone1, $phone2, addslashes($notes), $customer_no );
	    }
	    
	    $result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
			if ( $newcustomer )
				return $this->GetLastInsertId();
			else
		        return true;
	    }
	    
	    return false;
	}
	
	function SelectCustomer( $customer_no )
	{
	    $info = false;
	    
        $query = sprintf( "select cu_CustomerNo,cu_Surname,cu_Firstname,cu_Email,cu_Address1,cu_Address2,cu_Address3,
			cu_PostCode,cu_Phone1,cu_Phone2,cu_Notes 
			from customers where cu_CustomerNo=%d", $customer_no );
        $result = $this->RunQuery( $query );
        if ( $line = mysqli_fetch_row($result) )
        {  
           $info = array( 'cu_CustomerNo'>$line[0], 'cu_Surname'=>stripslashes($line[1]), 'cu_Firstname'=>stripslashes($line[2]), 'cu_Email'=>stripslashes($line[3]), 
               	'cu_Address1'=>stripslashes($line[4]), 'cu_Address2'=>stripslashes($line[5]), 'cu_Address3'=>stripslashes($line[6]), 'cu_PostCode'=>$line[7], 
				'cu_Phone1'=>$line[8], 'cu_Phone2'=>$line[9], 'cu_Notes'=>stripslashes($line[10]) ); 
        }
        
        $this->FreeQuery($result);
	    
	    return $info;
	}

	
	//*******************************************
	//
	//	cylindertypes table
	//
	//*******************************************
	function UpdateCylinderTypesTable( $new_type, $type, $description )
	{
		if ( $new_type )
		{
			return $this->AddCylinderType( $type, $description );
		}
		else
		{
			return $this->UpdateCylinderType( $type, $description );
		}
	}

	function AddCylinderType( $type, $description )
	{
		$query = sprintf( "insert into cylindertypes (ct_CylinderType,ct_Description) values('%s','%s')",
			$type, addslashes($description) );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;
	}

	function UpdateCylinderType( $type, $description )
	{
		$query = sprintf( "update cylindertypes set ct_Description='%s' where ct_CylinderType='%s'", addslashes($description), $type );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;	
	}

	function ReadCylinderTypes( $type )
	{
		$info = array();
		$query = sprintf( "select ct_CylinderType,ct_Description from cylindertypes " );
		if ( $type != "" )
		{
			$query .= sprintf( "where ct_CylinderType='%s'", $type );
		}
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'ct_CylinderType'=>$line[0], 'ct_Description'=>stripslashes($line[1]) );
		}

		return $info;
	}

	function DeleteCylinderType( $type )
	{
		// do not delete if the cylindertype has been used
		$query = sprintf( "select count(*) from cylinderchecks where cc_CylinderType='%s'", $type );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			if ( $line[0] > 0 )
			{
				return false;	
			}
		}

		$query = sprintf( "delete from cylindertypes where ct_CylinderType='%s'", $type );
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;	
	}


	//*******************************************
	//
	//	cylinderchecks table
	//
	//*******************************************
	function UpdateCylinderChecksTable( $new_check, $cc_no, $cyl_type, $desc )
	{
		if ( $new_check )
			return $this->AddCylinderCheck( $cyl_type, $desc );
		else
			return $this->UpdateCylinderCheck( $cc_no, $cyl_type, $desc ); 
	}
	
	function AddCylinderCheck( $type, $description )
	{
		$query = sprintf( "insert into cylinderchecks (cc_CylinderType,cc_Description) values('%s','%s')",
			$type, addslashes($description) );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
			return $this->GetLastInsertId();
	    }
	    
	    return false;
	}

	function UpdateCylinderCheck( $check_no, $cyl_type, $description )
	{
		$query = sprintf( "update cylinderchecks set cc_CylinderType='%s',cc_Description='%s' where cc_CylinderCheckNo=%d", $cyl_type, addslashes($description), $check_no );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;	
	}

	function ReadCylinderChecks( $check_no, $type )
	{
		$info = array();
		$query = sprintf( "select cc_CylinderCheckNo,cc_CylinderType,cc_Description from cylinderchecks " );
		if ( $check_no != 0 )
		{
			$query .= sprintf( " where cc_CylinderCheckNo=%d", $check_no );
		}
		else if ( $type != "" )
		{
			$query .= sprintf( " where cc_CylinderType='%s'", $type );
		}
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'cc_CylinderCheckNo'=>$line[0], 'cc_CylinderType'=>$line[1], 'cc_Description'=>stripslashes($line[2]) );
		}

		return $info;
	}

	function DeleteCylinderCheck( $check_no )
	{
		// do not delete if the cylindercheck has been used
		$query = sprintf( "select count(*) from inspections where in_CylinderCheckNo=%d", $check_no );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			if ( $line[0] > 0 )
			{
				return false;	
			}
		}

		$query = sprintf( "delete from cylinderchecks where cc_CylinderCheckNo=%d", $check_no);
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;	
	}


	//*******************************************
	//
	//	cylinders table
	//
	//*******************************************
	function UpdateCylindersTable( $new_cylinder, $cylinder_no, $customer_no, $specifications, $serial_no, $material, $manufacturer, $lab_no, $mf_date )
	{
		if ( $new_cylinder )
			return $this->AddCylinder( $customer_no, $specifications, $serial_no, $material, $manufacturer, $lab_no, $mf_date );
		else
			return $this->UpdateCylinder( $cylinder_no, $customer_no, $specifications, $serial_no, $material, $manufacturer, $lab_no, $mf_date );
	}

	function AddCylinder( $customer_no, $specifications, $serial_no, $material, $manufacturer, $lab_no, $mf_date )
	{
		$query = sprintf( "insert into cylinders (cy_CustomerNo,cy_Specifications,cy_SerialNo,cy_Material,cy_Manufacturer,cy_LabNo,cy_ManufactureDate)
			values(%d,'%s','%s','%s','%s','%s','%s')",
			$customer_no, addslashes($specifications), addslashes($serial_no), $material, addslashes($manufacturer), addslashes($lab_no), func_convert_date_format($mf_date) );

		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return $this->GetLastInsertId();
	    }
	    
	    return false;
	}

	function UpdateCylinder( $cylinder_no, $customer_no, $specifications, $serial_no, $material, $manufacturer, $lab_no, $mf_date )
	{
		$query = sprintf( "update cylinders set cy_CustomerNo=%d,cy_Specifications='%s',cy_SerialNo='%s',cy_Material='%s',cy_Manufacturer='%s',
			cy_LabNo='%s',cy_ManufactureDate='%s' 
			where cy_CylinderNo=%d",
			$customer_no, addslashes($specifications), addslashes($serial_no), $material, addslashes($manufacturer), addslashes($lab_no), func_convert_date_format($mf_date),
			$cylinder_no );

		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;
	}

	function ReadCylindersForCustomer( $cust_no )
	{
		return $this->ReadCylinders( 0, $cust_no, "" );
	}

	function ReadCylinders( $cylinder_no, $cust_no, $name_filter )
	{
		$info = array();
		$query = sprintf( "select cy_CylinderNo,cy_CustomerNo,cy_Specifications,cy_SerialNo,cy_Material,cy_Manufacturer,cy_LabNo,
			cy_ManufactureDate,cu_Surname,cu_Firstname,cu_Address1,
			ct_Description 
			from cylinders,customers,cylindertypes where cy_CustomerNo=cu_CustomerNo and cy_Material=ct_CylinderType " );
		if ( $cylinder_no != 0 )
		{
			$query .= sprintf( " and cy_CylinderNo=%d", $cylinder_no );
		}
		else if ( $cust_no != 0 )
		{
			$query .= sprintf( " and cy_CustomerNo=%d", $cust_no );
		}
		else if ( $name_filter != "" )
		{
			$query .= sprintf( " and cu_Surname like '%s%%'", $name_filter );
		}
		$result = $this->RunQuery( $query );
	
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'cy_CylinderNo'=>$line[0], 'cy_CustomerNo'=>$line[1], 'cy_Specifications'=>stripslashes($line[2]),
				'cy_SerialNo'=>stripslashes($line[3]), 'cy_Material'=>stripslashes($line[4]),
				'cy_Manufacturer'=>stripslashes($line[5]), 'cy_LabNo'=>stripslashes($line[6]), 'cy_ManufactureDate'=>func_convert_date_format($line[7]),
				'cu_Surname'=>stripslashes($line[8]), 'cu_Firstname'=>stripslashes($line[9]), 'cu_Address1'=>Stripslashes($line[10]),
				'ct_Description'=>stripslashes($line[11]) );
		}

		return $info;
	}

	function DeleteCylinder( $cylinder_no )
	{
		$query = sprintf( "select count(*) from examinations where ex_CylinderNo=%d", $cylinder_no );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			if ( $line[0] > 0 )
			{
				return false;	
			}
		}

		$query = sprintf( "delete from cylinders where cy_CylinderNo=%d", $cylinder_no );
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;
	}


	//*******************************************
	//
	//	inspections table
	//
	//*******************************************
	function UpdateInspectionsTable( $new, $exam_no, $check_no, $positive )
	{
		if ( $new )
			return $this->AddInspections( $exam_no, $check_no, $positive );
		else
			return $this->UpdateInspections( $exam_no, $check_no, $positive );
	}

	function AddInspections( $exam_no, $check_no, $positive )
	{
		$query = sprintf( "insert into inspections (in_ExaminationNo,in_CylinderCheckNo,in_CheckPositive) values(%d,%d,'%s')",
			$exam_no, $check_no, $positive );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
			return $this->GetLastInsertId();
	    }
	    
	    return false;
	}

	function UpdateInspections( $exam_no, $check_no, $positive )
	{
		$query = sprintf( "update inspections set in_CheckPositive='%s' where in_ExaminationNo=%d and in_CylinderCheckNo=%d", 
			$positive, $exam_no, $check_no );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;	
	}

	function ReadInspections( $exam_no )
	{
		$info = array();
		$query = sprintf( "select in_InspectionNo,in_ExaminationNo,in_CylinderCheckNo,in_CheckPositive,
			cc_Description  
			from inspections,cylinderchecks where in_ExaminationNo=%d and in_CylinderCheckNo=cc_CylinderCheckNo",
			$exam_no );
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'in_InspectionNo'=>$line[0], 'in_ExaminationNo'=>$line[1], 'in_CylinderCheckNo'=>stripslashes($line[2]),
				'in_CheckPositive'=>$line[3], 'cc_Description'=>stripslashes($line[4]) );
		}

		return $info;
	}

	function DeleteInspections( $exam_no )
	{
		$query = sprintf( "delete from inspections where in_ExaminationNo=%d", $exam_no);
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;	
	}


	//*******************************************
	//
	//	examinations table
	//
	//*******************************************
	function UpdateExaminationsTable( $new_exam, $exam_no, $cust_no, $cyl_no, $paint, $colour, $minors, $seriouss, $ext_pass, 
		$notes, $int_pass, $ring_fitted, $ring_colour, $test_pressure, $water_cap, $mpe, $accuracy, $buret, $hydro, 
		$repeat, $exist_mark, $new_mark, $sig_username, $exam_date, $cert_no,	$emailed_date, $reminder_date, $absence,
		$exist_text, $new_text )
	{
		if ( $exam_date == "" )
			$exam_date = "00/00/0000";
		if ( $emailed_date == "" )
			$emailed_date = "00/00/0000";
		if ( $reminder_date == "" )
			$reminder_date = "00/00/0000";

		if ( $new_exam )
			return $this->AddExamination( $cust_no, $cyl_no, $paint, $colour, $minors, $seriouss, $ext_pass, 
					$notes, $int_pass, $ring_fitted, $ring_colour, $test_pressure, $water_cap, $mpe, $accuracy, $buret, $hydro, 
					$repeat, $exist_mark, $new_mark, $sig_username, $exam_date, $cert_no,	$emailed_date, $reminder_date, $absence,
					$exist_text, $new_text );
		else
			return $this->UpdateExamination( $exam_no, $cust_no, $cyl_no, $paint, $colour, $minors, $seriouss, $ext_pass, 
					$notes, $int_pass, $ring_fitted, $ring_colour, $test_pressure, $water_cap, $mpe, $accuracy, $buret, $hydro, 
					$repeat, $exist_mark, $new_mark, $sig_username, $exam_date, $cert_no, $emailed_date, $reminder_date, $absence,
					$exist_text, $new_text );
	}

	function AddExamination( $cust_no, $cyl_no, $paint, $colour, $minors, $seriouss, $ext_pass, 
		$notes, $int_pass, $ring_fitted, $ring_colour, $test_pressure, $water_cap, $mpe, $accuracy, $buret, $hydro, 
		$repeat, $exist_mark, $new_mark, $sig_username, $exam_date, $cert_no, $emailed_date, $reminder_date, $absence,
		$exist_text, $new_text )
	{
		$query = sprintf( "insert into examinations (ex_CustomerNo,ex_CylinderNo,ex_PaintCondition,ex_Colour,ex_MinorScratches,ex_SeriousScratches,ex_ExternalPass,
			ex_Notes,ex_InternalPass,ex_RingFitted,ex_RingColour,ex_TestPressure,ex_WaterCapacity,ex_MPE,ex_AccuracyVerified,ex_BuretReading,ex_HydrostaticPass,
			ex_RepeatVisual,ex_ExistingHydroMark,ex_NewHydroMark,ex_SignatoryUserName,ex_ExaminationDate,ex_PeriodicCertNo,ex_EmailedDate,ex_ReminderDate,
			ex_AbsenceReason,ex_ExistingHydroMarkText,ex_NewHydroMarkText) 
			values(%d,%d,'%s','%s','%s','%s','%s',
			'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
			'%s','%s','%s','%s','%s',%d,'%s','%s','%s','%s','%s')",
			$cust_no, $cyl_no, addslashes($paint), addslashes($colour), $minors, $seriouss, $ext_pass, 
			addslashes($notes), $int_pass, $ring_fitted, addslashes($ring_colour),addslashes($test_pressure), addslashes($water_cap), addslashes($mpe),
			$accuracy, addslashes($buret), $hydro, 
			$repeat, addslashes($exist_mark), addslashes($new_mark), addslashes($sig_username), func_convert_date_format($exam_date), addslashes($cert_no), 
			func_convert_date_format($emailed_date), func_convert_date_format($reminder_date), addslashes($absence), addslashes($exist_text), addslashes($new_text) );
		
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return $this->GetLastInsertId();
	    }
	    
	    return false;
	}

	function UpdateExamination( $exam_no, $cust_no, $cyl_no, $paint, $colour, $minors, $seriouss, $ext_pass, 
		$notes, $int_pass, $ring_fitted, $ring_colour, $test_pressure, $water_cap, $mpe, $accuracy, $buret, $hydro, 
		$repeat, $exist_mark, $new_mark, $sig_username, $exam_date, $cert_no, $emailed_date, $reminder_date, $absence,
		$exist_text, $new_text )
	{
		$query = sprintf( "update examinations set ex_CustomerNo=%d,ex_CylinderNo=%d,ex_PaintCondition='%s',ex_Colour='%s',ex_MinorScratches='%s',ex_SeriousScratches='%s',
			ex_ExternalPass='%s',
			ex_Notes='%s',ex_InternalPass='%s',ex_RingFitted='%s',ex_RingColour='%s',ex_TestPressure='%s',ex_WaterCapacity='%s',ex_MPE='%s',ex_AccuracyVerified='%s',
			ex_BuretReading='%s',ex_HydrostaticPass='%s',
			ex_RepeatVisual='%s',ex_ExistingHydroMark='%s',ex_NewHydroMark='%s',ex_SignatoryUserName='%s',ex_ExaminationDate='%s',ex_PeriodicCertNo='%s',ex_EmailedDate='%s',
			ex_ReminderDate='%s',ex_AbsenceReason='%s',ex_ExistingHydroMarkText='%s',ex_NewHydroMarkText='%s' 
			where ex_ExaminationNo=%d",
			$cust_no, $cyl_no, addslashes($paint), addslashes($colour), $minors, $seriouss, $ext_pass, 
			addslashes($notes), $int_pass, $ring_fitted, addslashes($ring_colour), addslashes($test_pressure), addslashes($water_cap), addslashes($mpe),
			$accuracy, addslashes($buret), $hydro, 
			$repeat, addslashes($exist_mark), addslashes($new_mark), addslashes($sig_username), func_convert_date_format($exam_date), addslashes($cert_no), 
			func_convert_date_format($emailed_date), func_convert_date_format($reminder_date), addslashes($absence), addslashes($exist_text), addslashes($new_text),  
			$exam_no );
		//printf("<p>&nbsp;<br>&nbsp;<br>%s</p>", $query);
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) >= 0 )
	    {	// success
	        return true;
	    }
	    
	    return false;	
	}

	function ReadExaminations( $exam_no, $start_date, $end_date )
	{
		$info = array();
		$query = sprintf( "select ex_ExaminationNo,ex_CustomerNo,ex_CylinderNo,ex_PaintCondition,ex_Colour,ex_MinorScratches,ex_SeriousScratches,ex_ExternalPass,
			ex_Notes,ex_InternalPass,ex_RingFitted,ex_RingColour,ex_TestPressure,ex_WaterCapacity,ex_MPE,ex_AccuracyVerified,ex_BuretReading,ex_HydrostaticPass,
			ex_RepeatVisual,ex_ExistingHydroMark,ex_NewHydroMark,ex_SignatoryUserName,ex_ExaminationDate,ex_PeriodicCertNo,ex_EmailedDate,ex_ReminderDate,
			ex_AbsenceReason,ex_ExistingHydroMarkText,ex_NewHydroMarkText,
			cu_Surname,cu_Firstname,cu_Address1,cu_Email,
			cy_Specifications,cy_SerialNo,cy_Material 
			from examinations,customers,cylinders where ex_CustomerNo=cu_CustomerNo and ex_CylinderNo=cy_CylinderNo " );
		if ( $exam_no != 0 )
		{
			$query .= sprintf( " and ex_ExaminationNo=%d", $exam_no );
		}
		else if ( $start_date != "" )
		{
			$query .= sprintf( " and ex_ExaminationDate>='%s' and ex_ExaminationDate<='%s'", func_convert_date_format($start_date), func_convert_date_format($end_date) );
		}
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'ex_ExaminationNo'=>$line[0],'ex_CustomerNo'=>$line[1],'ex_CylinderNo'=>$line[2],'ex_PaintCondition'=>stripslashes($line[3]),'ex_Colour'=>stripslashes($line[4]),'ex_MinorScratches'=>$line[5],'ex_SeriousScratches'=>$line[6],'ex_ExternalPass'=>$line[7],
				'ex_Notes'=>stripslashes($line[8]),'ex_InternalPass'=>$line[9],'ex_RingFitted'=>$line[10],'ex_RingColour'=>stripslashes($line[11]),'ex_TestPressure'=>$line[12],'ex_WaterCapacity'=>$line[13],'ex_MPE'=>$line[14],'ex_AccuracyVerified'=>$line[15],'ex_BuretReading'=>$line[16],'ex_HydrostaticPass'=>$line[17],
				'ex_RepeatVisual'=>$line[18],'ex_ExistingHydroMark'=>stripslashes($line[19]),'ex_NewHydroMark'=>stripslashes($line[20]),'ex_SignatoryUserName'=>stripslashes($line[21]),'ex_ExaminationDate'=>func_convert_date_format($line[22]),
				'ex_PeriodicCertNo'=>$line[23],'ex_EmailedDate'=>func_convert_date_format($line[24]),'ex_ReminderDate'=>func_convert_date_format($line[25]), 
				'ex_AbsenceReason'=>stripslashes($line[26]),'ex_ExistingHydroMarkText'=>stripslashes($line[27]), 'ex_NewHydroMarkText'=>stripslashes($line[28]),
				'cu_Surname'=>stripslashes($line[29]), 'cu_Firstname'=>stripslashes($line[30]), 'cu_Address1'=>stripslashes($line[31]), 'cu_Email'=>stripslashes($line[32]),
				'cy_Specifications'=>stripslashes($line[33]), 'cy_SerialNo'=>stripslashes($line[34]), 'cy_Material'=>$line[35] );
		}

		return $info;
	}

	function ReadExaminationsForCustomer( $cust_no )
	{
		$info = array();
		$query = sprintf( "select ex_ExaminationNo,ex_CustomerNo,ex_CylinderNo,ex_PaintCondition,ex_Colour,ex_MinorScratches,ex_SeriousScratches,ex_ExternalPass,
			ex_Notes,ex_InternalPass,ex_RingFitted,ex_RingColour,ex_TestPressure,ex_WaterCapacity,ex_MPE,ex_AccuracyVerified,ex_BuretReading,ex_HydrostaticPass,
			ex_RepeatVisual,ex_ExistingHydroMark,ex_NewHydroMark,ex_SignatoryUserName,ex_ExaminationDate,ex_PeriodicCertNo,ex_EmailedDate,ex_ReminderDate,
			ex_AbsenceReason,ex_ExistingHydroMarkText,ex_NewHydroMarkText  
			from examinations where ex_CustomerNo=%d order by ex_ExaminationDate",
			$cust_no );
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'ex_ExaminationNo'=>$line[0],'ex_CustomerNo'=>$line[1],'ex_CylinderNo'=>$line[2],'ex_PaintCondition'=>$line[3],'ex_Colour'=>$line[4],'ex_MinorScratches'=>$line[5],'ex_SeriousScratches'=>$line[6],'ex_ExternalPass'=>$line[7],
				'ex_Notes'=>stripslashes($line[8]),'ex_InternalPass'=>$line[9],'ex_RingFitted'=>$line[10],'ex_RingColour'=>$line[11],'ex_TestPressure'=>$line[12],'ex_WaterCapacity'=>$line[13],'ex_MPE'=>$line[14],'ex_AccuracyVerified'=>$line[15],'ex_BuretReading'=>$line[16],'ex_HydrostaticPass'=>$line[17],
				'ex_RepeatVisual'=>$line[18],'ex_ExistingHydroMark'=>$line[19],'ex_NewHydroMark'=>$line[20],'ex_SignatoryUserName'=>stripslashes($line[21]),'ex_ExaminationDate'=>func_convert_date_format($line[22]),
				'ex_PeriodicCertNo'=>$line[23],'ex_EmailedDate'=>func_convert_date_format($line[24]),'ex_ReminderDate'=>func_convert_date_format($line[25]), 
				'ex_AbsenceReason'=>stripslashes($line[26]),'ex_ExistingHydroMarkText'=>stripslashes($line[27]), 'ex_NewHydroMarkText'=>stripslashes($line[28]) );
		}

		return $info;
	}

	function ReadExaminationsForCylinder( $cyl_no )
	{
		$info = array();
		$query = sprintf( "select ex_ExaminationNo,ex_CustomerNo,ex_CylinderNo,ex_PaintCondition,ex_Colour,ex_MinorScratches,ex_SeriousScratches,ex_ExternalPass,
			ex_Notes,ex_InternalPass,ex_RingFitted,ex_RingColour,ex_TestPressure,ex_WaterCapacity,ex_MPE,ex_AccuracyVerified,ex_BuretReading,ex_HydrostaticPass,
			ex_RepeatVisual,ex_ExistingHydroMark,ex_NewHydroMark,ex_SignatoryUserName,ex_ExaminationDate,ex_PeriodicCertNo,ex_EmailedDate,ex_ReminderDate,
			ex_AbsenceReason,ex_ExistingHydroMarkText,ex_NewHydroMarkText  
			from examinations where ex_CylinderNo=%d order by ex_ExaminationDate",
			$cyl_no );
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
			$info[] = array( 'ex_ExaminationNo'=>$line[0],'ex_CustomerNo'=>$line[1],'ex_CylinderNo'=>$line[2],'ex_PaintCondition'=>$line[3],'ex_Colour'=>$line[4],'ex_MinorScratches'=>$line[5],'ex_SeriousScratches'=>$line[6],'ex_ExternalPass'=>$line[7],
				'ex_Notes'=>$line[8],'ex_InternalPass'=>$line[9],'ex_RingFitted'=>$line[10],'ex_RingColour'=>$line[11],'ex_TestPressure'=>$line[12],'ex_WaterCapacity'=>$line[13],'ex_MPE'=>$line[14],'ex_AccuracyVerified'=>$line[15],'ex_BuretReading'=>$line[16],'ex_HydrostaticPass'=>$line[17],
				'ex_RepeatVisual'=>$line[18],'ex_ExistingHydroMark'=>$line[19],'ex_NewHydroMark'=>$line[20],'ex_SignatoryUserName'=>$line[21],'ex_ExaminationDate'=>$line[22],
				'ex_PeriodicCertNo'=>$line[23],'ex_EmailedDate'=>$line[24],'ex_ReminderDate'=>$line[25], 'ex_AbsenceReason'=>stripslashes($line[26]),
				'ex_ExistingHydroMarkText'=>stripslashes($line[27]), 'ex_NewHydroMarkText'=>stripslashes($line[28]) );
		}

		return $info;
	}

	function DeleteExamination( $exam_no )
	{
		// do not delete if the cylindertype has been used
		$query = sprintf( "select unix_timestamp(ex_EmailedDate) from examinations where ex_ExaminationNo=%d", $exam_no );
		$result = $this->RunQuery( $query );
		if ( $line = mysqli_fetch_row($result) )
		{
			if ( $line[0] > 0 )
			{
				return false;	
			}
		}

		$query = sprintf( "delete from examinations where ex_ExaminationNo=%d", $exam_no );
		$result = $this->RunQuery( $query );
	    if ( mysqli_affected_rows($this->db_link) == 1 )
	    {
	        return true;
	    }
	    
	    return false;	
	}



	//*******************************************
	//
	//	misc database functions
	//
	//*******************************************
	function GetTableRecordCount()
	{
	    $info = array();
	    $tables = array();
	    $tables[] = "examinations";
		$tables[] = "inspections";
	    $tables[] = "cylinders";
	    $tables[] = "cylinderchecks";
	    $tables[] = "cylindertypes";
	    $tables[] = "users";
		$tables[] = "customers";
	    $tables[] = "events";
	    
	    foreach ( $tables as $table )
	    {
	        $query = sprintf( "select table_rows from information_schema.tables where table_name='%s' and table_schema='tanktest'", $table );
			$query = sprintf( "select count(*) from %s", $table );
	        $result = $this->RunQuery( $query );
	        if ( $line = mysqli_fetch_row($result) )
	        {
	            $info[] = array( 'table'=>$table,  'count'=>$line[0] );
	        }
	        else
	        {
	            $info[] = array( 'table'=>$table, 'count'=>-1 );
	        }
	    }
	    
	    return $info;
	}

	function GetDeviceFailures( $de_no )
	{
		$info = array();
		$hours = 24;
		if ( $de_no == -3 )
		{ // login failures - 48 hours worth
			$query = sprintf( "select ev_EventNo,ev_Timestamp,ev_DeviceNo,ev_IOChannel,ev_EventType,ev_Value,ev_Description
						from events where ev_DeviceNo=%d and ev_Value=%d and
						ev_Timestamp>=date_sub(now(), interval %d hour)
						order by ev_Timestamp desc", $de_no, 0, $hours*2 );
		}
		else
		{
//		$query = sprintf( "select ev_EventNo,ev_Timestamp,ev_DeviceNo,ev_IOChannel,ev_EventType,ev_Value,ev_Description 
//						from events where ev_DeviceNo=%d and ev_EventType=%d and
//						ev_Timestamp>=date_sub(now(), interval %d hour)
//						order by ev_Timestamp desc", $de_no, E_ET_DEVICE_NG, $hours );
		}
		$result = $this->RunQuery( $query );
		while ( $line = mysqli_fetch_row($result) )
		{
				$info[] = array( 'ev_EventNo'=>$line[0], 'ev_Timestamp'=>$line[1], 'ev_DeviceNo'=>$line[2],
										'ev_IOChannel'=>$line[3], 'ev_EventType'=>$line[4], 'ev_Value'=>$line[5], 
										'ev_Description'=>stripslashes($line[6]) );
		}

		$this->FreeQuery($result);
		
		return $info;
	}

}

// convert yyyy-mm-dd hh:mm:ss to hh:mm dd/mm
function func_convert_timestamp( $tt )
{
	$expl = explode( " ", $tt );
	if ( isset( $expl[0]) && isset($expl[1]) )
	{
		$expld = explode( "-", $expl[0] );
		$explt = explode( ":", $expl[1] );
		
		$out = sprintf( "%02d:%02d %02d/%02d", $explt[0], $explt[1], $expld[2], $expld[1] );
	}
	else 
	{
		$out = $tt;
	}
	
	return $out;
}

function func_user_feature_enabled( $feature )
{
    if ( substr( $_SESSION['us_Features'], $feature, 1 ) == 'Y' )
        return true;
    else
        return false;
}

function func_get_user_feature_desc( $feature )
{
    $desc = "?";
    switch ( $feature )
    {
    default:
        break;
    case E_UF_CAMERAS:
        $desc = E_UFD_CAMERAS;
        break;
    case E_UF_UPGRADE:
        $desc = E_UFD_UPGRADE;
        break;
    case E_UF_HOMECAMERAS:
        $desc = E_UFD_HOMECAMERAS;
        break;
    }
    
    return $desc;
}

function func_get_security_level_desc( $sec )
{
    $desc = "?";
    switch ( $sec )
    {
    default:
        break;
    case SECURITY_LEVEL_NONE:
        $desc = "None";
        break;
    case SECURITY_LEVEL_GUEST:
        $desc = "Guest";
        break;
    case SECURITY_LEVEL_USER:
        $desc = "User";
        break;
    case SECURITY_LEVEL_ADMIN:
        $desc = "Admin";
        break;
    }
    
    return $desc;
}

// hh:mm xm to nnnn
// nnnn to hh:mm xm
function func_convert_time( $tt )
{
	$out = $tt;
	$expl = explode( ":", $tt );
	if ( substr($tt,2,1) == ':' || substr($tt,1,1) == ':' )
	{	// convert to nnnn
		$out = $expl[0] * 60 + intval($expl[1]);
		if ( intval($expl[0]) < 13 && (strtolower(substr($tt,5,1)) == 'p' || strtolower(substr($tt,6,1)) == 'p') )
			$out += 12*60;
	}
	else if ( $tt != "" )
	{	// convert to hh:mm xm
		$hh = intval($tt/60);
		$mm = intval($tt) - 60*$hh;
		$ampm = "am";
		if ( $hh > 12 )
		{
			$hh -= 12;
			$ampm = "pm";
		}
		$out = sprintf( "%02d:%02d %s", $hh, $mm, $ampm );
	}

	return $out;
}

function func_get_build_number()
{
	$ver = "?";
	
	$fh = fopen( "version.txt", "rt" );
	if ( $fh != false )
	{
		$ver = fgets( $fh );
		
		fclose( $fh );
	}
	
	return $ver;
}

function func_disabled_non_admin()
{
    if ( $_SESSION['us_AuthLevel'] == SECURITY_LEVEL_ADMIN )
    {
        return "";
    }
    else
    {
        return "disabled";
    }
}

function func_disabled_non_user()
{
    if ( $_SESSION['us_AuthLevel'] >= SECURITY_LEVEL_USER )
    {
        return "";
    }
    else
    {
        return "disabled";
    }
}



?>