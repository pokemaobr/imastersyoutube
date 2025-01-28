<?php

require_once '../config.php';
require_once '../vendor/autoload.php';

session_start([
    'cookie_lifetime' => 86400,
]);

$OAUTH2_CLIENT_ID = $OAUTH_CLIENT;
$OAUTH2_CLIENT_SECRET = $OAUTH_KEY;
$id = 'Ocysn2nGFXM';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

$youtube = new Google_Service_YouTube($client);

$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {

    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    file_put_contents('token.txt', $client->getAccessToken());
    header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

if ($client->getAccessToken()) {
    try {
        $part = ['part' => 'snippet,contentDetails,statistics,liveStreamingDetails'];
        $id = ['id' => $id];

        $video = $youtube->videos->listVideos($part, $id);

        $liveChatId = $video[0]['liveStreamingDetails']['activeLiveChatId'];

        $htmlBody = $liveChatId;

    } catch
    (Google_Service_Exception $e) {
        $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
        session_destroy();
        header("Refresh:0");
    } catch (Google_Exception $e) {
        $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    }
    $client->fetchAccessTokenWithRefreshToken($_SESSION[$tokenSessionKey]);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    file_put_contents('token.txt', $client->getAccessToken());

} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
    // If the user hasn't authorized the app, initiate the OAuth flow
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;

    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}


?>

<!doctype html>
<html>
<head>
    <title>My Live Streams</title>
</head>
<body>
<?= $htmlBody ?>
</body>
<script>
    //setTimeout(function(){location.reload()}, 500);
</script>
</html>

