<?php

function processMessage($message, $channel)
{
    $channelId = $message['authorDetails']['channelId'];
    $author = $message['authorDetails']['displayName'];
    $messageText = $message['snippet']['displayMessage'];
    $isMod = $message['authorDetails']['isChatModerator'];
    $isOwner = $message['authorDetails']['isChatOwner'];
    $isSponsor = $message['authorDetails']['isChatSponsor'];

    if (str_starts_with(strtolower($messageText), 'bom dia')) {

        return 'Olá ' . $author . '. Boas vindas ao canal iMasters';

    }

    if (str_starts_with(strtolower($messageText), '!dado')) {

        return 'Olá ' . $author . '. Seu dado de 20 lados saiu o número:' . mt_rand(1,20);

    }


}

function sendMessage($youtube, $liveChatId, $message)
{

    $liveChatMessage = new Google_Service_YouTube_LiveChatMessage();
    $liveChatSnippet = new Google_Service_YouTube_LiveChatMessageSnippet();
    $liveChatSnippet->setLiveChatId($liveChatId);
    $liveChatSnippet->setType("textMessageEvent");
    $messageDetails = new Google_Service_YouTube_LiveChatTextMessageDetails();
    $messageDetails->setMessageText($message);
    $liveChatSnippet->setTextMessageDetails($messageDetails);
    $liveChatMessage->setSnippet($liveChatSnippet);
    $youtube->liveChatMessages->insert('snippet', $liveChatMessage);

}