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

use Nwp\AssessmentBundle\Entity\ScoringItem;
use Nwp\AssessmentBundle\Entity\ProjectScoringItem;
use Nwp\AssessmentBundle\Form\ScoringItemType;
use Nwp\AssessmentBundle\Form\ScoringItemFilterType;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Config\Resource\FileResource;
use Doctrine\DBAL\Connection;

use Nwp\AssessmentBundle\Core\qqFileUploader;

/**
 * ScoringItem controller.
 *
 * @Route("/")
 */
class ScoringItemController extends BaseController
{
    /**
     * Lists all ScoringItem entities.
     *
     * @Route("/projectsite/scoringitem", name="projectsite_scoringitem")
     * @Template()
     */
    public function indexAction()
    {
        if (!$this->checkAccess("list",null,"ScoringItem")) {
            throw new AccessDeniedException();
        }
        
        $project_capability_array = $this->getUserProjectRoleCapabilities();
        
        if ($this->get('request')->get('btn_batch_action')) {
            return $this->render('NwpAssessmentBundle:Default:batch_confirmation.html.twig', array('entity_path' =>'projectsite_scoringitem', 'request_data' => $this->get('request')->request->all()));   
        }
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

    
        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'project_capability_array' => $project_capability_array,
        );
    }
    
    /**
     * Batch Detete for ScoringItem entities.
     *
     * @Route("/projectsite/scoringitem/batch/action/delete", name="projectsite_scoringitem_batch_action_delete")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    
    public function batchActionDelete(){
        
        $items_array = $this->batchApplicationAction("ScoringItem");
        
        if ($items_array != null) {
            
            //first check access, optimize later so the query doesn't have to be selected again, project ids will be passed through original query
            $Ids = implode(",", $items_array['ids']);
            $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\ScoringItem", "s", null, null, null, null,null, "s.id in (".$Ids.")",'s.id');
            
            //check that user has access to delete all papers selected for batch delete(based on project access)
            $project_ids="";
            foreach ($data as $d) {
                $project_ids .= $d->getProject()->getId().",";
            }
            $project_ids = substr($project_ids, 0, -1); //strip last comma
            
            if (!$this->checkAccess("delete",$project_ids,"ScoringItem")) {
                throw new AccessDeniedException();
            }
            //end of access check
            
            $entities_array=array();
                      
            $entities_array[0]['classname']="Nwp\AssessmentBundle\Entity\ScoringItem";
            $entities_array[0]['alias']="s";
            $entities_array[0]['fieldname']="id";
            $entities_array[0]['ids']=$items_array['ids'];
            $entities_array[0]['exts']=$items_array['exts'];
           
            $this->batchApplicationActionDelete($entities_array,"Papers","Event Papers");
         }
         
        return $this->redirect($this->generateUrl('projectsite_scoringitem'));     
    }
    
    /**
     * Batch Export for ScoringItem entities.
     *
     * @Route("/projectsite/scoringitem/batch/action/export", name="projectsite_scoringitem_batch_action_export")
     * @Method("post")
     * @Template("NwpAssessmentBundle:Default:batch_confirmation.html.twig")
     */
    public function batchActionExport(){
        
        $items_array = $this->batchApplicationAction("ScoringItem");
        
        if ($items_array != null) {
            if ($items_array['entities'] !="") { //we already have the data from filter query on list page
                $data=$items_array['entities'];
            } else {
                $Ids = implode(",", $items_array['ids']); //a subset of the filter query on list page was selected, so we need to requery
                $data= $this->getApplicationValues("Nwp\AssessmentBundle\Entity\ScoringItem", "s", null, null, null, null,null, "s.id in (".$Ids.")",'s.id');
            }
            //check that user has access to export for all papers selected for export (based on project access)
            $project_ids="";
            foreach ($data as $d) {
                $project_ids .= $d->getProject()->getId().",";
            }
            $project_ids = substr($project_ids, 0, -1); //strip last comma
            if (!$this->checkAccess("edit",$project_ids,"ScoringItem")) {
                throw new AccessDeniedException();
            }
            //end of access check 
            $fields=array('id','originalFileName','studentId','gradeLevel','year','administrationTime','project','prompt','organizationType','ncesId','psId',
                            'districtId','ipedsId','organizationName','state','county','classroomId','teacherId');
            $export= $this->batchApplicationActionExport("ScoringItem",$fields,$data);
         }
         
        return $export;     
    }
    
     /**
     * Finds and displays a ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/{id}/download", name="projectsite_scoringitem_download")
     * @Route("/eventsite/scoringitem/{id}/download", name="eventsite_scoringitem_download")
     
     * @Method("get")
     */
 
    public function downloadAction($id)
    {   
        $url =  $this->getRequest()->getPathInfo();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);
       
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItem entity.');
        }
        if (strpos($url, "/eventsite")!== false) { //Event Site
             //is current event set?
            $current_event_id = $this->getCurrentEvent();
       
            if ($current_event_id =="" || !(is_numeric($current_event_id))) {
              return $current_event_id; //redirect to index page
            }
             
            $event_entity = $em->getRepository('NwpAssessmentBundle:EventScoringItem')->findOneBy(array('scoringItem' => $id,'event' => $current_event_id));  
             
            if (!$event_entity) {
                throw $this->createNotFoundException('Unable to find ScoringItem entity for the current event.');
            }
             
         }
         
        if (strpos($url, "/projectsite")!== false) { //Project Site
            if (!$this->checkAccess("download",$entity->getProject()->getId(),"ScoringItem")) {
                throw new AccessDeniedException();
            }
         } elseif (strpos($url, "/eventsite")!== false) { //Event Site
            if (!$this->checkAccess("download",$current_event_id,"ScoringItem")) {
                throw new AccessDeniedException();
            } 
         }
        
        
        $mimeType = $entity->getFileType();
        $folder = $entity->getAbsolutePath();
        $filename=$entity->getOriginalFileName().".".$mimeType;
   
        $downloadFile = $this->downloadFile($folder, $mimeType, $filename);
        if (!$downloadFile) {
            throw $this->createNotFoundException('Unable to find the file.');
        } else {
            return $downloadFile;
        }
        
             
    }
    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        
        $projects = $this->getUserProjects(null, "ScoringItem", "list");
        
        $Ids="";
        foreach($projects as $project) {
            $Ids .= $project->getId().",";
        }
        $Ids = substr($Ids, 0, -1); //strip last comma
        
        $filterForm = $this->createForm(new ScoringItemFilterType($projects));
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('NwpAssessmentBundle:ScoringItem')->createQueryBuilder('e')->where('e.project IN ('.$Ids.')');
       
        // Reset filter
        if ($request->getMethod() == 'POST' && $request->get('filter_action') == 'reset') {
            $session->remove('ScoringItemControllerFilter');
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
                $session->set('ScoringItemControllerFilter', $filterData);
            } else {
                //Filter was probably submitted with empty values, remove session info for the filter, so that the results are not filtered.
                $session->remove('ScoringItemControllerFilter');
            }
            #echo $queryBuilder->getDql();
        } else {
            // Get filter from session
            if ($session->has('ScoringItemControllerFilter')) {
                $filterData = $session->get('ScoringItemControllerFilter');
                //this code fixes "Entities passed to the choice field must be managed" symfony error message  
                foreach ($filterData as $key => $filter) { 
                    if (is_object($filter)) {
                        $filterData[$key] = $em->merge($filter);
                    }
                }
                //
                $filterForm = $this->createForm(new ScoringItemFilterType($projects), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }
    
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
            return $me->generateUrl('projectsite_scoringitem', array('page' => $page));
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
     * Finds and displays a ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/{id}/show", name="projectsite_scoringitem_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItem entity.');
        }
        
        if (!$this->checkAccess("show",$entity->getProject()->getId(),"ScoringItem")) {
            throw new AccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/new", name="projectsite_scoringitem_new")
     * @Template();
     */
    public function newAction()
    {    
        if (!$this->checkAccess("create",null,"ScoringItem")) {
            throw new AccessDeniedException();
        }
        $request = $this->getRequest();
        if ($request->query->has('previous_scoringitem') ) {
            $previous_scoringitem = $request->query->get('previous_scoringitem');
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($previous_scoringitem);

            if ($entity) {//set fields to previous item values, except for fields that should be filled in again
                $entity->setStudentId(null);
                $entity-> setOriginalFileName(null);
                $entity-> setFileId(null);
                $entity-> setFileType(null);
            } else {//set fields to blank
                $entity = new ScoringItem();  
            }
        } else {
            $previous_scoringitem = null;
            $entity = new ScoringItem();
        }
         
        
        $projects = $this->getUserProjects(null, "ScoringItem",  "create");
        $form   = $this->createForm(new ScoringItemType($previous_scoringitem,$projects), $entity);
        

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    

    /**
     * Creates a new ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/create", name="projectsite_scoringitem_create")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ScoringItem:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new ScoringItem();  
        
        $request = $this->getRequest();
     
        if (($request->query->has('previous_scoringitem'))  && ($request->query->get('previous_scoringitem') !="")) {
            $previous_scoringitem = $request->query->get('previous_scoringitem');
            $previous_scoringitem_querystring="?previous_scoringitem=".$previous_scoringitem;
        } else {
            $previous_scoringitem=null;
            $previous_scoringitem_querystring="";
        }
        
        $projects = $this->getUserProjects(null, "ScoringItem", "create");
        
        $form    = $this->createForm(new ScoringItemType($previous_scoringitem,$projects), $entity);    
        $form->bind($request);

        if ($form->isValid()) {
            try {
                $user = $this->container->get('security.context')->getToken()->getUser();
                $entity->setUser($user);
                if ($entity->getFile() !="") { //set dateUploaded only if new file is attached
                    $file_attached=true;
                    $date_uploaded = new \DateTime('now'); 
                    $entity->setDateUploaded($date_uploaded);
                } else {
                    $file_attached = false;
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'flash.create.success');
                
                //update file id once insert id is available
                if ($file_attached != false) { 
                    $id = $entity->getId();
                    $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);
                    $entity->setFileId($id);
                    $em->persist($entity);
                    $em->flush();
                }
                
                if ($this->get('request')->get('btn_create_and_add')) {
                    return $this->redirect($this->generateUrl('projectsite_scoringitem_new')."?previous_scoringitem=".$entity->getId()); 
                } else {
                    return $this->redirect($this->generateUrl('projectsite_scoringitem_edit', array('id' => $entity->getId())).$previous_scoringitem_querystring);
                }
            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'A database error occurred.  Please ensure that Student Id, Administration Time, School Year, Project, and Grade Level are a unique combination. Attached Filename must be unique within Project.');         
            } 
           
            
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.create.error');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Displays fineuploader tool, which allows to upload multiple papers.
     *
     * @Route("/projectsite/scoringitem/new_multiple_papers", name="projectsite_scoringitem_new_multiple_papers_show")
      * @Template("NwpAssessmentBundle:ScoringItem:new_multiple_papers.html.twig")
     */
    public function newMultiplePapersAction()
     {
       return $this->render('NwpAssessmentBundle:ScoringItem:new_multiple_papers.html.twig');
     } 
     
     /**
     * Server script that uploads multiple papers.
     *
     * @Route("/projectsite/scoringitem/new_multiple_papers_upload", name="projectsite_scoringitem_new_multiple_papers_upload")
     * @Template
     */
    public function uploadAction()
     {       
       
        if (!$this->checkAccess("create multiple",null,"ScoringItem")) {
            throw new AccessDeniedException();
        }
  
       
       //$root_folder="/Library/WebServer/Documents/Assessment/app/logs";
       //$logfilename_mainlog	= $root_folder."/scoringitem_upload.log";
       //$logfile_mainlog		= fopen($logfilename_mainlog, "a");
       //fwrite($logfile_mainlog, "in upload action\n");    
        
       $request = $this->getRequest();
       if ($request->request->has('project_id')) {
           $project_id=$request->request->get('project_id');
       } else {
           $project_id=0;
       }  
       
       
       $uploader = new qqFileUploader(); //not able to get filename passed as parameter in setParams from javascript, have to call uploader class
       
       $filename = pathinfo($uploader->getName());
       $base_filename = isset($filename['filename']) ? $filename['filename'] : '';
       
       //fwrite($logfile_mainlog, "project id is ".$project_id."\n");
       //fwrite($logfile_mainlog, "filename is ".$base_filename."\n");
             
       $folder = __DIR__.'/../../../../'.$this->container->getParameter('nwp_assessment.file_uploads').'/papers/';
              
       // list of valid extensions, ex. array("jpeg", "xml", "bmp")
       $uploader->allowedExtensions =array('pdf','txt');
       // Specify max file size in bytes.
       $uploader->sizeLimit = 2 * 1024 * 1024; //2 megabytes per file
          
       if (($project_id !=0) && ($base_filename!="")) {    
            $dbh= $this->get('database_connection');
            //old sql used for Admins
            //$sql = 'SELECT 0 project_id, s.id,concat(s.student_id,"_",s.administration_time_id,"_",s.year_id, "_", s.project_id, "_", s.grade_level_id) as externalId from scoring_item s';
           
            $sql = "SELECT ".$project_id. " project_id, s.id, s.original_file_name externalId from scoring_item s 
                    WHERE s.project_id=".$project_id. " and s.original_file_name='".$base_filename."'";
                  
            $stmt = $dbh->query($sql); 
            $allowed_filenames_array= $stmt->fetchAll(\PDO::FETCH_ASSOC);  
            //fwrite($logfile_mainlog, $sql."\n\n");   
        }
   
        //fwrite($logfile_mainlog, print_r($allowed_filenames_array, true)); 
        
        $uploader->allowedFilenames =$allowed_filenames_array;

        $result = $uploader->handleUpload($folder);
  
        if (isset ($result['success']) && ($result['success']==true)) {
            //update record to include new file info
            $dbh= $this->get('database_connection');
            $date_uploaded= date("Y-m-d H:i:s");
            $sql = "UPDATE scoring_item set file_id=".$result['file_id'].",file_type = '".$result['file_type']."',date_uploaded='".$date_uploaded."' where id =  ".$result['file_id'];
            $stmt = $dbh->query($sql);
        } 
       
        $response = new Response();
        $response->setContent(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
        
        return $response;
     } 
        
    /**
     * Displays a form to upload multiple scoring item entities.
     *
     * @Route("/projectsite/scoringitem/new_multiple", name="projectsite_scoringitem_new_multiple")
     * @Template();
     */
    public function newMultipleAction()
    {   

       if (!$this->checkAccess("create multiple",null,"ScoringItem")) {
            throw new AccessDeniedException();
       }
       
       $role_admin_id=$this->isRoleAdmin();
       $event_capability_array="";
       $events_with_access_size=0;
       $projects_with_access_size=0;
       $project_id=0;
       
       if ($role_admin_id =="") { //Non-admin
           $upload_type="event_paper";
       } else { //Admin
           //get upload type from dropdown box
           $upload_type="paper"; //hardcoded for now
       }
       
       $projects_with_access = $this->getUserProjects(null,$entity="ScoringItem", $action="create multiple");
       if ($projects_with_access !="") {
           $projects_with_access_size = count($projects_with_access);
       }
       
       if ($upload_type =="event_paper") {
           if ($projects_with_access_size==1) {
                $events_with_access = $this->getUserEvents("projectsite",null,"ScoringItem", "create multiple",$projects_with_access[0]->getId());
           } else {
                $events_with_access = $this->getUserEvents("projectsite",null,"ScoringItem", "create multiple");
           }
           if ($events_with_access !="") {
                $events_with_access_size = count($events_with_access); 
           }
       }
       
       //Must have access to at least one project and one event
       if ($upload_type =="event_paper") {
            if (($events_with_access_size < 1) || ($projects_with_access_size < 1)) {
                throw new AccessDeniedException();
            }
       } else { //upload_type is paper
            if ($projects_with_access_size < 1) {
                throw new AccessDeniedException();
            }
       }
            
       //user has access, begin processing the script    
       ini_set('memory_limit', '-1');
       ini_set('max_execution_time', 300);
       
       $today = date ("Y-m-d H:i:s");
       
       $request = $this->getRequest();
           
       $builder = $this->get('form.factory')->createNamedBuilder('csv_upload_form', 'form', null);
        
       if ($upload_type =="event_paper") {  //non-admins upload files differently
            if ($projects_with_access_size >1) {
                $builder->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project','label' =>'Project*','required' => true,
                                            'choices' =>$projects_with_access)) ;
            } elseif  ($projects_with_access_size == 1){
                $project_id=$projects_with_access[0]->getId(); 
            } else {
                $project_id="";
            }     
            
            if ($events_with_access_size >1) {
                $builder->add('event', 'entity', array('class' => 'NwpAssessmentBundle:Event','label' =>'Event*',
                          'required' => true,'choices' =>$events_with_access)) ;
            } elseif  ($events_with_access_size == 1){
                $event_id=$events_with_access[0]->getId(); 
            } else {
                $event_id="";
            }     
        }
       
        $builder->add('csvFile', 'file', array('label' =>'Choose .csv file','required' =>true));
       
        $form1 =$builder ->getForm();
        
        $builder2 = $this->get('form.factory')->createNamedBuilder('file_upload_form', 'form', null);
        
        if ($projects_with_access_size >1) {
            $builder2->add('project', 'entity', array('class' => 'NwpAssessmentBundle:Project','label' =>'Project*',
                           'required' => true,'choices' =>$projects_with_access)) ;
        } elseif  ($projects_with_access_size == 1){
            $project_id=$projects_with_access[0]->getId(); 
        } else {
            $project_id="";
        }     
 
        $form2 =$builder2 ->getForm();

       if ($request->getMethod('post') == 'POST') {
           
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
                    
                    if ($projects_with_access_size >1) {  //(if user has access to more than 1 project, get from form)
                        if (isset ($form1['project'])) {
                            $project_id=$form1['project']->getData()->getId(); //admins should have it set to 0 for access to all projects
                        } 
                    } elseif (($projects_with_access_size ==1)) {
                        $project_id=$projects_with_access[0]->getId(); ; //gets first key in array = project id
                    } else {
                        $project_id="";
                    }

                    move_uploaded_file($file, $file_name); 
                    $handle = fopen($file_name, "r");            
                    $data = array_map("str_getcsv", preg_split('/[\r\n]+/', file_get_contents($file_name)));
                    
                   
                    $row=1;
                   
                    //initialize data                   
                    $prompt_array = $this->getApplicationValues("NwpAssessmentBundle:Prompt", "p", "p.id,p.name,IDENTITY (p.project) project");
                    $year_array = $this->getApplicationValues("NwpAssessmentBundle:Year", "y", "y.id,y.year name");
                    $admin_time_array = $this->getApplicationValues("NwpAssessmentBundle:AdministrationTime", "a", "a.id,a.name");
                    $org_type_array = $this->getApplicationValues("NwpAssessmentBundle:OrganizationType", "o", "o.id,o.name"); 
                    $state_array = $this->getApplicationValues("NwpAssessmentBundle:State", "s", "s.id,s.name");
                    $county_array = $this->getApplicationValues("NwpAssessmentBundle:County", "c", "c.id,c.name,IDENTITY (c.state) state");
                    $project_array = $this->getUserProjects("p.id,p.name", "ScoringItem","create multiple");
                    $grade_level_array = $this->getApplicationValues("NwpAssessmentBundle:GradeLevel", "g", "g.id,g.name");
                    $scoring_item_type_array = $this->getApplicationValues("NwpAssessmentBundle:ScoringItemType", "t", "t.id");
                    $component_array = $this->getApplicationValues("NwpAssessmentBundle:Component", "c", "c.id,c.name");
                    $grouping_array = $this->getApplicationValues("NwpAssessmentBundle:Grouping", "g", "g.id,g.name");
                    
                    
                    if ($request->request->has('action')) {
                        $action = $request->get('action');
                    } else {
                        $action="create";  //default to create, only admins have update multiple records access for now
                    }
                    
                   
                    if ($upload_type =="event_paper") { //Non-Admins have access to these columns in this exact order in .csv
                        
                        $column_array = array( array( 'column_id' => "1",
                            'column_name' => "original_file_name",
                            'column_desc' => "Writing Id",
                            'error_type_check' => "0,2,6",
                            'maxlength' => "100",
                            'table_name' => "temp_table1"
                        ),
                         array( 'column_id' => "2",
                            'column_name' => "student_id",
                            'column_desc' => "Student ID",
                            'error_type_check' => "0,1",
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "3",
                            'column_name' => "grade_level_id",
                            'column_desc' => "Student Grade Level",
                            'error_type_check' => "0,4",
                            'search_array' => $grade_level_array,
                            'search_array_size' => sizeof($grade_level_array),
                            'table_name' => "temp_table1"
                        ),
                         array( 'column_id' => "4",
                            'column_name' => "year_id",
                            'column_desc' => "Writing School Year",
                            'error_type_check' => "0,4",
                            'search_array' => $year_array,
                            'search_array_size' => sizeof($year_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "5",
                            'column_name' => "administration_time_id",
                            'column_desc' => "Writing Order",
                            'error_type_check' => "0,1,4",
                            'search_array' => $admin_time_array,
                            'search_array_size' => sizeof($admin_time_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "6",
                            'column_name' => "prompt_id",
                            'column_desc' => "Prompt",
                            'error_type_check' => "4",
                            'search_array' => $prompt_array,
                            'search_array_size' => sizeof($prompt_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "7",
                            'column_name' => "scoring_item_type_id",
                            'column_desc' => "Double Score Flag",
                            'error_type_check' => "0,1,4",
                            'search_array' => $scoring_item_type_array,
                            'search_array_size' => sizeof($scoring_item_type_array),
                            'table_name' => "temp_table2"
                        ),
                        array( 'column_id' => "8",
                            'column_name' => "component_id",
                            'column_desc' => "Component Flag",
                            'error_type_check' => "0,4",
                            'search_array' => $component_array,
                            'search_array_size' => sizeof($component_array),
                            'table_name' => "temp_table2"
                        ),
                        array( 'column_id' => "9",
                            'column_name' => "grouping_id",
                            'column_desc' => "Group Assignment",
                            'column_type' => "multi",
                            'error_type_check' => "4",
                            'search_array' => $grouping_array,
                            'search_array_size' => sizeof($grouping_array),
                            'table_name' => "temp_table3"
                        ),
                        array( 'column_id' => "10",
                          'column_name' => "date_to_remove",
                          'column_desc' => "System Removal Date",
                          'error_type_check' => "7,8",
                          'table_name' => "temp_table1"
                        ));     
                            
                       
                        
                    } //end of non-admin columns
                    
                    if ($upload_type =="paper") { //Admins have access to these columns in this exact order in .csv
                        $column_array = array( array( 'column_id' => "1",
                            'column_name' => "original_file_name",
                            'column_desc' => "Writing Id",
                            'error_type_check' => "0,2,6",
                            'maxlength' => "100",
                            'table_name' => "temp_table1"
                        ),
                         array( 'column_id' => "2",
                            'column_name' => "student_id",
                            'column_desc' => "Student Id",
                            'error_type_check' => "0,1",
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "3",
                            'column_name' => "grade_level_id",
                            'column_desc' => "Student Grade Level",
                            'error_type_check' => "0,4",
                            'search_array' => $grade_level_array,
                            'search_array_size' => sizeof($grade_level_array),
                            'table_name' => "temp_table1"
                        ),
                         array( 'column_id' => "4",
                            'column_name' => "year_id",
                            'column_desc' => "Writing School Year",
                            'error_type_check' => "0,4",
                            'search_array' => $year_array,
                            'search_array_size' => sizeof($year_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "5",
                            'column_name' => "administration_time_id",
                            'column_desc' => "Writing Order",
                            'error_type_check' => "0,1,4",
                            'search_array' => $admin_time_array,
                            'search_array_size' => sizeof($admin_time_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "6",
                            'column_name' => "project_id",
                            'column_desc' => "Project",
                            'error_type_check' => "0,4",
                            'search_array' => $project_array,
                            'search_array_size' => sizeof($project_array),
                            'table_name' => "temp_table1"
                            ),
                        array( 'column_id' => "7",
                            'column_name' => "prompt_id",
                            'column_desc' => "Prompt",
                            'error_type_check' => "4",
                            'search_array' => $prompt_array,
                            'search_array_size' => sizeof($prompt_array),
                            'table_name' => "temp_table1"
                        ),   
                        array( 'column_id' => "8",
                            'column_name' => "organization_type_id",
                            'column_desc' => "Org Type",
                            'error_type_check' => "4",
                            'search_array' => $org_type_array,
                            'search_array_size' => sizeof($org_type_array),
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "9",
                            'column_name' => "nces_id",
                            'column_desc' => "Nces Id",
                            'error_type_check' => "3",
                            'length' => "12",
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "10",
                            'column_name' => "ps_id",
                            'column_desc' => "Ps Id",
                            'error_type_check' => "3",
                            'length' => "8",
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "11",
                            'column_name' => "district_id",
                            'column_desc' => "District Id",
                            'error_type_check' => "1",
                            'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "12",
                            'column_name' => "ipeds_id",
                            'column_desc' => "Ipeds Id",
                            'error_type_check' => "1",
                            'table_name' => "temp_table1"
                        ), 
                        array( 'column_id' => "13",
                          'column_name' => "organization_name",
                          'column_desc' => "Org Name",
                          'error_type_check' => "2",
                          'maxlength' => "255",
                          'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "14",
                          'column_name' => "state_id",
                          'column_desc' => "State",
                          'error_type_check' => "5,4",
                          'dependent_column_id'=> "14",
                          'search_array' => $state_array,
                          'search_array_size' => sizeof($state_array),
                          'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "15",
                          'column_name' => "county_id",
                          'column_desc' => "County",
                          'error_type_check' => "4",
                          'search_array' => $county_array,
                          'search_array_size' => sizeof($county_array),
                          'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "16",
                          'column_name' => "classroom_id",
                          'column_desc' => "Classrm Id",
                          'error_type_check' => "2",
                          'maxlength' => "15",
                          'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "17",
                          'column_name' => "teacher_id",
                          'column_desc' => "Teacher Id",
                          'error_type_check' => "2",
                          'maxlength' => "15",
                          'table_name' => "temp_table1"
                        ),
                        array( 'column_id' => "18",
                          'column_name' => "date_to_remove",
                          'column_desc' => "System Removal Date",
                          'error_type_check' => "7,8",
                          'table_name' => "temp_table1"
                        ));  
                        
                    } //end of admin columns
                    
                  
                    if ($action=="update") { //special processing for updating records
                      
                      $project_id_array=array(); //get ids of papers they have access to update (based on project access)
                      for($p=0;$p<sizeof($project_array);$p++){ 
                         $project_id_array[] = $project_array[$p]['id'];
                      }
                      
                      $Ids = implode(",",$project_id_array);
                      
                      $paper_array = $this->getApplicationValues("NwpAssessmentBundle:ScoringItem", "s", "s.id",null,null,null,null,"s.project IN (".$Ids.")",null);
                      
                      //now add id column to original column array
                       $column_array_update = array( array( 'column_id' => "0",
                      'column_name' => "id",
                      'column_desc' => "Paper Id",
                      'error_type_check' => "0,1,4",
                      'search_array' => $paper_array,
                      'search_array_size' => sizeof($paper_array),
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
                        ),
                        array( 'error_id' => "5",
                            'error_name' => "Required Dependent",
                            'error_message' => "  can not be empty"
                        ),
                        array( 'error_id' => "6",
                            'error_name' => "Valid String",
                            'error_message' => "  must consist of valid characters"
                        ),
                        array( 'error_id' => "7",
                            'error_name' => "Valid Date",
                            'error_message' => "  must be in YYYY-mm-dd format"
                        ),
                        array( 'error_id' => "8",
                            'error_name' => "Future Date",
                            'error_message' => "  must be greater than today"
                        )
                             
                        );
                     
                        $error_type_array_size=sizeof($error_type_array);
                    
                        $temp_table1="scoring_item";
                        $temp_table2="event_scoring_item";
                        $temp_table3="event_scoring_item_grouping";
                        
                        if ($action=="create") {
                            if ($upload_type =="paper") {
                                $sql = "INSERT INTO ".$temp_table1."(original_file_name, student_id,  administration_time_id,year_id, prompt_id, project_id, grade_level_id, organization_type_id, 
                                        nces_id, ps_id, district_id, ipeds_id, organization_name, state_id, county_id, classroom_id, teacher_id, date_to_remove, user_id) 
                                        VALUES 
                                        (:original_file_name, :student_id, :administration_time_id, :year_id, :prompt_id,  :project_id, :grade_level_id, :organization_type_id, 
                                        :nces_id, :ps_id, :district_id, :ipeds_id, :organization_name, :state_id, :county_id,  :classroom_id, :teacher_id, :date_to_remove, :user_id)"; 
                            } elseif ($upload_type =="event_paper") { //
                               $sql= "INSERT INTO ".$temp_table1."(original_file_name, student_id,  administration_time_id,year_id, prompt_id, project_id, grade_level_id, date_to_remove, user_id) 
                                      VALUES 
                                      (:original_file_name, :student_id, :administration_time_id, :year_id, :prompt_id, $project_id, :grade_level_id,:date_to_remove, :user_id)"; 
                               
                               $em = $this->getDoctrine()->getEntityManager();
                               $status_ready = $em->getRepository('NwpAssessmentBundle:ScoringItemStatus')->findOneBy(array('name' => 'Ready'))->getId();
                        
                               $sql2 = "INSERT INTO ".$temp_table2."(scoring_item_id,event_id,component_id,scoring_item_type_id,is_random,status_id,scoring_round_number,read_number,date_updated) 
                                        VALUES 
                                        (:scoring_item_id,:event_id,:component_id,:scoring_item_type_id,:is_random,:status_id,:scoring_round_number,:read_number,:date_updated)";                  
                                
                            }
                        } elseif ($action=="update") {
                            $sql = "UPDATE ".$temp_table1." SET original_file_name= :original_file_name,student_id = :student_id,administration_time_id=:administration_time_id,year_id=:year_id,prompt_id=:prompt_id,
                                                       project_id=:project_id,grade_level_id=:grade_level_id,organization_type_id=:organization_type_id,nces_id=:nces_id,
                                                       ps_id=:ps_id,district_id=:district_id,ipeds_id = :ipeds_id,organization_name=:organization_name,state_id=:state_id,
                                                       county_id=:county_id,classroom_id=:classroom_id,teacher_id=:teacher_id,date_to_remove=:date_to_remove,user_id=:user_id
                                                       WHERE id = :id";            
                        }
                                
                        try {
                    
                        $dbh= $this->get('database_connection');
                        $dbh->beginTransaction();
                    
                        //get unique combos that can no longer be entered into scoring_item table
                        $sql_unique = 'SELECT s.id, concat(s.student_id,"_",s.administration_time_id,"_",s.year_id, "_", s.project_id, "_", s.grade_level_id) as uniqueId from scoring_item s order by s.id ASC';
                        $stmt_unique = $dbh->query($sql_unique); 
                        $unique_array = $stmt_unique->fetchAll(\PDO::FETCH_ASSOC);
                        $new_unique_array=array();
                        foreach($unique_array as $k=>$v){
                            $new_unique_array[$unique_array[$k]['id']]['uniqueId'] = $unique_array[$k]['uniqueId'];
                        }
                        
                        
                        
                        $unique_found=false;
                        
                        //get unique filename combos that can no longer be entered into scoring_item table
                        $sql_unique_filename = 'SELECT s.id, concat(s.project_id,"_",s.original_file_name) as uniqueFilename from scoring_item s';
                        if (($project_id !=0)&& ($project_id !="")) {
                            $sql_unique_filename .= ' WHERE s.project_id = '.$project_id;
                        }            
                        $sql_unique_filename .= ' ORDER by s.id ASC';
                        $stmt_unique_filename = $dbh->query($sql_unique_filename); 
                        $unique_array_filename = $stmt_unique_filename->fetchAll(\PDO::FETCH_ASSOC);
                        $new_unique_array_filename=array();
                        foreach($unique_array_filename as $k=>$v){
                            $new_unique_array_filename[$unique_array_filename[$k]['id']]['uniqueFilename'] = $unique_array_filename[$k]['uniqueFilename'];
                        }
                        
                        $unique_found_filename=false;
                    
                        //prepare records to insert into scoring_item table
                        $stmt = $dbh->prepare($sql);
                        if (isset ($sql2)) {
                            $stmt2 = $dbh->prepare($sql2);
                        }
                        
                        foreach($data as $value) { //each row
  
                        if (($row>1) && !(($row==sizeof($data)) && (sizeof($value)==1))) {//do not process header row or last row if array size is 1 (windows csv makes extra blank row with one column)
                            $unique_id = "";
                            $unique_filename = "";
                            $paper_id= "";
                            $new_data= "";
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
                                                    $result= $this->errorCheckCsv($check,trim($group),$key,$column_array,$error_type_array,$project_id,$row,null,$data);
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
                                    $paper_id=$val;
                                }
                                                     
                                //build unique id with these variables if we are creating new paper records
                                if ($column_array[$key]["column_name"]=="student_id") {
                                     $student_id = $val;
                                 } elseif ($column_array[$key]["column_name"]=="administration_time_id") {
                                     $administration_time_id = $val;
                                 } elseif ($column_array[$key]["column_name"]=="year_id") {
                                     $year_id=$val;
                                  } elseif ($column_array[$key]["column_name"]=="project_id") {
                                     $project_id=$val;
                                 } elseif ($column_array[$key]["column_name"]=="grade_level_id") {
                                     $grade_level_id=$val;
                                 } elseif ($column_array[$key]["column_name"]=="original_file_name") {
                                     $original_file_name=$val;
                                     $original_file_name_column_id=$column_array[$key]["column_id"];
                                 }
                                
                                //bind values
                                if ($error_msg_array==null) {
                                    if ((($column_array[$key]["column_name"]=="organization_type_id") || ($column_array[$key]["column_name"]=="county_id") ||
                                         ($column_array[$key]["column_name"]=="state_id") || ($column_array[$key]["column_name"]=="prompt_id") ||
                                         ($column_array[$key]["column_name"]=="district_id") || ($column_array[$key]["column_name"]=="ipeds_id") ||                                        
                                         ($column_array[$key]["column_name"]=="date_to_remove")) && ($val=="")){
                                        $val=null;
                                    }
                                    if (($column_array[$key]["column_name"]=="date_to_remove") && ($val!="")) { //convert date to correct date format
                                        $val=date('Y-m-d', strtotime($val));
                                    }
                                    
                                    if ((isset ($column_array[$key]["table_name"])) && ($column_array[$key]["table_name"]=="temp_table1")) {                          
                                        $stmt->bindValue($column_array[$key]["column_name"], $val);//bind value to scoring_item table
                                    } elseif ((isset ($column_array[$key]["table_name"])) && ($column_array[$key]["table_name"]=="temp_table2"))  {  
                                        $stmt2->bindValue($column_array[$key]["column_name"], $val);//bind value to event_scoring_item table
                                    }
                                }
                             } //end of loop that goes through each value in a row
                             
                          $unique_id = $student_id."_".$administration_time_id."_".$year_id."_".$project_id."_".$grade_level_id;
                          $unique_filename=$project_id."_".$original_file_name;
                         
                          if ($action=="create") {
                            foreach($new_unique_array as $new_unique_key=>$new_unique_val){
                                if($new_unique_val['uniqueId']==$unique_id)  {
                                    $error_msg_array[$row]["uniqueId"]=true;
                                    break;  
                                }
                             }
                             
                             foreach($new_unique_array_filename as $new_unique_filename_key=>$new_unique_filename_val){
                                if (($original_file_name!="") &&($new_unique_filename_val['uniqueFilename']==$unique_filename))  {
                                    $error_msg_array[$row][$original_file_name_column_id-1]["uniqueFilename"]=true;
                                    break;  
                                }
                             }
                             
                          } elseif ($action=="update") {
                             
                             foreach($new_unique_array as $new_unique_key=>$new_unique_val){
                                if(($new_unique_val['uniqueId']==$unique_id) && ($new_unique_key !=$paper_id)) {
                                    $error_msg_array[$row]["uniqueId"]=true;
                                    break;  
                                }
                             }
                             
                             foreach($new_unique_array_filename as $new_unique_filename_key=>$new_unique_filename_val){
                                if(($new_unique_filename_val['uniqueFilename']==$unique_filename) && ($new_unique_filename_key !=$paper_id)) {
                                    $error_msg_array[$row][$original_file_name_column_id]["uniqueFilename"]=true;
                                    break;  
                                }
                             }
                          }
                          
                           if ($action=="create") {
                               $new_unique_array[$unique_id]['uniqueId']=$unique_id; //add uniqueid from this row to unique_array
                               $new_unique_array_filename[$unique_filename]['uniqueFilename']=$unique_filename; //add uniqueFilename from this row to unique_filename_array
                           } elseif ($action=="update") { //update uniqueid if it changed for this record
                               if ((isset($new_unique_array[$paper_id]['uniqueId'])) && ($new_unique_array[$paper_id]['uniqueId']!=$unique_id)) {
                                    $new_unique_array[$paper_id]['uniqueId']=$unique_id;
                               }
                               
                               
                               if ((isset($new_unique_array_filename[$paper_id]['uniqueFilename'])) && ($new_unique_array_filename[$paper_id]['uniqueFilename']!=$unique_filename)) {
                                    $new_unique_array_filename[$paper_id]['uniqueFilename']=$unique_filename;
                               }
                            }
                          
                           if ($error_msg_array==null) { //upload to table if there are no errors in .csv file
                                 $stmt->bindValue("user_id", $user_id);//bind user id value for scoring_item table (not entered in .csv file)
                                 $stmt->execute();
                                 if ($upload_type =="event_paper") {  //non-admins only upload to event_scoring_item and event_scoring_item_grouping tables
                                    $scoring_item_id = $dbh->lastInsertId();
                                    if ($events_with_access_size >1) {  //(if user has access to more than 1 event, get from form)
                                        $event_id=$form1['event']->getData()->getId();
                                    }
                                    //bind values for event_scoring_item table that are not entered in .csv
                                    $stmt2->bindValue("event_id", $event_id);//bind value
                                    $stmt2->bindValue("scoring_item_id", $scoring_item_id);//bind value
                                    $stmt2->bindValue("is_random", 0);//bind value
                                    $stmt2->bindValue("status_id", $status_ready);//bind value 
                                    $stmt2->bindValue("scoring_round_number", '1');//bind value
                                    $stmt2->bindValue("read_number", '1');//bind value  
                                    $stmt2->bindValue("date_updated",$today);//bind value
                                    $stmt2->execute();
                                    
                                    
                                    if ($groupings_ids !="") {
                                        $event_scoring_item_id = $dbh->lastInsertId();
                                        $groupings_ids_vals = array_map('trim', explode(',', substr($groupings_ids, 0, -1)));
                                        foreach($groupings_ids_vals as $group_val) {
                                            $sql3 = "INSERT INTO ".$temp_table3."(event_scoring_item_id,grouping_id) 
                                                    VALUES (:event_scoring_item_id,:grouping_id)"; 
                                                    $stmt3 = $dbh->prepare($sql3);
                                                    $stmt3->bindValue("event_scoring_item_id", $event_scoring_item_id);//bind value
                                                    $stmt3->bindValue("grouping_id", $group_val);//bind value
                                                    $stmt3->execute();
                                        }
                                     }
                                  }
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
            return $this->render('NwpAssessmentBundle:ScoringItem:errors_csv_multiple.html.twig', array( "column_array" => $column_array, "error_type_array" => $error_type_array, "error_msg_array" => $error_msg_array));
        } else {
            if ($error_msg!="") {
                $this->get('session')->getFlashBag()->add('error', "The following error occurred: ".$error_msg.". Your file has not been uploaded to the database.");   
            } else {
                $this->get('session')->getFlashBag()->add('success', 'Your .csv file was successfully uploaded to the database.');
            }
        }
            
     }     
 } 
 return $this->render('NwpAssessmentBundle:ScoringItem:new_multiple.html.twig', array( 'project_id' => $project_id, 'upload_type' => $upload_type, 'form1' => $form1->createView(), 'form2' => $form2->createView()));
}
    
    
    /**
     * Displays a form to edit an existing ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/{id}/edit", name="projectsite_scoringitem_edit")
     * @Template()
     */
    public function editAction($id)
    {
       
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItem entity.');
        }
        
        if (!$this->checkAccess("edit",$entity->getProject()->getId(),"ScoringItem")) {
            throw new AccessDeniedException();
        }
         
        $request = $this->getRequest();
        if ($request->query->has('previous_scoringitem') ) {
            $previous_scoringitem = $request->query->get('previous_scoringitem');
        } else {
            $previous_scoringitem=null;
        }
        
        $projects = $this->getUserProjects(null, "ScoringItem", "edit");
        
        $editForm = $this->createForm(new ScoringItemType($previous_scoringitem,$projects), $entity);
        $deleteForm = $this->createDeleteForm($id); 
        
        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/{id}/update", name="projectsite_scoringitem_update")
     * @Method("post")
     * @Template("NwpAssessmentBundle:ScoringItem:edit.html.twig")
     */
    public function updateAction($id)
    {
        $error="";
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ScoringItem entity.');
        }

        $request = $this->getRequest();
        //$allParams = $request->request->all();
        //print_r($allParams);
        if ($request->query->has('previous_scoringitem') ) {
            $previous_scoringitem = $request->query->get('previous_scoringitem');
        } else {
            $previous_scoringitem=null;
        }
         
        $projects = $this->getUserProjects(null, "ScoringItem", "edit");
         
        $editForm   = $this->createForm(new ScoringItemType($previous_scoringitem,$projects), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->bind($request);

        if ($editForm->isValid()) {           
            
            try {
                //workaround for JQuery hidden fields, set previous values in db to null based on org type, as these values are not automatically changed with hidden fields
                if ($entity->getOrganizationType()) {
                if ($entity->getOrganizationType()->getId()==1) {
                    $entity->setPsId(null);
                    $entity->setDistrictId(null);
                    $entity->setIpedsId(null);
                } elseif ($entity->getOrganizationType()->getId()==2) {
                    $entity->setNcesId(null);
                    $entity->setDistrictId(null);
                    $entity->setIpedsId(null);
                } elseif ($entity->getOrganizationType()->getId()==3) {
                    $entity->setNcesId(null);
                    $entity->setPsId(null);
                    $entity->setIpedsId(null);
                } elseif ($entity->getOrganizationType()->getId()==4) {
                    $entity->setNcesId(null);
                    $entity->setPsId(null);
                    $entity->setDistrictId(null);
                } else {
                    $entity->setNcesId(null);
                    $entity->setPsId(null);
                    $entity->setDistrictId(null);
                    $entity->setIpedsId(null);
                }
                } 

                if ($entity->getFile() !="") { //set dateUploaded only if file is attached 
                    $date_uploaded = new \DateTime('now');
                    $original_file_name=pathinfo($entity->getFile()->getClientOriginalName())['filename'];
                    $entity->setOriginalFilename($original_file_name);
                    $entity->setDateUploaded($date_uploaded);
                }
                
                $em->persist($entity);
                $em->flush();

                if ($this->get('request')->get('btn_edit') && ($error==""))  {
                    $this->get('session')->getFlashBag()->add('success', 'flash.update.success');
                } 

                if ($this->get('request')->get('btn_edit_and_create') && ($error=="")) {
                    return $this->redirect($this->generateUrl('projectsite_scoringitem_new')."?previous_scoringitem=".$id); 
                } else {
                    return $this->redirect($this->generateUrl('projectsite_scoringitem_edit', array('id' => $id))); 
                }
                
             } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'A database error occurred.  Please ensure that Student Id, Administration Time, School Year, Project, and Grade Level are a unique combination. Attached Filename must be unique within Project.');  
                if ($entity->getFile() !="") { //clear out file values if file was attached
                    $entity->setOriginalFilename(null);
                    $entity->setDateUploaded(null);
                }
             } 

             
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ScoringItem entity.
     *
     * @Route("/projectsite/scoringitem/{id}/delete", name="projectsite_scoringitem_delete")
     */
    public function deleteAction($id)
    {
        if ($this->get('request')->getMethod()=="POST") {
            $form = $this->createDeleteForm($id);
            $request = $this->getRequest();
            $form->bind($request);
        } else { //get method
            $id = $this->get('request')->get('id');
        }
           

        if ((($this->get('request')->getMethod()=="POST") && ($form->isValid())) || (($this->get('request')->getMethod()=="GET") && ($id !=''))) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NwpAssessmentBundle:ScoringItem')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ScoringItem entity.');
            }
            
            if (!$this->checkAccess("delete",$entity->getProject()->getId(),"ScoringItem")) {
                throw new AccessDeniedException();
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('projectsite_scoringitem'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
 * @Route("/projectsite/scoringitem/countyByStateId", name="_countyByStateId")
 */
public function  getCountyByStateId()        
{  
    $this->em = $this->get('doctrine')->getEntityManager();
    $this->repository = $this->em->getRepository('NwpAssessmentBundle:County');
 
    $stateId = $this->get('request')->query->get('data');

    $counties = $this->repository->findByState($stateId);
 
    $html = '<option value=""></option>';
    foreach($counties as $county)
    {
        $html = $html . sprintf("<option value=\"%d\">%s</option>",$county->getId(), $county->getName());
    }
 
    return new Response($html);
}

    /**
 * @Route("/projectsite/scoringitem/promptByProjectId", name="_promptByProjectId")
 */
public function  getPromptByProjectId()        
{  
    $this->em = $this->get('doctrine')->getEntityManager();
    $this->repository = $this->em->getRepository('NwpAssessmentBundle:Prompt');
 
    $projectId = $this->get('request')->query->get('data_project');

    $prompts = $this->repository->findByProject($projectId);
 
    $html = '<option value=""></option>';
    foreach($prompts as $prompt)
    {
        $html = $html . sprintf("<option value=\"%d\">%s</option>",$prompt->getId(), $prompt->getName());
    }
 
    return new Response($html);
}
}
