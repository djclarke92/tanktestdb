<?php
if (isset($_GET['pdf'])) {
    $file = $_GET['pdf'];
    $filename = 'report.pdf'; // Desired filename in the browser

    // Ensure no output is sent before the header() function
    if (file_exists($file)) {
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"'); // 'inline' for viewing in browser
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');
        @readfile($file);

        //unlink($file);
        exit; // Terminate script execution after sending file
    } else {
        // Handle error if file doesn't exist
        $err = "Error: PDF file not found.";
    }
}
else
{
    $err = "no pdf specified";
}
// Handle errors (optional, but good practice)
header("HTTP/1.1 404 Not Found");
echo "Image not found: " . $err;
?>
