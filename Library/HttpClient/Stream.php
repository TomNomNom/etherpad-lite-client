<?php

class HttpClient_Stream extends HttpClient {
  public function get($url){
    $params = array(
      'http' => array(
        'method' => 'GET',
        'ignore_errors' => true,
        'header' => 'Content-Type: application/x-www-form-urlencoded'
      )
    );
    $context = stream_context_create($params);
    $fp = fopen($url, 'rb', false, $context);
    $result = $fp ? stream_get_contents($fp) : null;
    return $result;
  }

  public function post($url, $postdata){
    $params = array(
      'http' => array(
        'method'        => 'POST',
        'ignore_errors' => true,
        'header'        => 'Content-Type: application/x-www-form-urlencoded',
        'content'       => $postdata
      )
    );
    $context = stream_context_create($params);
    $fp = fopen($url, 'rb', false, $context);
    $result = $fp ? stream_get_contents($fp) : null;
    return $result;
  }
}
