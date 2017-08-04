<?php
  require("db.php");
  
  // Get parameters from URL
  $center_lat = $_GET["lat"];
  $center_lng = $_GET["lng"];
  $radius     = $_GET["radius"];
  $mapType    = $_GET["type"];


  // Start XML file, create parent node
  $dom = new DOMDocument("1.0");
  $node = $dom->createElement("markers");
  $parnode = $dom->appendChild($node);
  
  // Opens a connection to a mySQL server
  $connection = mysqli_connect("localhost", $username, $password, $database);
  if (!$connection) {
    die("Not connected : " . mysqli_connect_errno());
  }
  
  mysqli_query($connection, "SET NAMES 'utf8'");

// Search the rows in the markers table
  $query = sprintf("SELECT id, name, address, lat, lng, phone, note,( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM markers WHERE type = '%s' HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
    mysqli_real_escape_string($connection, $center_lat),
    mysqli_real_escape_string($connection, $center_lng),
    mysqli_real_escape_string($connection, $center_lat),
    mysqli_real_escape_string($connection, $mapType),
    mysqli_real_escape_string($connection, $radius));
 
  $result = mysqli_query($connection, $query);
  #var_dump($result); die();
  if (!$result) {
    die("Invalid query: " . mysqli_error($connection));
  }

  header("Content-type: text/xml");
  
  // Iterate through the rows, adding XML nodes for each
  while ($row = $result->fetch_assoc()){
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("id", $row['id']);
    $newnode->setAttribute("name", $row['name']);
    $newnode->setAttribute("address", $row['address']);
    $newnode->setAttribute("lat", $row['lat']);
    $newnode->setAttribute("lng", $row['lng']);
    $newnode->setAttribute("distance", $row['distance']);
    $newnode->setAttribute("phone", $row['phone']);
    $newnode->setAttribute("note", $row['note']);
  }
  echo $dom->saveXML();

?>