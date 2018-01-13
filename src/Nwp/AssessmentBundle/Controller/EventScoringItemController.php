<?php

namespace Nwp\AssessmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Nwp\AssessmentBundle\Entity\EventScoringItem;
use Nwp\AssessmentBundle\Form\EventScoringItemFilterType;

/**
 * EventScoringItem controller.
 *
 * @Route("/projectsite/eventscoringitem")
 */
class EventScoringItemController extends BaseController
{
    /**
     * Lists all EventScoringItem entities.
     *
     * @Route("/", name="projectsite_eventscoringitem")
     * @Template()
     */
    public function indexAction()
    {
        if (!$this->checkAccess("list",null,"EventScoringItem")) {
            throw new AccessDeniedException();
        }
        
        $role_admin_id=$this->isRoleAdmin();
        
        $project_event_capability_array = $this->getUserProjectRoleEventCapabilities();
        
        //var_dump($project_event_capability_array);
        
        if ($this->get('request')->get('btn_batch_action')) {
            return $this->render('NwpAssessmentBundle:Default:batch_confirmation.html.twig', array('entity_path' =>'projectsite_eventscoringitem', 'request_data' => $this->get('request')->request->all()));   
        }
        
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

       //var_dump($project_event_capability_array);
    
        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'role_admin_id' => $role_admin_id,
            'project_event_capability_array' => $project_event_capability_array,
        );
    }
    
    /**
     * Batch Detete for EventScoringItem entities.
     *
     * @Route("/batch/action/delete", name="projectsite_eventscoringitem_batch_action_delete")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    
    public function batchActionDelete(){
        
        $items_array = $this->batchApplicationAction("EventScoringItem");
          
        if ($items_array != null) {
            //first check access, optimize later so the query doesn't have to be selected again, project ids will be passed through original query
            $Ids = implode(",", $items_array['ids']);
            #$data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\EventScoringItem", "es", null, null, null, null,null, "es.id in (".$Ids.")",'es.id');
            $data=  $this->getApplicationValues("Nwp\AssessmentBundle\Entity\EventScoringItem", "esi", null, null, null, "scoringItem","s", "esi.id in (".$Ids.")",'esi.id');  
            //check that user has access to delete all event papers selected for batch delete(based on project access)
            $project_event_combo_ids="";
            $scoring_item_ids=array();
            $scoring_item_exts=array();
            
            foreach ($data as $d) {
                $project_event_combo_ids .= $d->getScoringItem()->getProject()->getId().",".$d->getEvent()->getId().";";
                $scoring_item_ids[]= $d->getScoringItem()->getId();
                $scoring_item_exts[]= $d->getScoringItem()->getFileType();
            }
            $project_event_combo_ids = substr($project_event_combo_ids, 0, -1); //strip last comma
           
            if (!$this->checkAccess("delete",$project_event_combo_ids,"EventScoringItem")) {
                throw new AccessDeniedException();
            }
            //end of access check
            
            $entities_array=array();
                      
            $entities_array[0]['classname']="Nwp\AssessmentBundle\Entity\EventScoringItemGrouping";
            $entities_array[0]['alias']="eg";
            $entities_array[0]['fieldname']="eventScoringItem";
            $entities_array[0]['ids']=$items_array['ids'];
            
            $entities_array[1]['classname']="Nwp\AssessmentBundle\Entity\EventScoringItem";
            $entities_array[1]['alias']="es";
            $entities_array[1]['fieldname']="id";
            $entities_array[1]['ids']=$items_array['ids'];
            
            //Delete action also deletes from scoring_item table
            $entities_array[2]['classname']="Nwp\AssessmentBundle\Entity\ScoringItem";
            $entities_array[2]['alias']="s";
            $entities_array[2]['fieldname']="id";
            $entities_array[2]['ids']=$scoring_item_ids;
            $entities_array[2]['exts']=$scoring_item_exts;
           
            
                     
            $this->batchApplicationActionDelete($entities_array,'Event Papers','Project Papers');
         }
         
        return $this->redirect($this->generateUrl('projectsite_eventscoringitem'));     
    }
    
    /**
     * Batch Unassign for EventScoringItem entities.
     *
     * @Route("/batch/action/unassign", name="projectsite_eventscoringitem_batch_action_unassign")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    
    public function batchActionUnassign(){
        
        //Unassign action only deletes items from event_scoring_item table, not from scoring_item
        
        $items_array = $this->batchApplicationAction("EventScoringItem");
          
        if ($items_array != null) {
            //first check access, optimize later so the query doesn't have to be selected again, project ids will be passed through original query
            $Ids = implode(",", $items_array['ids']);
            $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\EventScoringItem", "es", null, null, null, null,null, "es.id in (".$Ids.")",'es.id');
           
            //check that user has access to delete all event papers selected for batch delete(based on project access)
            $project_event_combo_ids="";
           
            
            foreach ($data as $d) {
               $project_event_combo_ids .= $d->getScoringItem()->getProject()->getId().",".$d->getEvent()->getId().";";
            }
           $project_event_combo_ids = substr($project_event_combo_ids, 0, -1); //strip last comma
           
            if (!$this->checkAccess("unassign",$project_event_combo_ids,"EventScoringItem")) {
                throw new AccessDeniedException();
            }
            //end of access check
            
            $entities_array=array();
                      
            $entities_array[0]['classname']="Nwp\AssessmentBundle\Entity\EventScoringItemGrouping";
            $entities_array[0]['alias']="eg";
            $entities_array[0]['fieldname']="eventScoringItem";
            $entities_array[0]['ids']=$items_array['ids'];
            
            $entities_array[1]['classname']="Nwp\AssessmentBundle\Entity\EventScoringItem";
            $entities_array[1]['alias']="es";
            $entities_array[1]['fieldname']="id";
            $entities_array[1]['ids']=$items_array['ids'];
                     
            $this->batchApplicationActionDelete($entities_array,'Event Papers','Project Papers');
         }
         
        return $this->redirect($this->generateUrl('projectsite_eventscoringitem'));     
    }
    
    /**
     * Batch Export for EventScoringItem entities.
     *
     * @Route("/batch/action/export", name="projectsite_eventscoringitem_batch_action_export")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    public function batchActionExportData(){
        
        $items_array = $this->batchApplicationAction("EventScoringItem");
        
        if ($items_array != null) {
            if ($items_array['entities'] !="") { //we already have the data from filter query on list page
                $data=$items_array['entities'];
            } else {
                $Ids = implode(",", $items_array['ids']); //a subset of the filter query on list page was selected, so we need to requery
                $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\EventScoringItem", "es", null, null, null, null,null, "es.id in (".$Ids.")",'es.id');
            }
            //check that user has access to export for all papers selected for export (based on project access)
            $project_event_combo_ids="";
            foreach ($data as $d) {
                $project_event_combo_ids .= $d->getScoringItem()->getProject()->getId().",".$d->getEvent()->getId().";";
            }
            $project_event_combo_ids = substr($project_event_combo_ids, 0, -1); //strip last comma
            if (!$this->checkAccess("edit",$project_event_combo_ids,"EventScoringItem")) {
                throw new AccessDeniedException();
            }
            //end of access check 
            $fields=array('id','scoringItem','event','scoringItemType','isRandom');
            $export= $this->batchApplicationActionExport("EventScoringItem",$fields,$data);
         }
         
        return $export;     
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        
        $role_admin_id=$this->isRoleAdmin();
        
        $projects=$this->getUserProjects(null, "EventScoringItem", "list");
        
        $projectIds="";
        foreach($projects as $project) {
            $projectIds .= $project->getId().",";
        }
        $projectIds = substr($projectIds, 0, -1); //strip last comma
        
        $events = $this->getUserEvents("projectsite",null,"EventScoringItem", "list");
        
        $EventIds="";
        foreach($events as $event) {
            $EventIds .= $event->getId().",";
        }
        $EventIds = substr($EventIds, 0, -1); //strip last comma
        
        $filterForm = $this->createForm(new EventScoringItemFilterType($events,$projectIds,$role_admin_id));
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = 
        $em->getRepository('NwpAssessmentBundle:EventScoringItem')
                          ->createQueryBuilder('esi')
                          ->leftJoin('esi.scoringItem', 's')
                          ->where('esi.event IN ('.$EventIds.')')
                          ->andWhere('s.project IN ('.$projectIds.')')
                          ->orderBy('esi.id');
        
         
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('EventScoringItemControllerFilter');
        }
    
        // Filter action
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'filter') {
             
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                
                // Save filter to session
                $filterData = $filterForm->getData();
                
                $session->set('EventScoringItemControllerFilter', $filterData);
                
                
            } else {
                //Filter was probably submitted with empty values, remove session info for the filter, so that the results are not filtered.
                $session->remove('EventScoringItemControllerFilter');
            }
        } else {
            // Get filter from session
            if ($session->has('EventScoringItemControllerFilter')) {
                $filterData = $session->get('EventScoringItemControllerFilter');
                //this code fixes "Entities passed to the choice field must be managed" symfony error message  
                foreach ($filterData as $key => $filter) { 
                    if (is_object($filter)) {
                        $filterData[$key] = $em->merge($filter);
                    }
                }
                $filterForm = $this->createForm(new EventScoringItemFilterType($events,$projectIds,$role_admin_id), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }
        
       // echo $queryBuilder->getDql();
        
        if (isset($filterData)) {
             $this->get('session')->getFlashBag()->add('info', 'flash.filter.success');
        }
        
        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();
    
        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('projectsite_eventscoringitem', array('page' => $page));
        };
    
        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));
    
        return array($entities, $pagerHtml);
    }
    
    /**
     * Finds and displays a EventScoringItem entity.
     *
     * @Route("/{id}/show", name="projectsite_eventscoringitem_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //$entity = $em->getRepository('NwpAssessmentBundle:EventScoringItem')->find($id);
        
       
         $queryBuilder =$em->getRepository('NwpAssessmentBundle:EventScoringItem')
                           ->createQueryBuilder('esi')
                           ->select('esi')
                           ->leftJoin('esi.scoringItem', 's')
                           ->Where('esi.id='.$id);
        
        $query = $queryBuilder->getQuery();
        $entity= $query->getSingleResult();
         
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EventScoringItem entity.');
        }
        
        $project_combo_id=$entity->getScoringItem()->getProject()->getId().",".$entity->getEvent()->getId();
        
        if (!$this->checkAccess("show",$project_combo_id,"EventScoringItem")) {
            throw new AccessDeniedException();
        }

        return array(
            'entity'      => $entity,
        );
    }
    
    /**
     * Displays a form to upload multiple scoring item entities.
     *
     * @Route("/new_multiple", name="projectsite_eventscoringitem_new_multiple")
     * @Template();
     */
    public function newMultipleAction()
    {    
     
       if (!$this->checkAccess("create multiple",null,"EventScoringItem")) {
            throw new AccessDeniedException();
       }
       
          
       ini_set('memory_limit', '-1');
       ini_set('max_execution_time', 300);
       
       $request = $this->getRequest();
    
       $form1 = $this->get('form.factory')->createNamedBuilder('csv_upload_form', 'form', null)
        ->add('csvFile', 'file', array('label' =>'Choose .csv file','required' =>true))
        ->getForm();
 
       if ($request->getMethod('post') == 'POST') {
            $today = date ("Y-m-d H:i:s");
            $error_msg_array=array();
            $error_msg = "";
            if ($request->request->has('csv_upload_form')) {
            //handle the first form   
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
                    
                    $project_array = $this->getUserProjects("p.id,p.name", "EventScoringItem", "create");
                    $event_array = $this->getApplicationValues("NwpAssessmentBundle:Event", "e", "e.id,e.name",null,"CurrentEventArrayUserSession",null,null,"e.endDate >='".$today."'");
                    $scoring_item_type_array = $this->getApplicationValues("NwpAssessmentBundle:ScoringItemType", "t", "t.id");
                    $is_random_array=array(0,1); //"0" for non-randomly selected, "1" for randomly selected
                    $component_array = $this->getApplicationValues("NwpAssessmentBundle:Component", "c", "c.id,c.name");
                    $grouping_array = $this->getApplicationValues("NwpAssessmentBundle:Grouping", "g", "g.id,g.name");
                    
                    $project_id_array=array(); //get ids of papers they have access to update (based on project access)
                    for($p=0;$p<sizeof($project_array);$p++){ 
                        $project_id_array[] = $project_array[$p]['id'];
                    }
                    $Ids = implode(",",$project_id_array);
                    $paper_array = $this->getApplicationValues("NwpAssessmentBundle:ScoringItem", "s", "s.id",null,null,null,null,"s.project IN (".$Ids.")",null);
                     
                    $column_array= array( array( 'column_id' => "0",
                        'column_name' => "scoring_item_id",
                        'column_desc' => "Paper Id",
                        'error_type_check' => "0,1,4",
                        'search_array' => $paper_array,
                        'search_array_size' => sizeof($paper_array),
                      ),
                    array( 'column_id' => "1",
                        'column_name' => "event_id",
                        'column_desc' => "Event",
                        'error_type_check' => "0,4",
                        'search_array' => $event_array,
                        'search_array_size' => sizeof($event_array),
                    ),
                    array( 'column_id' => "2",
                        'column_name' => "scoring_item_type_id",
                        'column_desc' => "Paper Type Id",
                        'error_type_check' => "0,1,4",
                        'search_array' => $scoring_item_type_array,
                        'search_array_size' => sizeof($scoring_item_type_array)
                    ),
                    array( 'column_id' => "3",
                        'column_name' => "is_random",
                        'column_desc' => "Randomly Selected",
                        'error_type_check' => "0,1,4",
                        'search_array' => $is_random_array,
                        'search_array_size' => sizeof($is_random_array)
                    ),
                    array( 'column_id' => "4",
                        'column_name' => "component_id",
                        'column_desc' => "Component",
                        'error_type_check' => "0,4",
                        'search_array' => $component_array,
                        'search_array_size' => sizeof($component_array)
                    ),
                    array( 'column_id' => "5",
                        'column_name' => "grouping_id",
                        'column_desc' => "Grouping",
                        'column_type' => "multi",
                        'error_type_check' => "4",
                        'search_array' => $grouping_array,
                        'search_array_size' => sizeof($grouping_array)
                    ));
                    
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
                        ),
                        array( 'error_id' => "5",
                            'error_name' => "Required Dependent",
                            'error_message' => "  can not be empty"
                        ),
                        array( 'error_id' => "6",
                            'error_name' => "Unique array",
                            'error_message' => "  has duplicate values in column"
                        )
                        );
                     
                        $error_type_array_size=sizeof($error_type_array);
                    
                        $temp_table="event_scoring_item";
                        $temp_table2="event_scoring_item_grouping";
                        
                        $em = $this->getDoctrine()->getEntityManager();
                        $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
                        
                        $sql = "INSERT INTO ".$temp_table."(scoring_item_id,event_id,component_id,scoring_item_type_id,is_random,status_id,scoring_round_number,read_number,date_updated) 
                                                        VALUES 
                                                        (:scoring_item_id,:event_id,:component_id,:scoring_item_type_id,:is_random,:status_id,:scoring_round_number,:read_number,:date_updated)";                  
                        try {
                    
                        $dbh= $this->get('database_connection');
                        $dbh->beginTransaction();
                    
                        //get unique combos that can no longer be entered into scoring_item table
                        $sql_unique = 'SELECT es.id, CONCAT(es.event_id,"_",es.scoring_item_id) AS uniqueId FROM event_scoring_item es ORDER BY es.id ASC';
                        $stmt_unique = $dbh->query($sql_unique); 
                        $unique_array = $stmt_unique->fetchAll(\PDO::FETCH_ASSOC);
                        $new_unique_array=array();
                        foreach($unique_array as $k=>$v){
                            $new_unique_array[$unique_array[$k]['id']]['uniqueId'] = $unique_array[$k]['uniqueId'];
                        }
                        
                        $unique_found=false;
                        
                        //prepare records to insert into scoring_item table
                        $stmt = $dbh->prepare($sql);

                        foreach($data as $value) { //each row
  
                        if (($row>1) && !(($row==sizeof($data)) && (sizeof($value)==1))) {//do not process header row or last row if array size is 1 (windows csv makes extra blank row with one column)
                            $unique_id = "";
                            $event_paper_id= "";
                            
                            foreach($value as $key => $val) {  
                                $groupings_ids="";
                                $error_checks = explode(",", $column_array[$key]["error_type_check"]); 
        
                                   if ($column_array[$key]["column_name"]=="grouping_id") { //column has multiple values separated by commas
                                        if ($val !="") {
                                            $groupings = array_map('trim', explode(',', $val));
                                            
                                            if (count($groupings) !=count(array_unique($groupings))) {
                                                $error_msg_array[$row][$key][0] =" grouping is not unique";
                                            }
                                            
                                            foreach($groupings as $group) {
                                                foreach($error_checks as $check) {
                                                    $result= $this->errorCheck($check,trim($group),$key,$column_array,$error_type_array);
                                                    $valid=$result[0];
                                                    $groupings_ids.=$result[1].",";
                                                    
                                                    if ($valid==false) { 
                                                        $error_msg_array[$row][$key][$check] = $column_array[$key]["column_desc"].$error_type_array[$check]["error_message"]." ".$result[2];                                         
                                                    }
                                                }
                                            }
                                           
                                        }
                                    } else {
                                        foreach($error_checks as $check) {
                                            $result = $this->errorCheck($check,$val,$key,$column_array,$error_type_array);
                                            $valid=$result[0];
                                            if ($valid==false) { 
                                                $error_msg_array[$row][$key][$check] = $column_array[$key]["column_desc"].$error_type_array[$check]["error_message"]." ".$result[2];                                         
                                            }
                                       } 
                                    } 
                                            
                                $val=$result[1];
                       
                                //unique id check
                                if ($column_array[$key]["column_name"]=="id") {
                                    $event_paper_id=$val;
                                }
                                if ($column_array[$key]["column_name"]=="event_id")  {
                                    $unique_id = $val."_".$unique_id;
                                } elseif ($column_array[$key]["column_name"]=="scoring_item_id") {
                                    $unique_id = $val;
                                }                               
                                
                                if ($error_msg_array==null) {
                                    if ($column_array[$key]["column_name"]!="grouping_id") { //column has multiple values separated by commas
                                       
                                        $stmt->bindValue($column_array[$key]["column_name"], $val);//bind value
                                    } 
                                }
                            }
     
                          
                            foreach($new_unique_array as $new_unique_key=>$new_unique_val){
                                if($new_unique_val['uniqueId']==$unique_id)  {
                                    $error_msg_array[$row]["uniqueId"]=true;            
                                    break;  
                                }
                             }
                            $new_unique_array[$unique_id]['uniqueId']=$unique_id; //add uniqueid from this row to unique_array
                           
                          
                            if ($error_msg_array==null) { //upload to table if there are no errors in .csv file
                               $stmt->bindValue("status_id", $status_ready);//bind value 
                               $stmt->bindValue("scoring_round_number", '1');//bind value
                               $stmt->bindValue("read_number", '1');//bind value  
                               $stmt->bindValue("date_updated",$today);//bind value
                               $stmt->execute();
                               
                               
                               
                               if ($groupings_ids !="") {
                                    $event_scoring_item_id = $dbh->lastInsertId();
                                    $groupings_ids_vals = array_map('trim', explode(',', substr($groupings_ids, 0, -1)));
                                    foreach($groupings_ids_vals as $group_val) {
                                        $sql2 = "INSERT INTO ".$temp_table2."(event_scoring_item_id,grouping_id) 
                                                 VALUES (:event_scoring_item_id,:grouping_id)"; 
                                                $stmt2 = $dbh->prepare($sql2);
                                                $stmt2->bindValue("event_scoring_item_id", $event_scoring_item_id);//bind value
                                                $stmt2->bindValue("grouping_id", $group_val);//bind value
                                                $stmt2->execute();
                                    }
                                }
                             } 
                         }
                       
                         $row++;
                     }
                    
                  
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
            return $this->render('NwpAssessmentBundle:EventScoringItem:errors_csv_multiple.html.twig', array( "column_array" => $column_array, "error_type_array" => $error_type_array, "error_msg_array" => $error_msg_array));
        } else {
            if ($error_msg!="") {
                $this->get('session')->getFlashBag()->add('error', "The following error occurred: ".$error_msg.". Your file has not been uploaded to the database.");   
            } else {
                $this->get('session')->getFlashBag()->add('success', 'Your .csv file was successfully uploaded to the database.');
            }
        }
            
     }     
 } 
 return $this->render('NwpAssessmentBundle:EventScoringItem:new_multiple.html.twig', array( 'form1' => $form1->createView()));
}


