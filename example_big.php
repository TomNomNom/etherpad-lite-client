<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<style type="text/css">
h1{margin:0;display:inline;}
h2{display:inline;font-size:81.3%}
h3{display:inline;margin-right:20px;}
.pad{background-color:#F7F7F7;padding:20px;border-style:solid;border-width:1px;margin-bottom:10px;}
.contents{display:none;word-wrap: break-word;}
#pads{width:700px;}
</style>
</head>
<body>
<pre>
<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Include the Class
include 'etherpad-lite-client.php';
$host = "http://beta.etherpad.org";

// By this point the user has authenticated

// Create an instance
$instance = new EtherpadLiteClient('EtherpadFTW','http://beta.etherpad.org/api'); // Example URL:  http://your.hostname.tld:8080/api --  All API calls return a JSON value as documented in the API here: https://github.com/Pita/etherpad-lite/wiki/HTTP-API

// Get the Params from the URL
$action = $_GET['action'];

// Step 1, get GroupID of the userID where userID is OUR userID and NOT the userID used by Etherpad
try {
  $mappedGroup = $instance->createGroupIfNotExistsFor("John@McLear.co.uk");
  $groupID = $mappedGroup->groupID;
} catch (Exception $e) {}

// Create a session
/* get Mapped Author ID based on a value from your web application such as the userID */
try {
  $author = $instance->createAuthorIfNotExistsFor('John McLear', 'Cake'); 
  $authorID = $author->authorID;
} catch (Exception $e) {
  echo "\n\ncreateAuthorIfNotExistsFor Failed with message ". $e->getMessage();
}
$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
$sessionID = $instance->createSession($groupID, $authorID, $validUntil);
$sessionID = $sessionID->sessionID;
setcookie("sessionID",$sessionID); // Set a cookie 

// echo "New Session ID is $sessionID->sessionID\n\n";

// Run some logic based on the initial request
if ($action){ // if an action is set then lets do it.

  if ($action == "newPad") // If the request is to create a new pad
  {
    $name = $_GET["name"];
    if (!$name){
      function genRandomString() { // A funtion to generate a random name if something doesn't already exist
        $length = 10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';
        for ($p = 0; $p < $length; $p++) {
          $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
      }
      $name = genRandomString();
    }
    $contents = $_GET["contents"];
    try {
      $newPad = $instance->createGroupPad($groupID,$name,$contents);
      $padID = $newPad->padID;
      $newlocation = "$host/p/$padID"; // redirect to the new padID location
      header( "Location: $newlocation" ) ;
    } catch (Exception $e) {
      echo "\n\ncreateGroupPad Failed with message ". $e->getMessage();
    }
  }
  if ($action == "deletePad") // If teh request is to delete an existing pad
  {
    $name = $_GET["name"];
    try {
      // urldecode seems to be a historical problem
      $name = urldecode($name);
      $instance->deletePad($groupID . "\$" . $name);
    } catch (Exception $e) {
      // the pad doesn't exist?
      echo "\n\ndeletePad Failed with message ". $e->getMessage();
    }
  }
  if ($action == "makePublic") // If teh request is to delete an existing pad
  {
    $name = $_GET["name"];
    try {
      $instance->setPublicStatus($name,"true");
    } catch (Exception $e) {
    // the pad doesn't exist?
    echo "\n\nMake Public Failed with message ". $e->getMessage();
    }
  }
  if ($action == "makePrivate") // If teh request is to delete an existing pad
  {
    $name = $_GET["name"];
    try {
      $instance->setPublicStatus($name,"false");
    } catch (Exception $e) {
    // the pad doesn't exist?
    echo "\n\nMake Private Failed with message ". $e->getMessage();
    }
  }

}

// Step 2, list Pads from this Group.
/* Example: List Pads from a group */
try {
  $padList = $instance->listPads($groupID);
  $padList = $padList->padIDs;
} catch (Exception $e) {
  echo "\n\nlistPads Failed: ". $e->getMessage();
}


// Begin writing to the UI
// echo "<h1>Pads</h1><div id='pads'>";
echo "Create new Pad <form action='#' style='display:inline;'><input type='hidden' name='action' value='newPad'><input type='text' name='name'><input type='submit'></form>";

$count = 0;

foreach($padList as $pad => $key){  // For each pad in the object
  // This should really be ordered based on last modified
  $padname = explode("$",$pad);
  $group = $padname[0];
  $padname = $padname[1];
  $padContents = $instance->getText($pad); // Get the pad contents
  $contents = $padContents->text;
  // this should escape whitespaces at $padname. Note: whitespaces dosn't appear in new versions of etherpad-lite
  $padnameEncoded = urlencode($padname);
  $padUrl = $group . "\$" . $padnameEncoded;
  echo "<div class='pad'>";
  echo "<h1><a href=$host/p/$padUrl>$padname</a></h1>";
  echo " - <h2><a onClick='$(\"#contents$count\").slideDown();'>Preview</a></h2><br/>";
  echo "<div class='contents' id=contents$count>$contents</div>";
  echo "<h3><a href=example_big.php?action=deletePad&name=$padnameEncoded>Delete Pad</a></h3>";
  echo "<h3><a href=$host/p/$padUrl>Edit Pad</a></h3>";
  $readOnlyID = $instance->getReadOnlyID($pad);
  $readOnlyID = $readOnlyID->readOnlyID;
  echo "<h3><a href=$host/ro/$readOnlyID>Read only view</a>";
  $getpublicStatus = $instance->getPublicStatus($pad); // get Security status of the pad
  if ($getpublicStatus->publicStatus === false){
    echo "<h3><a href=example_big.php?action=makePublic&name=$padUrl>Make pad public</a></h3>";
  }
  else{
    echo "<h3><a href=example_big.php?action=makePrivate&name=$padUrl>Make pad private</a></h3>";
  }
  echo "</div>";
  $count++;
}
echo "</div>";
?>
</body>
