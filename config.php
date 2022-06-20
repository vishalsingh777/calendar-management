<?php 
 
// Database configuration    
define('DB_HOST', 'localhost'); 
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'bookingcalendar'); 
 
// Google API configuration 
define('GOOGLE_CLIENT_ID', '832074765055-gj0rnbngbgegakvjubv9gu40n75bhn4n.apps.googleusercontent.com'); 
define('GOOGLE_CLIENT_SECRET', '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'); 
define('GOOGLE_OAUTH_SCOPE', 'https://www.googleapis.com/auth/calendar'); 
define('REDIRECT_URI', 'http://127.0.0.1/final/google/google_calendar_event_sync.php'); 
define('CALENDAR_ID', 'primary'); 


define('OUTLOOK_CLIENT_ID', '40cc697a-8f9d-4236-918a-d923c80d3d90'); 
define('OUTLOOK_CLIENT_SECRET', '_w28Q~RhfeupqmkPB50qzKNOh8JCtbhxzYfiEc8g'); 
define('OUTLOOK_REDIRECT_URI', 'https://localhost/final/outlook/authorize.php'); 
define('OUTLOOK_SCOPE', 'https://graph.microsoft.com/.default'); 


$outlookOauthURL = 'https://login.microsoftonline.com/common/oauth2/authorize?client_id=' .OUTLOOK_CLIENT_ID.'&redirect_uri=' . urlencode(OUTLOOK_REDIRECT_URI) . '&response_mode=query&response_type=code&scope="' . urlencode(OUTLOOK_SCOPE) ."&state=12345";
 

 
// Start session 
if(!session_id()) session_start(); 
 
// Google OAuth URL 
$googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode(GOOGLE_OAUTH_SCOPE) . '&redirect_uri=' . REDIRECT_URI . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID . '&access_type=online'; 
 
?>

