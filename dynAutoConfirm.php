#!/usr/bin/env php
<?php
  set_time_limit(3600);

  // Connect to gmail
  $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
  $username = 'YOUR_USERNAME@gmail.com';
  $password = 'YOUR_PASSWORD';

  // try to connect
  $inbox = imap_open($hostname, $username, $password, NULL, 1) or die('Cannot connect to Gmail: ' . print_r(imap_errors()));

  /*
    ALL - return all messages matching the rest of the criteria
    ANSWERED - match messages with the \\ANSWERED flag set
    BCC "string" - match messages with "string" in the Bcc: field
    BEFORE "date" - match messages with Date: before "date"
    BODY "string" - match messages with "string" in the body of the message
    CC "string" - match messages with "string" in the Cc: field
    DELETED - match deleted messages
    FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
    FROM "string" - match messages with "string" in the From: field
    KEYWORD "string" - match messages with "string" as a keyword
    NEW - match new messages
    OLD - match old messages
    ON "date" - match messages with Date: matching "date"
    RECENT - match messages with the \\RECENT flag set
    SEEN - match messages that have been read (the \\SEEN flag is set)
    SINCE "date" - match messages with Date: after "date"
    SUBJECT "string" - match messages with "string" in the Subject:
    TEXT "string" - match messages with text "string"
    TO "string" - match messages with "string" in the To:
    UNANSWERED - match messages that have not been answered
    UNDELETED - match messages that are not deleted
    UNFLAGGED - match messages that are not flagged
    UNKEYWORD "string" - match messages that do not have the keyword "string"
    UNSEEN - match messages which have not been read yet
  */

  // search and get unseen emails, function will return email ids
  $emails = imap_search($inbox, 'SUBJECT "[Dyn]" UNSEEN');

  if (!$emails) die('No [Dyn] unseen emails');

  foreach ($emails as $mail) {
    $message = imap_fetchbody($inbox, $mail, 2);
    #echo "message: $message";
    preg_match('/"(https:\/\/account.dyn.com\/confirm\/.*?)"/', $message, $matches);
    if (!isset($matches[1])) {
      die("No confirmation link in email!");
    }
    $link = $matches[1];
    #echo("link:"); print_r($link);
    $response = file_get_contents($link);
    #echo "response: $response";
    $okConfirmed = "Your account is confirmed."; # TODO: check the real answer!
    $alreadyConfirmed = "Your confirmation has already been completed.";
    if (preg_match("/$okConfirmed/", $response)) {
      die("Confirmed successfully");
    }
    if (preg_match("/$alreadyConfirmed/", $response)) {
      die("Already confirmed successfully");
    }
    die("Unforeseen response!");
  }

  // close the connection
  imap_expunge($inbox);
  imap_close($inbox);
?>
