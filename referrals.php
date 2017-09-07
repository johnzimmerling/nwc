<?php
$fp = fopen('test.txt','a+');
$debug = false;

//Store MySQL connection string variables in private file, and buffer the output
ob_start();
require './PRIVATE.php';
ob_end_clean();

session_start();

//Connect to MySQL Database
$conn=mysqli_connect($ip,$user,$pass,$db) or die('Error connecting to MySQL server: ' . mysqli_error(@conn));

//Collect input contents
$data = json_decode(file_get_contents("php://input"));

if ($data->task == 'validate') {
  $debug = true;
  $myArray    = array();

  $sql  = "select * from nwc.members where ";
  $sql .= "onlineID='"   . $data->onlineID . "' and ";
  $sql .= "password='" . $data->password . "'";

  $result = mysqli_query($conn, $sql);
  $member = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $row_cnt = mysqli_num_rows($result);
  if ($row_cnt == 1) { 

    
    $sql = "select count(*) from nwc.referrals where ref_to=" . $member['mbrID'] . ";";
    $result = mysqli_query($conn, $sql);
   
    $myArray['validated'] = 'success';
    $myArray['notifications'] = mysqli_fetch_row($result)[0];
      
    $myArray['member'] = $member;
    

    echo json_encode($myArray);
    $_SESSION["currentuser"] = $data->onlineID;
  } else {
    echo 'The onlineID or password you have entered is invalid.';
  }
}

else if ($data->task == 'getsessiondata') {
    $myArray = array();
    if(isset($_SESSION["currentuser"]))
    {
      $sql = "SELECT * FROM nwc.members where onlineID='" . $_SESSION["currentuser"] . "';";
      $result = mysqli_query($conn, $sql);
      if (!$result) {
        echo "Error: " . $sql . '<br>' ;
      } else {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
          $myArray[] = $row;
        }
        if (sizeof($myArray) ==0) {
          $myArray['mbrID'] = 'new';
        }
        echo json_encode($myArray);
      }
    } else {
      echo 'nosession';
    }    
}

