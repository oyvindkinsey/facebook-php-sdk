<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require '../src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '344617158898614',
  'secret' => '6dc8ac871858b34798bc2488200e503d',
  'session'  => true
));

if (isset($_REQUEST['logout'])) {
  // 
  $facebook->destroySession();
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: ".$facebook->getCurrentUrl(false));
  exit();
}

// Identity will contain data about the user we believe is signed in.
// When 'session' is enabled, this will be stored across requests, and so will
// not be validated. If you wish to validate that the claims still hold, for
// instance before allowing access to a settings page, please call
//   $facebook=>getIdentity($signed_request = null, $validate = true);
// If you set the 'validate' option (seconds) then the SDK will validate
// automatically when the specified time has passed since the last validation.
//
// At all time the identity object will contain information about when the
// claim was issued, and when it was last validated.
$identity = $facebook->getIdentity();

if (isset($_REQUEST['code'])) {
  // this is a fresh assertion, lets redirect to a more pretty url
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: ".$facebook->getCurrentUrl(false));
  exit();
}

if ($identity) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $identity = null;
  }
}

if ($identity) {
  // Login or logout url will be needed depending on current user state.
  $logoutUrl = '?logout=true';
} else {
  $loginUrl = $facebook->getLoginUrl();
}

// This call will always work since we are fetching public data.
$naitik = $facebook->api('/naitik');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($identity): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($identity): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user_profile['id']; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($user_profile); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

    <h3>Public profile of Naitik</h3>
    <img src="https://graph.facebook.com/naitik/picture">
    <?php echo $naitik['name']; ?>
  </body>
</html>
