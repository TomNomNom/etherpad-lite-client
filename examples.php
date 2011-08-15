<pre>
<?php
// Include the Class
include 'etherpad-lite-client.php';

// Create an instance
$instance = new EtherpadLiteClient('EtherpadFTW','http://beta.etherpad.org/api');

// All API calls return a JSON value, you should print_r the JSON to see more info.

echo "<h1>Pads</h1>";

/* Example: Create Author */ 
$author = $instance->createAuthor('John McLear'); // This really needs explaining..
$authorID = $author->authorID;
echo "The AuthorID is now $authorID\n\n";

/* Example: get Mapped Author */
// This isn't written yet

/* Example: Set Text into a Pad */
// BUG FOR PITA:: $instance->setText('testPad','Hello world');

/* Example: Create a new Pad */
// $instance->createPad('testPad','Hello world');
// Above will error out if the pad already exists.. Waiting on a fix from Tomnomnom

/* Example: Delete Pad */
// $instance->deletePad('testPad');

/* Example: Get Ready Only ID of a pad */
$readOnlyID = $instance->getReadOnlyID('testPad');
echo "The read only ID of this pad is: $readOnlyID->readOnlyID\n\n";

/* Example: Get Public Status of a pad and include some logic -- This only works for group pads */
// BUG FOR PITA 
$getpublicStatus = $instance->getPublicStatus('testPad');
if ($getpublicStatus->publicStatus === false){echo "This Pad is not public but this is really buggy so ignore it\n\n";}else{echo "This Pad is public but this is really buggy so ignore it\n\n";}

/* Example: Set Public Status of a pad -- This only works for group pads */
$instance->setPublicStatus('testPad',true); // true or false

/* Example: Set Password on a pad -- This only works for group pads */
$instance->setPassword('testPad','aPassword');

/* Example: Get true/false if the pad is password protected and include some logic -- This only works for group pads*/
//BUG FOR PITA
$isPasswordProtected = $instance->isPasswordProtected('testPad');
if ($isPasswordProtected->isPasswordprotected === false){echo "Pad is not password protected but this is really buggy so ignore it\n\n";}else{echo "Pad is password protected but this is really buggy so ignore it\n\n";}

/* Example: Get revisions Count of a pad */
$revisionCount = $instance->getRevisionsCount('testPad');
$revisionCount = $revisionCount->revisions;
echo "Pad has $revisionCount revisions\n\n";

/* Example: Get Pad Contents and echo to screen */
$padContents = $instance->getText('testPad');
echo "Pad text is: <br/><ul>$padContents->text\n\n</ul>";
echo "End of Pad Text\n\n<hr>";
echo "<h1>Groups</h1>";

/* Example: Create Group */
$createGroup = $instance->createGroup();
$groupID = $createGroup->groupID;
echo "New GroupID is $groupID\n\n";

/* Example: Create Group Pad */
$newPad = $instance->createGroupPad($groupID,'testpad','Example text body'); 
$padID = $newPad->padID;
echo "Created new pad with padID: $padID\n\n";

/* Example: List Pads from a group */
$padList = $instance->listPads($groupID); // Errors out if the group does not exist
echo "Available pads for this group:\n";
var_dump($padList->padIDs);
echo "\n";

/* Example: Create Mapped Group -- This maps a humanly readable name to a groupID */
// $mapGroup = $instance->getMappedGroup($groupID);
// BUG This bit is confusing as hell and the PHP function doesn't exist in the class - Waitnig on original author to write it

/* Example: Delete a Group */
//BUG - Waiting on PITA: $instance->deleteGroup($groupID);

echo "<hr>";
echo "<h1>Sessions</h1>";

/* Example: Create Session */
$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
$sessionID = $instance->createSession($groupID, $authorID, $validUntil);
echo "New Session ID is $sessionID->sessionID\n\n";

/* Example: Get Session info */
echo "Session info:\n";
$sessionID = $sessionID->sessionID;
$sessioninfo = $instance->getSessionInfo($sessionID);
var_dump($sessioninfo);
echo "\n";

/* Example: List Sessions os Author */
echo "Sessions the Author $authorID is part of:\n";
$authorSessions = $instance->listSessionsOfAuthor($authorID);
var_dump($authorSessions);
echo "\n";

/* Example: List Sessions of Group */
$groupSessions = $instance->listSessionsOfGroup($groupID);
var_dump($groupSessions);

/* Example: Delete Session */
$instance->deleteSession($sessionID);


/* Example: */
?>