else if ($data->task == 'getTemperature') {
    $myArray = array();
    if(isset($_SESSION["currentuser"]))
    {
      $sql = "SELECT * FROM nwc.temp";
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

else if ($data->task == 'getMemberInfo') {
    $myArray = array();
    if(isset($_SESSION["currentuser"]))
    {
      $sql = "SELECT * FROM nwc.members where onlineID != '" . $_SESSION["currentuser"] . "'";
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

else if ($data->task == 'getReferrals') {
    $debug = false ;
    $myArray = array();
    if(true)
    {
      $sql  = "SELECT concat(c1.mbr_f_name,' ',c1.mbr_l_name) AS referralfrom, ";
      $sql .= "r.ref_desc as description, ";
      $sql .= "date_format(r.ref_date,'%b %d, %Y') as referral_date, ";
      // $sql .= "r.refID as id, ";
      $sql .= "concat(r.ref_cnt_f_name,' ',r.ref_cnt_l_name) as contact, ";
      $sql .= "r.ref_cnt_title as title, ";
      $sql .= "r.ref_cnt_phone as phone, ";
      $sql .= "r.ref_cnt_email as email, ";
      $sql .= "r.ref_location as location, ";
      $sql .= "r.ref_type as type, ";
      $sql .= "t.tmpDesc as temperature, ";
      //$sql .= "date_format(r.ref_closed_date,'%b %d, %Y') as Close_Date, ";
      $sql .= "r.ref_mark_read as markread, ";      
      $sql .= "r.ref_delivery as delivery, ";      
      $sql .= "r.ref_closed_value as value "; 
      $sql .= "FROM ";
      $sql .= "nwc.referrals AS r ";
      $sql .= "JOIN ";
      $sql .= "nwc.members AS c1 ON r.ref_from  = c1.mbrID ";
      $sql .= "JOIN ";
      $sql .= "nwc.members AS c2 ON r.ref_to = c2.mbrID ";
      $sql .= "JOIN ";
      $sql .= "nwc.temp AS t ON r.ref_temp = t.tmpID ";
      $sql .= "WHERE ";
      $sql .= "r.ref_to = $data->id " ;
      //$sql .= "AND r.ref_date between '$data->dateStart' and '$data->dateStop' ";      
      $sql .= "ORDER BY ";
      $sql .= "r.ref_date";

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

else if ($data->task == 'register') {
    $pword_type = 0 ;
    $member_type = 0 ;
    $sql = "select count(*) from nwc.members where email='" . $data->userInfo->email . "' or onlineID = '" . $data->userInfo->onlineID . "'";
    $result = mysqli_query($conn, $sql);
    $row_cnt = mysqli_fetch_row($result);

    if ($row_cnt[0] >= 1) {
        echo 'onlineID or email already exists';
    } else {
        $sql  = "insert into nwc.members (";
        $sql .= "mbr_f_name, ";
        $sql .= "mbr_l_name, ";
        $sql .= "mbr_co, ";
        if (!is_null($data->userInfo->occupation)){
          $sql .= "mbr_title, ";
        }
        $sql .= "mbr_email, ";
        $sql .= "mbr_phone, ";
        if (!is_null($data->userInfo->phone_secondary)){
          $sql .= "mbr_alt_phone, ";
        }
        if (!is_null($data->userInfo->comments)){
          $sql .= "mbr_desc, ";
        }
        $sql .= "password,";
        $sql .= "password_type,";
        $sql .= "mbr_type) values ('";

        $sql .= $data->userInfo->name_first        . "', '";
        $sql .= $data->userInfo->name_last         . "', '";
        $sql .= $data->userInfo->name_business     . "', '";
        if (!is_null($data->userInfo->occupation)){
          $sql .= $data->userInfo->occupation      . "', '";
        }
        $sql .= $data->userInfo->email             . "', '";
        $sql .= $data->userInfo->phone_main        . "', '";
        if (!is_null($data->userInfo->phone_secondary)){
          $sql .= $data->userInfo->phone_secondary . "', '";
        }
        if (!is_null($data->userInfo->comments)){
          $sql .= $data->userInfo->comments        . "', '";
        }
        $sql .= $data->userInfo->password          . "', '";
        $sql .= $pword_type                        . "', '";
        $sql .= $member_type                       . "'); ";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
          echo "Error: " . $sql . "<br>" ;
        } else {
          echo 'success';
        }            
    }
}

else if ($data->task == 'logout') {    
    unset($_SESSION['currentuser']);
    if(!isset($_SESSION['currentuser'])){
        echo 'success';
    } else {
        echo 'failed to destroy session';
    }
}

else if ($data->task == 'insertNewReferral') {
  $debug = true;

  $sql  = 'insert into nwc.referrals (' ;
  $sql .= "ref_from, ";
  $sql .= "ref_to, ";
  $sql .= "ref_date, ";
  $sql .= "ref_desc, ";
  $sql .= "ref_location, ";
  $sql .= "ref_cnt_f_name, ";
  $sql .= "ref_cnt_l_name, ";
  $sql .= "ref_cnt_title, ";
  $sql .= "ref_cnt_phone, ";
  $sql .= "ref_cnt_email, ";
  $sql .= "ref_type, ";
  $sql .= "ref_temp, ";
  $sql .= "ref_delivery, ";
  $sql .= "ref_mark_read) ";
  $sql .= "values ('";
  $sql .= $data->referral->originator                . "', '"; 
  $sql .= $data->referral->recipient->mbrID          . "', '";
  $sql .= $data->referral->date                      . "', '";
  $sql .= $data->referral->description               . "', '"; 
  $sql .= $data->referral->location                  . "', '"; 
  $sql .= $data->referral->contactNameFirst          . "', '"; 
  $sql .= $data->referral->contactNameLast           . "', '"; 
  $sql .= $data->referral->occupation                . "', '"; 
  $sql .= $data->referral->phone                     . "', '"; 
  $sql .= $data->referral->email                     . "', '"; 
  $sql .= $data->referral->type->description         . "', '"; 
  $sql .= $data->referral->temperature->tmpID        . "', '"; 
  $sql .= $data->referral->delivery->description     . "', '"; 
  $sql .= 'new'                                      . "') ;";

  $result = mysqli_query($conn, $sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" ;
  } else {
    echo 'success';
  }            

}

else if ($data->task == 'updateuser') {
// use !is_null() to avoid inserting null strings into nwc.members for non-required fields
    if(isset($_SESSION["currentuser"]))
    {
        $sql = "select count(*) from nwc.members where email !='" . $data->email . "' and onlineID = '" . $data->username . "'";

        $result = mysqli_query($conn, $sql);
        $row_cnt = mysqli_fetch_row($result);

        if ($row_cnt[0] == 1) { 
          echo ('usernameexists');
        } else {
          $sql  = "update nwc.members set ";
          $sql .= "mbr_f_name = '"      . $data->userInfo->name_first      . "', ";
          $sql .= "mbr_l_name = '"       . $data->userInfo->name_last       . "', ";
          $sql .= "mbr_co = '"   . $data->userInfo->name_business   . "', ";
          if (!is_null($data->userInfo->occupation)){
            $sql .= "mbr_title = '"      . $data->userInfo->occupation      . "', ";
          }
          $sql .= "mbr_email = '"           . $data->userInfo->email           . "', ";
          $sql .= "mbr_phone = '"      . $data->userInfo->phone_main      . "', ";
          if (!is_null($data->userInfo->phone_secondary)){
            $sql .= "mbr_alt_phone = '" . $data->userInfo->phone_secondary . "', ";
          }
          if (!is_null($data->userInfo->comments)){
            $sql .= "mbr_desc = '"        . $data->userInfo->comments        . "', ";
          }
          $sql .= "password = '"        . $data->userInfo->password        . "'  ";
          $sql .= "where onlineID ='"         . $data->userInfo->id              . "'; ";
          $result = mysqli_query($conn, $sql);
          if (!$result) {
            echo "Error: " . $sql . "<br>" ;
          } else {
            echo 'success';
          }            
        }
    } else {
        echo 'nosession';
    }
}

else if ($data->task == 'updateReferral') {
// don't do an update without values 
  $debug = false;
  $processUpdate = false;

  $sql  = "update nwc.referrals ";
  $sql .= "set ";

  if (!is_null($data->referral->dateclosed)){
      $processUpdate = true;
      $sql .= "ref_closed_date='" . $data->referral->closereferraldate . "' " ;
  }

  if (!is_null($data->referral->dollarestimate)){
      $processUpdate = true;
      if (!is_null($data->referral->dateclosed)){
          $sql .= ", ";
      }
      $sql .= "ref_closed_value='" . $data->referral->dollarestimate . "' ";
  }   

  if (!is_null($data->referral->markread)){
      $processUpdate = true;
      if (!is_null($data->referral->dateclosed) || !is_null($data->referral->dollarestimate) ){
          $sql .= ", ";
      }
      $sql .= "ref_markread='" . $data->referral->markread . "' ";
  }   


  $sql .= "where refID = '" . $data->referral->id . "';";

  if ($processUpdate){
    $result = mysqli_query($conn, $sql);  
    if (!$result) {
      echo "Error: " . $sql . "<br>" ;
    }        
  }
  echo 'success';
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
  $debug = false ;
}

?>
