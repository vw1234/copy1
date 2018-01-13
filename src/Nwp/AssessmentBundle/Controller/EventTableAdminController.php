<?php

namespace Nwp\AssessmentBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
 
class EventTableAdminController extends Controller
{
    
 
    
 //override Sonata Admin CRUD editAction
 //public function editAction($id = null)
 //   {
//        // the key used to lookup the template
//        $templateKey = 'edit';
//
//        $em = $this->getDoctrine()->getEntityManager();
//        $id = $this->get('request')->get($this->admin->getIdParameter());

//        // $object = $this->admin->getObject($id);
//        // My custom method to limit which records are shown for sonata_type_collection
//        $object = $em->getRepository('NwpAssessmentBundle:EventUser')->findOneBy(array('id' => $id));
        
        
//        if (!$object)
//        {
//            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
//        }
//
//        if (false === $this->admin->isGranted('EDIT', $object))
//        {
//            throw new AccessDeniedException();
//        }
//
//        $this->admin->setSubject($object);
//
//        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
//        $form->setData($object);
//
//        // Trick is here ###############################################
//        // Method to find rooms for this event
//        // And set the data in form
//        $queryBuilder =$em->getRepository('NwpAssessmentBundle:Role')
//                          ->createQueryBuilder('r')
//                          ->select('r')
//                          ->where("r.name='Scorer 1'")
//                          ->OrWhere("r.name='Scorer 2'")
//                   ;   
//        $query = $queryBuilder->getQuery();
//        $eu= $query->getResult();
//        
//        $role_ids="";
//        foreach ($eu as $e) {
//            $role_ids .= $e->getId().",";
//        }
//        $role_ids = substr($role_ids, 0, -1); //strip last comma
//        
//        $event_id=$object->getEvent();
//        $room_id = $object->getGradeLevel();
//        $table_id = $object->getTableId();
//        $query = $em->createQuery("SELECT eu FROM NwpAssessmentBundle:EventUser eu where eu.event = ".$event_id->getId()." and eu.tableId =".$table_id." and eu.gradeLevel = ".$room_id->getId()." and eu.role IN (".$role_ids.")");
//        
//        $eu=$query->getResult();      
//        $form['eu']->setData($eu);
//      
//  
//        // #############################################################

//        if ($this->get('request')->getMethod() == 'POST')
//        {
//            $form->bindRequest($this->get('request'));
//            
//            $isFormValid = $form->isValid();
//
//            // persist if the form was valid and if in preview mode the preview was approved
//            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved()))
//            {
//                try {
//                    //$this->admin->update($object); //out of the box way of updating object
//                    $db = $em->getConnection();
//                    $db->beginTransaction();
//
//                    $request = $this->getRequest();
//                   
//                    $formData = $form['eu']->getData();
//                    $count=0;
//                    $count_delete=0;
//                    $Ids="";
//
//                    foreach ($formData as $eu) {    
//                        if ($request->get('delete_checkbox_'.$count) && ($eu->getUser() !=null)) {
//                            $checkbox_value = $request->get('delete_checkbox_'.$count);
//                            if ($checkbox_value==1) {
//                                //echo "checkbox is checked for id ".$eu->getId();
//                                //build delete ids to delete from event_user table
//                                if ($eu->getId()) {
//                                    $Ids .= $eu->getId().",";
//                                }
//                                $count_delete++;
//                            }
//                        } else {  
//                            //select the record from db, if exists update, otherwise insert new record
//                            $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $eu->getId()));
//                            if ($eu->getTarget()!="") {
//                               $target=$eu->getTarget();
//                            } else {
//                                $target="NULL";
//                            }
//                            if ($eu_record) {  
//                                $sql = "UPDATE event_user eu set eu.role_id=".$eu->getRole()->getId().",eu.user_id = ".$eu->getUser()->getId().",eu.target = ".$target." WHERE eu.id=".$eu->getId();
//                                $stmt = $db->prepare($sql); 
//                                $stmt->execute();
//                            } else {
//                                $sql = "INSERT INTO event_user(event_id,grade_level_id,table_id,user_id,role_id,target) values (".$event_id->getId().",".$room_id->getId().",".$table_id.",".$eu->getUser()->getId().",".$eu->getRole()->getId().",".$target.")";
//                                $stmt = $db->prepare($sql); 
//                                $stmt->execute();
//                            } 
//                        }
//                        $count++;
//                    }
//
//                    $Ids = substr($Ids, 0, -1); //strip last comma
//
//                    if ($Ids !="") {
//                        $sql = "DELETE FROM event_user WHERE id IN (".$Ids.")";   
//                        $stmt = $db->prepare($sql);
//                        $stmt->execute();
//                    }
//
//                    //Update Table Leader
//                    $sql = "UPDATE event_user eu set eu.user_id = ".$object->getUser()->getId()." WHERE eu.id=".$id;
//                    $stmt = $db->prepare($sql);
//                    $stmt->execute();
//
//                    if (($count-$count_delete)>8) {
//                        $db->rollback();
//                        $this->get('session')->setFlash('sonata_flash_error', 'Item has not been updated.  Maximum of 8 Scorers are Allowed per Table.');
//                    } else {
//                        $db->commit();
//                        $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');
//                        
//                         if ($this->isXmlHttpRequest())
//                        {
//                            return $this->renderJson(array(
//                                'result'    => 'ok',
//                                'objectId'  => $this->admin->getNormalizedIdentifier($object)
//                            ));
//                        }
//                        // redirect to edit mode
//                        return $this->redirectTo($object); 
//                    }    
//                    
//               } catch (Exception $e){
//                    $db->rollback();
//                    $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
//               }  
//            }
//
//            // show an error message if the form failed validation
//            if (!$isFormValid)
//            {
//                $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
//            }
//            elseif ($this->isPreviewRequested())
//            {
//                // enable the preview template if the form was valid and preview was requested
//                $templateKey = 'preview';
//            }
//        }
//
//        $view = $form->createView();
//
//        // set the theme for the current Admin Form
//        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
//
//        return $this->render($this->admin->getTemplate($templateKey), array(
//            'action' => 'edit',
//            'form'   => $view,
//            'object' => $object,
//        ));
//    }
    
