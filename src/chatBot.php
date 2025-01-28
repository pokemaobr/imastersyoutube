<?php

//ini_set('display_errors', 0);
//error_reporting(~E_ALL);

use Google\Service\YouTube\VideoListResponse;

require_once '../config.php';
require_once '../vendor/autoload.php';
require_once 'messages.php';

session_start([
    'cookie_lifetime' => 86400,
]);

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = $OAUTH_CLIENT;
$OAUTH2_CLIENT_SECRET = $OAUTH_KEY;
$liveChatId = 'Cg0KC09jeXNuMm5HRlhNKicKGFVDTlRYX1M3enE5aF9NS2hQbGpNSW9ZdxILT2N5c24ybkdGWE0';
$channel = '';
$htmlBody = '';

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

        $i = (int)file_get_contents('i.txt');

        $messages = $youtube->liveChatMessages->listLiveChatMessages($liveChatId, 'snippet,authorDetails');

        $timeOfLastMessage = new \DateTime(file_get_contents('last-message-time-lives.txt'));

        foreach ($messages as $message) {

            $timeOfMessage = new \DateTime($message['snippet']['publishedAt']);

            if ($timeOfMessage > $timeOfLastMessage) {

                $htmlBody .= ($message['authorDetails']['displayName'] . ' - ' . $message['snippet']['displayMessage'] . ' - ' . $message['snippet']['publishedAt'] . '</br>');
                $return = processMessage($message, $channel);
                //print ($return);
                if (!empty($return)) sendMessage($youtube, $liveChatId, $return);
                file_put_contents('last-message-time-lives.txt', $message['snippet']['publishedAt']);


            }

            file_put_contents('last-message-time-lives.txt', $message['snippet']['publishedAt']);

        }

        switch($i) {

            case 5: $mensagem = 'Se inscreva no canal iMasters';
            break;
            case 10: $mensagem = 'Se inscreva no canal iMasters';
            break;
            case 15: $mensagem = 'Se inscreva no canal iMasters';
            break;
            default:
            $mensagem = '';

        }

        if (!empty($mensagem)) sendMessage($youtube, $liveChatId, $mensagem);

        $i = $i + 1;

        if ($i < 16) file_put_contents('i.txt', $i);
        else file_put_contents('i.txt', 0);


    } catch (Google_Service_Exception $e) {
        $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
        session_destroy();
        header("Refresh:0");
    } catch (Google_Exception $e) {
        $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    }

}

?>

<!doctype html>
<html>
<head>
    <title>My Live Streams</title>
</head>
<body>
<button onclick="reload(timeout)" id="button">Parar</button>
<br/>
<?= @$htmlBody ?>
</body>
<script>

    var timeout = setTimeout(function () {
        location.reload()
    }, 10000);

    function reload() {

        if (timeout) {
            clearTimeout(timeout);
            timeout = null;
            document.getElementById('button').textContent = 'Voltar';
        } else {
            location.reload();
        }

    }

</script>
</html>
