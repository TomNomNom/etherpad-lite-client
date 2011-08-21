<?php
class EtherpadLiteClient {

  const API_VERSION             = 1;

  const CODE_OK                 = 0;
  const CODE_INVALID_PARAMETERS = 1;
  const CODE_INTERNAL_ERROR     = 2;
  const CODE_INVALID_FUNCTION   = 3;
  const CODE_INVALID_API_KEY    = 4;

  protected $apiKey = "";
  protected $baseUrl = "http://localhost:9001/api";
  
  public function __construct($apiKey, $baseUrl = null){
    $this->apiKey  = $apiKey;
    if (isset($baseUrl)){
      $this->baseUrl = $baseUrl;
    }
    if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)){
      throw new InvalidArgumentException("[{$this->baseUrl}] is not a valid URL");
    }
  }

  protected function call($function, array $arguments = array()){
    $query = array_merge(
      array('apikey' => $this->apiKey),
      $arguments
    );
    $url = $this->baseUrl."/".self::API_VERSION."/".$function."?".http_build_query($query);
    // not all PHP installs have access to curl
    if (function_exists('curl_init')){
      $c = curl_init($url);
      curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($c, CURLOPT_TIMEOUT, 20);
      $result = curl_exec($c);
      curl_close($c);
    } else {
      $result = file_get_contents($url);
    }
    
    if($result == ""){
      throw new UnexpectedValueException("Empty or No Response from the server");
    }
    
    $result = json_decode($result);
    if ($result === null){
      throw new UnexpectedValueException("JSON response could not be decoded");
    }
    return $this->handleResult($result);
  }

  protected function handleResult($result){
    if (!isset($result->code)){
      throw new RuntimeException("API response has no code");
    }
    if (!isset($result->message)){
      throw new RuntimeException("API response has no message");
    }
    if (!isset($result->data)){
      $result->data = null;
    }

    switch ($result->code){
      case self::CODE_OK:
        return $result->data;
      case self::CODE_INVALID_PARAMETERS:
      case self::CODE_INVALID_API_KEY:
        throw new InvalidArgumentException($result->message);
      case self::CODE_INTERNAL_ERROR:
        throw new RuntimeException($result->message);
      case self::CODE_INVALID_FUNCTION:
        throw new BadFunctionCallException($result->message);
      default:
        throw new RuntimeException("An unexpected error occurred whilst handling the response");
    }
  }

  // GROUPS
  // Pads can belong to a group. There will always be public pads that doesnt belong to a group (or we give this group the id 0)
  
  // creates a new group 
  public function createGroup(){
    return $this->call("createGroup");
  }

  // this functions helps you to map your application group ids to etherpad lite group ids 
  public function createGroupIfNotExistsFor($groupMapper){
    return $this->call("createGroupIfNotExistsFor", array(
      "groupMapper" => $groupMapper
    ));
  }

  // deletes a group 
  public function deleteGroup($groupID){
    return $this->call("deleteGroup", array(
      "groupID" => $groupID
    ));
  }

  // returns all pads of this group
  public function listPads($groupID){
    return $this->call("listPads", array(
      "groupID" => $groupID
    ));
  }

  // creates a new pad in this group 
  public function createGroupPad($groupID, $padName, $text){
    return $this->call("createGroupPad", array(
      "groupID" => $groupID,
      "padName" => $padName,
      "text"    => $text
    ));
  }

  // AUTHORS
  // Theses authors are bind to the attributes the users choose (color and name). 

  // creates a new author 
  public function createAuthor($name){
    return $this->call("createAuthor", array(
      "name" => $name
    ));
  }

  // this functions helps you to map your application author ids to etherpad lite author ids 
  public function createAuthorIfNotExistsFor($authorMapper, $name){
    return $this->call("createAuthorIfNotExistsFor", array(
      "authorMapper" => $authorMapper,
      "name"         => $name
    ));
  }

  // SESSIONS
  // Sessions can be created between a group and a author. This allows
  // an author to access more than one group. The sessionID will be set as
  // a cookie to the client and is valid until a certian date.

  // creates a new session 
  public function createSession($groupID, $authorID, $validUntil){
    return $this->call("createSession", array(
      "groupID"    => $groupID,
      "authorID"   => $authorID,
      "validUntil" => $validUntil
    ));
  }

  // deletes a session 
  public function deleteSession($sessionID){
    return $this->call("deleteSession", array(
      "sessionID" => $sessionID
    ));
  }

  // returns informations about a session 
  public function getSessionInfo($sessionID){
    return $this->call("getSessionInfo", array(
      "sessionID" => $sessionID
    ));
  }

  // returns all sessions of a group 
  public function listSessionsOfGroup($groupID){
    return $this->call("listSessionsOfGroup", array(
      "groupID" => $groupID
    ));
  }

  // returns all sessions of an author 
  public function listSessionsOfAuthor($authorID){
    return $this->call("listSessionsOfAuthor", array(
      "authorID" => $authorID
    ));
  }

  // PAD CONTENT
  // Pad content can be updated and retrieved through the API

  // returns the text of a pad 
  // should take optional $rev
  public function getText($padID){
    return $this->call("getText", array(
      "padID" => $padID
    ));
  }

  // sets the text of a pad 
  public function setText($padID, $text){
    return $this->call("setText", array(
      "padID" => $padID, 
      "text"  => $text
    ));
  }

  // PAD
  // Group pads are normal pads, but with the name schema
  // GROUPID$PADNAME. A security manager controls access of them and its
  // forbidden for normal pads to include a $ in the name.

  // creates a new pad
  public function createPad($padID, $text){
    return $this->call("createPad", array(
      "padID" => $padID, 
      "text"  => $text
    ));
  }

  // returns the number of revisions of this pad 
  public function getRevisionsCount($padID){
    return $this->call("getRevisionsCount", array(
      "padID" => $padID
    ));
  }

  // deletes a pad 
  public function deletePad($padID){
    return $this->call("deletePad", array(
      "padID" => $padID
    ));
  }

  // returns the read only link of a pad 
  public function getReadOnlyID($padID){
    return $this->call("getReadOnlyID", array(
      "padID" => $padID
    ));
  }

  // sets a boolean for the public status of a pad 
  public function setPublicStatus($padID, $publicStatus){
    return $this->call("setPublicStatus", array(
      "padID"        => $padID,
      "publicStatus" => $publicStatus
    ));
  }

  // return true of false 
  public function getPublicStatus($padID){
    return $this->call("getPublicStatus", array(
      "padID" => $padID
    ));
  }

  // returns ok or a error message 
  public function setPassword($padID, $password){
    return $this->call("setPassword", array(
      "padID"    => $padID,
      "password" => $password
    ));
  }

  // returns true or false 
  public function isPasswordProtected($padID){
    return $this->call("isPasswordProtected", array(
      "padID" => $padID
    ));
  }
}

