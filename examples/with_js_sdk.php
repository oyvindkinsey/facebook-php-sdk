<?php

require '../src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '344617158898614',
  'secret' => '6dc8ac871858b34798bc2488200e503d',
  'session'    => true,
  'validate' => 20000
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
if ($identity) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $identity = null;
  }
}
?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <body>
    <?php if ($identity) { ?>
      <a href="?logout=true">Logout</a>
      Your user profile is
      <pre>
        <?php print htmlspecialchars(print_r($user_profile, true)) ?>
      </pre>
    <?php } else { ?>
      <fb:login-button></fb:login-button>
    <?php } ?>
    <div id="fb-root"></div>
    <script>

      window.fbAsyncInit = function() {
        FB.init({
          appId: '<?php echo $facebook->getAppID() ?>',
          xfbml: true,
        });

        FB.Event.subscribe('auth.login', function(response) {
           // By doing this we mimic the behavior expected of a canvas app
           var form = document.createElement('form'); form.method = 'POST';
           var input = form.appendChild(document.createElement('input'));
           input.type = 'hidden'; input.name = 'signed_request';
           input.value =  response.authResponse.signedRequest;

           document.body.appendChild(form);
           form.submit();
        });

        FB.Event.subscribe('auth.statusChange', function(response) {
          if (response.status != 'connected') {
            // the user either logged out of Facebook or deTOSed this app
            // this doesn't necessarily mean that he should be logged out of
            // this app though.. This app != Facebook :)
            location.href = '?logout=true';
          }
        });

        FB.Event.subscribe('auth.authResponseChange', function(response) {
          console.log('auth.authResponseChange', response);
        });

      };

      (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());

    </script>
  </body>
</html>
