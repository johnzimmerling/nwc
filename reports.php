<?php
$fp = fopen('test.txt','a+');
$debug = false ;

//Store MySQL connection string variables in private file, and buffer the output
ob_start();
require './PRIVATE.php';
ob_end_clean();

session_start();

//Connect to MySQL Database
$conn=mysqli_connect($ip,$user,$pass,$db) or die('Error connecting to MySQL server: ' . mysqli_error(@conn));

//Collect input contents
$data = json_decode(file_get_contents("php://input"));


if ($data->task == 'getActiveMembers') {
    $myArray = array();
    if(true)
    {

      $sql  = "SELECT ";
      $sql .= "    mbr_f_name || ' ' || mbr_l_name AS Member, ";
      $sql .= "    mbr_co AS Company, ";
      $sql .= "    mbr_email AS Email, ";
      $sql .= "    mbr_desc AS Description, ";
      $sql .= "    TO_CHAR(mbr_join_date, 'Mon DD, YYYY') AS Joined ";
      $sql .= 'FROM ';
      $sql .= '    nwc.members ';
      $sql .= 'WHERE ';
      $sql .= '    active = true ';
  
      $result = mysqli_query($conn, $sql);
      if (!$result) {
        echo "Error: " . $sql . '<br>' ;
      } else {
        while ($row = mysqli_fetch_assoc($result)) {
          $myArray[] = $row;
        }
        echo json_encode($myArray);
      }
    } else {
      echo 'nosession';
    }    
}

else if ($data->task == 'getReferralsPassed') {
    $myArray = array();
    if(true)
    {

    $sql  = "SELECT ";
    $sql .= "a.refID AS ID, ";
    $sql .= "to_char(a.ref_date, 'Mon DD, YYYY') AS ReferralDate, ";
    $sql .= "b.mbr_f_name || ' ' || b.mbr_l_name AS From, ";
    $sql .= "c.mbr_f_name || ' ' || c.mbr_l_name AS To, ";
    $sql .= "a.ref_desc AS Description, ";
    $sql .= "d.tmpDesc AS Temperature ";
    $sql .= "FROM ";
    $sql .= "nwc.referrals a ";
    $sql .= "JOIN nwc.members b on a.ref_from = b.mbrID ";
    $sql .= "JOIN nwc.members c on a.ref_to = c.mbrID ";
    $sql .= "JOIN nwc.temperature d ON a.ref_temp = d.tmpID ";
    $sql .= "WHERE a.ref_date between '$data->dateStart' and '$data->dateStop' ";
    $sql .= "ORDER BY a.ref_date ASC ";
  
      $result = mysqli_query($conn, $sql);
      if (!$result) {
        echo "Error: " . $sql . '<br>' ;
      } else {
        while ($row = mysqli_fetch_assoc($result)) {
          $myArray[] = $row;
        }
        echo json_encode($myArray);
      }
    } else {
      echo 'nosession';
    }    
}

else if ($data->task == 'getClosedBusiness') {
    $myArray = array();
    if(true)
    {

    $sql  = "SELECT ";
    $sql .= "a.refID AS ID, ";
    $sql .= "to_char(a.ref_date, 'Mon DD, YYYY') AS ReferralDate, ";
    $sql .= "b.mbr_f_name || ' ' || b.mbr_l_name AS From, ";
    $sql .= "c.mbr_f_name || ' ' || c.mbr_l_name AS To, ";
    $sql .= "a.ref_desc AS Description, ";
    $sql .= "to_char(a.ref_closed_date, 'Mon DD, YYYY') AS closed, ";
    $sql .= "a.ref_closed_value AS value ";
    $sql .= "FROM ";
    $sql .= "nwc.referrals a ";
    $sql .= "JOIN nwc.members b on a.ref_from = b.mbrID ";
    $sql .= "JOIN nwc.members c on a.ref_to = c.mbrID ";
    $sql .= "WHERE ";
    $sql .= "a.ref_closed_date IS NOT NULL ";
    $sql .= "ORDER BY a.ref_closed_date ASC ";
  
      $result = mysqli_query($conn, $sql);
      if (!$result) {
        echo "Error: " . $sql . '<br>' ;
      } else {
        while ($row = mysqli_fetch_assoc($result)) {
          $myArray[] = $row;
        }
        echo json_encode($myArray);
      }
    } else {
      echo 'nosession';
    }    
}

if ($debug) {
  fwrite($fp , 'task = ' . print_r($data->task,1));
  fwrite($fp , "\n");
  fwrite($fp , print_r($data,1));
  fwrite($fp , "\n");
  fwrite($fp , 'sql = ' . $sql);
  fwrite($fp , "\n");
  fwrite($fp , 'session = ' . $_SESSION["currentuser"]);
  fwrite($fp , "\n");
  if (sizeof($myArray) > 0) {
    fwrite($fp , json_encode($myArray));
    fwrite($fp , "\n");
  }
}

?>