<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\EventUser;

/**
 * EventUser controller.
 *
 * @Route("/")
 */
class EventUserController extends BaseController
{    
    /**
     * Displays a form to upload multiple scoring item entities.
     *
     * @Route("/projectsite/eventuser/new_multiple", name="projectsite_eventuser_new_multiple")
     * @Template();
     */
    
    public function newMultipleAction()
    {   

       if (!$this->checkAccess("create multiple",null,"EventUser")) {
            throw new AccessDeniedException();
       }
       
       $role_admin_id=$this->isRoleAdmin();
       $event_capability_array="";
       $events_with_access_size=0;
       $project_id=0;     
       //user has access, begin processing the script    
       ini_set('memory_limit', '-1');
       ini_set('max_execution_time', 300);
       
       $today = date ("Y-m-d H:i:s");
       
       $request = $this->getRequest();
    
       $form1 = $this->get('form.factory')->createNamedBuilder('csv_upload_form', 'form', null)
        ->add('csvFile', 'file', array('label' =>'Choose .csv file','required' =>true))
        ->getForm();
        
       if ($request->getMethod('post') == 'POST') {
            
            $error_msg_array=array();
            $error_msg = "";
            if ($request->request->has('csv_upload_form')) {
             
            $form1->bind($request);
            
            if ($form1->isValid()) { 
                $file = $form1['csvFile']->getData();
                $file_type = $file->getClientMimeType();
                $ext = pathinfo($file->getClientOriginalName())['extension'];      
                  
                $csv_mimetypes = array(
                    'text/csv',
                    'text/plain',
                    'application/csv',
                    'text/comma-separated-values',
                    'application/excel',
                    'application/vnd.ms-excel',
                    'application/vnd.msexcel',
                    'text/anytext',
                    'application/octet-stream',
                    'application/txt',
                );

                if ((in_array($file_type, $csv_mimetypes)) && ($ext="csv")) {  //uploaded file is one of possible csv mime types and has csv extension
                    $file_path = __DIR__.'/../../../../'.$this->container->getParameter('nwp_assessment.file_uploads').'/csv/';
                    $user_id = $this->container->get('security.context')->getToken()->getUser()->getId();
                    $file_name=$file_path.$user_id.".".$ext;
                    
                    move_uploaded_file($file, $file_name); 
                    $handle = fopen($file_name, "r");            
                    $data = array_map("str_getcsv", preg_split('/[\r\n]+/', file_get_contents($file_name)));
                    
                    $row=1;
                    
                    //initialize data                   
                    $grade_level_array = $this->getApplicationValues("NwpAssessmentBundle:GradeLevel", "g", "g.id,g.name");
                    $event_array = $this->getApplicationValues("NwpAssessmentBundle:Event", "e", "e.id,e.name",null,"CurrentEventArrayUserSession",null,null,"e.endDate >='".$today."'");
                    
                    if ($request->request->has('action')) {
                        $action = $request->get('action');
                    } else {
                        $action="update";  //default to update, only admins have update multiple records access for now
                    }
                    
                    $column_array = array( array( 'column_id' => "1",
                        'column_name' => "user_id",
                        'column_desc' => "User",
                    ),
                    array( 'column_id' => "2",
                        'column_name' => "event_id",
                        'column_desc' => "Event", 
                    ),
                    array( 'column_id' => "3",
                        'column_name' => "grade_level_id",
                        'column_desc' => "Student Grade Level",
                    ),
                    array( 'column_id' => "4",
                        'column_name' => "table_id",
                        'column_desc' => "Table",
                    ),
                    array( 'column_id' => "5",
                        'column_name' => "role_id",
                        'column_desc' => "Role",
                        'error_type_check' => "0",   
                    ),
                    array( 'column_id' => "6",
                        'column_name' => "target",
                        'column_desc' => "Target",
                        'error_type_check' => "1",
                        'table_name' => "temp_table1"
                    ),
                    array( 'column_id' => "7",
                        'column_name' => "max_block",
                        'column_desc' => "Max Block",
                        'error_type_check' => "1",
                        'table_name' => "temp_table1"
                    ));  
  
                    if ($action=="update") { //special processing for updating records                     
                        
                        $event_user_id_array=array(); //get ids of event_users they have access to update (based on event access)
                    
                        for($e=0;$e<sizeof($event_array);$e++){ 
                            $event_user_id_array[] = $event_array[$e]['id'];
                        }
                      
                        $Ids = implode(",",$event_user_id_array);

                        $event_user_array = $this->getApplicationValues("NwpAssessmentBundle:EventUser", "eu", "eu.id",null,null,null,null,"eu.event IN (".$Ids.")",null);
 
                        //now add id column to original column array
                        $column_array_update = array( array( 'column_id' => "0",
                            'column_name' => "id",
                            'column_desc' => "Event User Id",
                            'error_type_check' => "0,1,4",
                            'search_array' => $event_user_array,
                            'search_array_size' => sizeof($event_user_array),
                            'table_name' => "temp_table1"
                        ));
                        
                        $column_array = array_merge($column_array_update,$column_array); //add id as the first column to the original column array
                    }
                    
                    $column_array_size=sizeof($column_array);

                    $columns_in_file = sizeof($data[0]);  //column number does not match, no need to do further processing
                    if ($columns_in_file == $column_array_size) {
                        $error_type_array = 
                        array( array( 'error_id' => "0",
                            'error_name' => "Required",
                            'error_message' => " can not be empty"
                         ),
                        array( 'error_id' => "1",
                            'error_name' => "Numeric",
                            'error_message' => " must be numeric"
                         ),
                        array( 'error_id' => "2",
                            'error_name' => "Maxlength",
                            'error_message' => " exceeds the allowed number of characters"
                        ),
                        array( 'error_id' => "3",
                            'error_name' => "Length",
                            'error_message' => " number of characters must equal to "
                        ),
                        array( 'error_id' => "4",
                            'error_name' => "Foreign Key",
                            'error_message' => " must be an existing value"
                        )
                        );
                     
                        $error_type_array_size=sizeof($error_type_array);
                    
                        $temp_table1="event_user";
                        
                        if ($action=="update") {
                            $sql = "UPDATE ".$temp_table1." SET target= :target,max_block = :max_block
                                                       WHERE id = :id";            
                        } //end of update
                                
                        try {
                    
                        $dbh= $this->get('database_connection');
                        $dbh->beginTransaction();
                    
                        //prepare records to insert into event_user table
                        $stmt = $dbh->prepare($sql);
                        
                        foreach($data as $value) { //each row
  
                        if (($row>1) && !(($row==sizeof($data)) && (sizeof($value)==1))) {//do not process header row or last row if array size is 1 (windows csv makes extra blank row with one column)
                          
                            $event_user_id= "";
                            $new_data= "";
                            $unique_id = "";
                            foreach($value as $key => $val) {  
                              
                                if (isset( $column_array[$key]["error_type_check"])) {
                                    $error_checks = explode(",", $column_array[$key]["error_type_check"]);      

                                    foreach($error_checks as $check) {
                                        $result = $this->errorCheckCsv($check,$val,$key,$column_array,$error_type_array,$project_id,$row,$new_data,$data);
                                            $valid=$result[0];
                                            $new_data=$result[3];
                                            if ($valid==false) { 
                                                $error_msg_array[$row][$key][$check] = $column_array[$key]["column_desc"].$error_type_array[$check]["error_message"]." ".$result[2];                                         
                                            }
                                    } 
                                }    
                                            
                                $val=$result[1];
                                
                                //unique id check
                                if ($column_array[$key]["column_name"]=="id") { //we are updating records
                                    $event_user_id=$val;
                                }

                                //bind values
                                if ($error_msg_array==null) {
                                    if ((isset ($column_array[$key]["table_name"])) && ($column_array[$key]["table_name"]=="temp_table1")) {                          
                                        $stmt->bindValue($column_array[$key]["column_name"], $val);//bind value to scoring_item table
                                    } 
                                }
                             } //end of loop that goes through each value in a row
                             

                       if ($error_msg_array==null) { //upload to table if there are no errors in .csv file
                            $stmt->execute();
                        } 
                             
                    }
                       
                    $row++;
                        
               } //end of row processing loop
                  
                if ($error_msg_array==null) { 
                    $dbh->commit();
                }
                    
            } catch (Exception $e){
                $dbh->rollback();
                $error_msg=$e->getMessage();
            }    
                 $dbh = NULL; //release database handle   
                 fclose($handle); //close file 
                 unlink ($file_name); //delete .csv file
        } else {
            $error_msg = "The number of columns in the .csv file does not match the number of columns required for your action";
        }
            
    } else {
        $error_msg = "Please attach a valid .csv file";        
                }
    } else {
        $this->get('session')->getFlashBag()->add('error', 'flash.create.error'); //form is invalid
    }   
    
             
    if ($error_msg_array !=null) {
        return $this->render('NwpAssessmentBundle:EventUser:errors_csv_multiple.html.twig', array( "column_array" => $column_array, "error_type_array" => $error_type_array, "error_msg_array" => $error_msg_array));
    } else {
        if ($error_msg!="") {
            $this->get('session')->getFlashBag()->add('error', "The following error occurred: ".$error_msg.". Your file has not been uploaded to the database.");   
        } else {
            $this->get('session')->getFlashBag()->add('success', 'Your .csv file was successfully uploaded to the database.');
        }
    }          
   }     
 } 
 
 return $this->render('NwpAssessmentBundle:EventUser:new_multiple.html.twig', array('form1' => $form1->createView()));
}
    
    
   
}