    public function redirectTo($object)
    {
        $url = false;
        
        if ($this->get('request')->get('btn_update_and_list')) {
          $url = $this->generateUrl("nwp.assessment.admin.eventtable_list", array("filter[event][type]" => 1,"filter[event][value]" => $object->getEvent()->getId(),"filter[gradeLevel][type]" => 1,"filter[gradeLevel][value]" => $object->getGradeLevel()->getId()));     
        }

        if ($this->get('request')->get('btn_create_and_create')) {
            $params = array();
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $this->get('request')->get('subclass');
            }
            $url = $this->admin->generateUrl('create', $params);
        }

        if (!$url) {
            $url = $this->admin->generateObjectUrl('edit', $object);
        }

        return new RedirectResponse($url);
    }
    
    //override Sonata Admin CRUD deleteAction
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = $this->get('request')->get($this->admin->getIdParameter());
        
        $object = $em->getRepository('NwpAssessmentBundle:EventUser')->findOneBy(array('id' => $id));
       
        $room_id = $object->getGradeLevel();
        $event_id = $object->getEvent();
        $table_id = $object->getTableId();
        
        $query = $em->createQuery("SELECT eu FROM NwpAssessmentBundle:EventUser eu where eu.event = ".$event_id->getId()." and eu.gradeLevel = ".$room_id->getId()." and eu.tableId = ".$table_id);
        
        $object=$query->getResult();
        
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE')) {        
          throw new AccessDeniedException();
       }
        
        
       // if ($this->getRequest()->getMethod() == 'DELETE') {
         
            try {
                //Trick is here
                //Only delete where table id is the table being deleted for this event and this room
                foreach($object as $ob){   
                    $this->admin->delete($ob);     
                }
                
                //$this->admin->delete($object);
                $this->addFlash('sonata_flash_success', 'flash_delete_success');
            } catch (ModelManagerException $e) {
               $this->addFlash('sonata_flash_error', 'flash_delete_error');
            }

            return new RedirectResponse($this->admin->generateUrl('list'));
       // }
       

        return $this->render($this->admin->getTemplate('delete'), array(
            'object' => $object,
            'action' => 'delete'
        ));
    }
    
      public function batchActionDelete(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }
    
        try {
            
            $request = $this->getRequest();
            $allParams = $request->request->all();
            
            $tableIds = "";
            $event_id = "";
            $room_id = "";
            $table_id = "";
            $em = $this->getDoctrine()->getEntityManager();
       
            foreach ($allParams['idx'] as $item_checkbox){
                $object_find = $em->getRepository('NwpAssessmentBundle:EventUser')->findOneBy(array('id' => $item_checkbox));
                if ($object_find) {
                     if ($object_find->getEvent()) {
                        $event_id = $object_find->getEvent()->getId();
                     }
                     if ($object_find->getGradeLevel()) {
                          $room_id = $object_find->getGradeLevel()->getId();
                     }
                     if ($object_find->getTableId()) {
                          $table_id = $object_find->getTableId();
                     }
                     if ($event_id && $room_id && $table_id) {
                        $objects_delete = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('event' => $event_id,'gradeLevel' => $room_id,'tableId' => $table_id ));
                    
                        foreach ($objects_delete as $object_delete) {
                            if ($object_delete->getId()) {
                                $tableIds .= $object_delete->getId().",";
                            }
                        }
                        
                     } else {
                         $tableIds =$object_find->getId();  //either event id, grade level id, or table id was missing, delete just one record with this id
                     }        
                }
            }
           $tableIds = substr($tableIds, 0, -1); //strip last comma
           // echo "tableIds are ".$tableIds;
           
            $sql = "DELETE FROM Nwp\AssessmentBundle\Entity\EventUser eu WHERE eu.id in (".$tableIds.")"; 
                     $query = $this->get('doctrine')->getEntityManager()->createQuery($sql);
                     $query->execute();
                   
            $this->addFlash('sonata_flash_success', 'flash_batch_delete_success');
        } catch ( ModelManagerException $e ) {
            $this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
        }
 
         return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }
}