public function errorCheck($check,$val,$key,$column_array,$error_type_array) {
       $error_msg_spec ="";
       
        if ($check !="") {
            $valid=true;
            
            switch ($check)
            {   
            
                case 0://required
            if (($val==null) ||($val=="")) {
                $valid=false;
             } 
            break;
            
            case 1://numeric
            if ((($val!=null) && ($val!="")) && (!is_numeric($val))) {
                $valid=false;
            }
            break;
                                       
            case 4:
            if (isset($column_array[$key]["search_array"]) && ($column_array[$key]["search_array_size"]>0)) { 
                
                if (($val!=null) && ($val!="")) {
                                              
                    $found=false;
                                                
                    for($s=0;$s<$column_array[$key]["search_array_size"];$s++){  
                                                     
                        if ($column_array[$key]["column_name"]=="is_random") {
                            if ($column_array[$key]["search_array"][$s]==$val) {  
                                $found=true; 
                            }
                        } else if (($column_array[$key]["column_name"]=="event_id") || ($column_array[$key]["column_name"]=="component_id") || ($column_array[$key]["column_name"]=="grouping_id")) {
                            if ($column_array[$key]["search_array"][$s]['name']==$val) { 
                                $found=true; 
                            }
                        } else {
                            if ($column_array[$key]["search_array"][$s]['id']==$val) {  
                                $found=true; 
                            }
                        }
                        
                        if ($found==true) {                           
                            if ($column_array[$key]["column_name"]!="is_random") {
                                $val=$column_array[$key]["search_array"][$s]['id'];
                            }
                            break;
                        }
                                                   
                     }
                                                
                     if ($found==false) {
                        $valid=false;
                     }
                                                   
                   }
                                               
            } else { //array we are searching against is not set or has 0 items, for example user might not have any events or projects assigned to them
                $valid=false;
            }
            break;
            default: 
         }
         
         if ($valid==false) {
            
            if ($error_type_array[$check]["error_id"]=="4") {
                if ($column_array[$key]["column_name"]=="scoring_item_id") {
                    $error_msg_spec=" and you must have access to it";
                }
                if ($column_array[$key]["column_name"]=="event_id") {
                    $error_msg_spec=", and its End Date must be today or greater";
                }
            } 
                               
       }
     }   
     return array ($valid,$val,$error_msg_spec);
  }

}


