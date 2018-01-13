<?php
//Author:  Shanna Epstein
//Date:    January 18, 2017
//This script removes pdf files for papers from scroing_item table where date_to_remove <= today

require("dbstring.php");
ini_set('max_execution_time', 400000);

$log_msg="";
$error_msg="";
$root_folder="";
$view_date="";
$deleted_files="";
$commit=0;
$today =  date ("Y-m-d H:i:s");

if($view_date==''){
        //$view_date = $HTTP_GET_VARS["date"];
        if ($view_date == ""){
            //$view_date = date('Y-m-d', time()-86400);
            $view_date =  date ("Y-m-d");
        }
    }

echo $server_name." is server name.\n";

if (($server_name=='test.nwp.org') || ($server_name=='awc-staging') || ($server_name=='assessment')) 
{
  $root_folder="/home/apache/assessment/app";
} else {
  $root_folder="/Library/WebServer/Documents/Assessment/app";
}

$log_folder = "/logs/";
$papers_folder = "/uploads/papers/";

$logfilename_mainlog	= $root_folder.$log_folder."remove_pdfs.log";
echo "logging to: ".$logfilename_mainlog." \n";

$logfile_mainlog		= fopen($logfilename_mainlog, "a");


$result = fwrite($logfile_mainlog, "BEGIN LOG\n");
if (!$result)
{
  $error_msg .= "Failed to write to FILE\n";
}



function emailNotification($error_msg, $server_name, $logfile_mainlog, $logfilename_mainlog) {
  if ($error_msg !="") {
    //Email developer if any errors occurred
    $subject = "CRON job for remove_pdfs.php failed on server ".$server_name;
    mail("sepsten@nwp.org,jstapleton@nwp.org", $subject, "Error message: ".$error_msg."\r\nLook here: ".$logfilename_mainlog, "Importance: High\r\n");
    print "Script completed with ERRORS.  Sent notification e-mail to developer."."\n";
    fwrite($logfile_mainlog, "Script completed with ERRORS.  Sent notification e-mail to developer.\n");
  }
}

try {
    $dbh = new PDO("mysql:host=$SID;dbname=$DID", $UID, $PWD);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database";
    fwrite($logfile_mainlog, "Connected to database\n");
    $dbh->beginTransaction();
    $main_query = "SELECT id,file_type FROM scoring_item WHERE date_to_remove <= '".$view_date."' and file_id is not null";
    print $main_query."\n";
    fwrite($logfile_mainlog, "Main query: \n".$main_query."\n");
    $statement = $dbh->prepare($main_query);
    $statement->execute();
    $count_select = $statement->rowCount();
    fwrite($logfile_mainlog, $count_select." records will be processed: \n");
    
    while($row = $statement->fetch(PDO::FETCH_BOTH)) {
        
        
         if (($row[0]!="") && ($row[1]!="")) { //delete the physical paper files
            
            $filename = $root_folder.$papers_folder.$row[0].".".$row[1];
            print $filename."\n";
             if (file_exists($filename)) {
                if (unlink ($filename)) {
                    $deleted_files .= $row[0].",";
                    print  $row[0].".".$row[1]." file deleted: \n";
                    fwrite($logfile_mainlog, $row[0].".".$row[1]." file deleted: \n");
                } else {
                   fwrite($logfile_mainlog, "ID ".$row[0]." file was not deleted.  System could not delete.: \n");
                    print "ID ".$row[0]." file was not deleted:System could not delete. \n"; 
                }
              }  else {
                  fwrite($logfile_mainlog, "Filename does not exist:".$filename." \n");
                  print "Filename does not exist:".$filename." \n";
              }
         } else {
             fwrite($logfile_mainlog, "ID ".$row[0]." file was not deleted. Name or extension missing.: \n");
             print "ID ".$row[0]." file was not deleted. Name or extension missing: \n";
         }
      }
      
      $deleted_files = substr($deleted_files, 0, -1); //strip last comma
      
      if ($deleted_files!="") {
         //update date_removed. 
          $update_query ="UPDATE scoring_item set file_id=null, file_type=null, date_removed='".$today."' 
                                                WHERE id IN (".$deleted_files.")";
          $count_update = $dbh->exec($update_query);
          fwrite($logfile_mainlog, "Update Query: \n".$update_query."\n");
          fwrite($logfile_mainlog, $count_update." rows were updated for IDs: ".$deleted_files."\n");
          $commit=1;
      }
      
      //all queries will be committed at this time
       if ($commit==1) {
            $dbh->commit();
            print "changes COMMITED \n";
            fwrite($logfile_mainlog, "Changes were COMMITTED\n");
       } else {
           print "changes NOT COMMITED.  There are no records to UPDATE. \n";
           fwrite($logfile_mainlog, "Changes NOT COMMITTED.  There are no records to UPDATE.\n");
           
       }
    
    }

catch(PDOException $e)
    {
    echo $e->getMessage();
    $error_msg .= "Exception occurred\n";
    $error_msg .= "Error Message: ".$e->getMessage()."\n";
    fwrite($logfile_mainlog, $error_msg."\n");
    
    $dbh->rollBack();
    print "changes were ROLLED BACK \n";
    fwrite($logfile_mainlog, "Changes were ROLLED BACK\n");
    emailNotification($error_msg, $server_name, $logfile_mainlog, $logfilename_mainlog);
    }




print $view_date."\n";
fwrite($logfile_mainlog, "\nRemove date is ".$view_date."\n");


$dbh = NULL; //release database handle

emailNotification($error_msg, $server_name, $logfile_mainlog, $logfilename_mainlog);

//print (time()-$then)." seconds elapsed\n";
//fwrite($logfile_mainlog, (time()-$then)." seconds elapsed\n");
fwrite($logfile_mainlog, "END LOG\n");


