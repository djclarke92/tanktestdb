<?php 
//--------------------------------------------------------------------------------------
//
//	TankTest Website
//	Copyright (c) 2026 Dave Clarke
//
//--------------------------------------------------------------------------------------


session_start();
include_once( "files/common.php" );
include_once( "files/commonhtml.php" );
include_once( "files/class.email.php" );
include_once( "fpdf/fpdf.php" );
include_once( "files/createpdf.php" );

func_session_init();

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'vendor/autoload.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';


$_SESSION['bootstrap'] = true;
$_SESSION['regulations'] = sprintf( "This Document of Certification must be retained by the cylinder owner and should be produced if required by an 
inspector under the Health and Safety at Work Act 2015, or the cylinder filler. A Document of Certification cannot be 
issued if a cylinder fails the Periodic Test.  The action to be taken in the event of a failure is set out in regulations 
15.56, 15.57 and 15.58 of the Health an Safety at Work (Hazardous Substances) Regulations 2017.");

if ( isset($_SERVER['HTTP_AUTHORIZATION']) )
{
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
}


function ValidateUser( $db, $username, $password, &$my_login_msg, $autologin_username )
{
    $ok = false;
    
    $_SESSION['us_Username'] = "";
    $_SESSION['us_AuthLevel'] = "";
    $_SESSION['us_Features'] = "";
    $_SESSION['us_Name'] = "";
    $_SESSION['us_SignatoryNumber'] = "";
    
    $my_login_msg = "";
    
    $list = $db->ReadUsers();
    foreach ( $list as $user )
    {
        if ( strtolower($username) == strtolower($user['us_Username']) )
        {
            $hash = hash( "sha256", $password, FALSE );
            if ( $autologin_username != "" )
            {   // fake the login
                $user['us_Password'] = $hash;
            }
            if ( strcmp( $user['us_Password'], $hash ) == 0)
            {
                $ok = true;
                $_SESSION['us_Username'] = $user['us_Username'];
                $_SESSION['us_AuthLevel'] = $user['us_AuthLevel'];
                $_SESSION['us_Features'] = $user['us_Features'];
                $_SESSION['us_Name'] = $user['us_Name'];
                $_SESSION['us_SignatoryNumber'] = $user['us_SignatoryNumber'];
            }
            else
            {  // wrong password
                $my_login_msg = sprintf( "The entered password is not correct." );
            }
            break;
        }
    }
    
    
    if ( $_SESSION['us_Username'] == "" && $my_login_msg == "" )
    {	// error
        $my_login_msg = sprintf( "The Username entered does not exist in the database." );
    }
    
    if ( !$ok )
    {   // delay to stop robot attacks
        sleep( 4 );
    }
    
    return $ok;
}


// database
$db = new MySQLDB();
if ( $db->Open( DB_HOST_NAME, DB_USER_NAME, DB_PASSWORD, DB_DATABASE_NAME ) === false )
{
    func_unauthorisedaccess();
    return;
}

func_check_database( $db );

$my_email = "";
$my_password = "";
$my_login_msg = "";
$new_login = false;
$from_refresh = false;
$display_mode = "";


if ( isset($_GET['PageMode']) )
{
    $_SESSION['page_mode'] = $_GET['PageMode'];
}
if ( isset($_GET['RefreshEnabled']) || isset($_GET['MonitorRefreshEnabled']) )
    $from_refresh = true;
    
    
if ( isset($_POST['my_logout']) || isset($_GET['Logout']) )
{
    func_user_logout();
}
else if ( isset($_POST['Username']) && isset($_POST['Password']) )
{
    $my_email = $_POST['Username'];
    $my_password = $_POST['Password'];
    if ( $my_email != "" && $my_password != "" )
    {
        if ( ValidateUser( $db, $my_email, $my_password, $my_login_msg, "" ) )
        {
            $new_login = true;
            $_SESSION['page_mode'] = "Home";
            
            $db->SaveUserLoginAttempt( $my_email, 1 );
        }
        else   
        {   // failed
            $db->SaveUserLoginAttempt( $my_email, 0 );
        }
    }
}

if ( $_SESSION['us_AuthLevel'] <= SECURITY_LEVEL_NONE )
{
    $_SESSION['page_mode'] = "Intro";
}

$fs_redirect_timeout = AUTO_REFRESH_LOGOUT;
$fs_redirect_url = sprintf( "?Logout=1&PageMode=Home", $_SERVER['PHP_SELF'] );
                    

?>

<!DOCTYPE html>
<html lang="en">
<head>
  	<title>TankTestDB Admin Console</title>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<script src="./files/jquery-3.6.0.min.js"></script>
  	<script src="./files/popper-2.11.5.min.js"></script>
  	<link rel="stylesheet" href="./files/bootstrap-5.1.3.min.css">
  	<script src="./files/bootstrap-5.1.3.bundle.min.js"></script>
  
	<meta http-equiv="Content-Language" content="en-nz">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="description" content="TankTest Database">
	<meta name="keywords" content="Scuba Diving">
	<link rel="icon" href="favicon-32x32.png" type="image/x-icon">
	<link rel="shortcut icon" href="favicon-32x32.png" type="image/x-icon">
  
  <style>
  <?php
  if ( $display_mode == "" )
  { 
    printf( "body { padding-top: 30px; }" );
  }
  ?>
  </style>
  
  <script language="javascript">
		var counter = 0;
        var monRefreshChecked = false;

        function firstTime()
		{
			<?php
			if ( $new_login || $from_refresh )
			{
			    printf( "if ( document.getElementById('RefreshEnabled') != null ) {" );
			    printf( "  document.getElementById('RefreshEnabled').checked = true;" );
			    printf( "}" );
            }
			?>
		}
		
		function homeTimer()
		{
		    var progressBar = document.getElementById('refresh-progress-bar');
		    var refreshCheck = document.getElementById('RefreshEnabled');
			if ( refreshCheck != null && progressBar != null ) {
			  if ( refreshCheck.checked ) { 
    	        counter = counter + 1;
                progressBar.innerHTML = '<span class="spinner-border spinner-border-sm"></span>&nbsp;&nbsp;' + (counter < 10 ? '&nbsp;' : '') + (30-counter);
			  } else {
                progressBar.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + (counter < 10 ? '&nbsp;' : '') + (30-counter);
              }
			}

    		<?php
            if ( $_SESSION['page_mode'] == "Home" )
            {
                ?>
                if ( counter >= 30 ) {
                    counter = 0;
                    if ( document.getElementById('RefreshEnabled').checked ) {
                    window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?RefreshEnabled"; 
                    }
                } else {
                    var all = document.getElementsByClassName('timestamp');
                    for (var i = 0; i < all.length; i++) {
                        // 01234567890
                        // hh:mm dd/mm
                        var dd = all[i].textContent;
                        var now = new Date();
                        var last = new Date();

                        last.setFullYear( now.getFullYear() );
                        last.setMonth( parseInt(dd.substr(9,2))-1 );
                        last.setDate( parseInt(dd.substr(6,2)) );
                        last.setHours( parseInt(dd.substr(0,2)) );
                        last.setMinutes( parseInt(dd.substr(3,2)) );
                        last.setSeconds( 0 );
                        
                        if ( last.getTime() + 16*60*1000 < now.getTime() ) {
                        if ( (now.getSeconds() % 2) == 0 ) {
                            all[i].style.color = 'white';
                            all[i].style.backgroundColor = 'red';
                        } else {
                            all[i].style.color = 'red';
                            all[i].style.backgroundColor = 'transparent';
                        }
                        } else if ( last.getTime() + 6*60*1000 < now.getTime() ) {
                        all[i].style.color = 'red';
                        all[i].style.backgroundColor = 'transparent';
                        } else {
                        all[i].style.color = 'black';
                        all[i].style.backgroundColor = 'transparent';
                        }
                    }
                }
                <?php 
    			
    
    			printf( "setTimeout( 'homeTimer()', 1000 );" );
    		}
    		?>
    	}
	</script>
	<script>
		$(document).ready(function()
		{
  			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl)
            });
		});
	</script>
	<script>
    function onclickExternalPass()
    {
        const myCheckbox = document.getElementById('ex_ExternalFail');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickExternalFail()
    {
        const myCheckbox = document.getElementById('ex_ExternalPass');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickInternalPass()
    {
        const myCheckbox = document.getElementById('ex_InternalFail');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickInternalFail()
    {
        const myCheckbox = document.getElementById('ex_InternalPass');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickHydrostaticPass()
    {
        const myCheckbox = document.getElementById('ex_HydrostaticFail');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickHydrostaticFail()
    {
        const myCheckbox = document.getElementById('ex_HydrostaticPass');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickRepeatVisual()
    {
        const myCheckbox = document.getElementById('ex_RepeatVisualFail');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickRepeatVisualFail()
    {
        const myCheckbox = document.getElementById('ex_RepeatVisual');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickMinorScratches()
    {
        const myCheckbox = document.getElementById('ex_SeriousScratches');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    function onclickSeriousScratches()
    {
        const myCheckbox = document.getElementById('ex_MinorScratches');
        if (myCheckbox) {
            myCheckbox.checked = false;
        }
    }
    </script>
</head>



<?php
$bg = "";   //"background='images/background.png'";
printf( "<body onLoad='firstTime();homeTimer();' style='background-color: #F5F5F5;' %s>", $bg );

printf( "<img src='images/background1.png' id='bg' alt='background1.png' style='position: fixed; top: 100; left: 0; min-width: 100%%; min_height: 100%%; z-index: -1;'>");

if ( $display_mode == "" )
{
?>
<nav class="navbar navbar-expand-lg bg-dark navbar-dark fixed-top">
 <div class="container-fluid">
  <a class="navbar-brand" href="?PageMode=Home">TankTestDB</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
      <?php
      if ( $_SESSION['us_Username'] != "" )
      {
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=Customers'>Customers</a>" );
          printf( "</li>" );
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=Cylinders'>Cylinders</a>" );
          printf( "</li>" );
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=Examinations'>Examinations</a>" );
          printf( "</li>" );    
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=CylinderTypes'>Cylinder Types</a>" );
          printf( "</li>" );    
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=CylinderChecks'>Cylinder Checks</a>" );
          printf( "</li>" );    
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=Users'>Users</a>" );
          printf( "</li>" );    
          printf( "<li class='nav-item'>" );
          printf( "  <a class='nav-link' href='?PageMode=Events'>Events</a>" );
          printf( "</li>" );    
      }

      if ( $_SESSION['us_Username'] != "" )
      {
        printf( "<li class='nav-item'>" );
        $tip = sprintf( "You are logged in as %s", $_SESSION['us_Username'] );
        printf( "<a class='nav-link' href='?Logout=1' data-bs-toggle='tooltip' data-bs-html='true' title='%s'>Logout</a>", $tip );
        printf( "</li>" );    
      }
      ?>
    </ul>
  </div>  
 </div>
</nav>
<?php
printf( "<form action='%s' enctype='multipart/form-data' method='post' class='form-inline'>", $_SERVER['PHP_SELF'] );

}


//printf( "%s,%s,%s", $_SESSION['page_mode'], $_SESSION['us_Username'], $_SESSION['us_AuthLevel'] );
{
    printf( "<div class='container-fluid'>" );
    
    switch ( $_SESSION['page_mode'] )
    {
    default:
    case "Intro":
        include("./files/intro.php");
        break;
        
    case "Home":
        include("./files/home.php");

        $pattern = 'report-*.pdf';

           // Delete all files with the specified extension in the folder
        array_map('unlink', glob($pattern)); 
        break;

    case "Customers":
        include("./files/customers.php");
        break;
    
    case "Cylinders":
        include("./files/cylinders.php");
        break;
    
    case "Examinations":
        include("./files/examinations.php");
        break;

    case "Users":
        include("./files/users.php");
        break;
        
    case "CylinderTypes":
        include("./files/cylindertypes.php");
        break;

    case "CylinderChecks":
        include("./files/cylinderchecks.php");
        break;
        
    case "Events":
        include("./files/events.php");
        break;
    }
    
    printf( "</div>" );
}
?>


</form>

<?php 
if ( $display_mode == "" )
{
?>
<div class="container bg-info small" style="margin-bottom:0;">
  	<div class="row">
  		<div class="col-sm-6">
		<?php 
		printf( "TankTestDB Build %s", trim(func_get_build_number()) );
		printf( "<br>" );
		printf( "Design by DMDave @ %s", date("Y-m-d H:i:s") );
		?>
		</div>
		<div class="col-sm-6">
		<?php 		
		printf( "%s - %s", $_SERVER['REMOTE_ADDR'], (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown") );
		?>
		</div>
	</div>
</div>

<?php 
}

$db->Close();
?>

</body>
</html>

