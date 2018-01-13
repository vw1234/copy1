<?php

namespace Nwp\AssessmentBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Nwp\AssessmentBundle\Entity\EventUser;
use Doctrine\DBAL\Connection;
 
class EventAdminController extends Controller
{
    #code modified from: example at http://stackoverflow.com/questions/13253342/sonata-admin-form-collection
    
#    public function editAction($id = null)
#    {
#        // the key used to lookup the template
#        $templateKey = 'edit';
#
#        $em = $this->getDoctrine()->getEntityManager();
#        $id = $this->get('request')->get($this->admin->getIdParameter());
#
#        // $object = $this->admin->getObject($id);
#        // My custom method to limit which records are shown for sonata_type_collection
#        $object = $em->getRepository('NwpAssessmentBundle:Event')->findOneBy(array('id' => $id));
#
#        if (!$object)
#        {
#            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
#        }
#
#        if (false === $this->admin->isGranted('EDIT', $object))
#        {
#            throw new AccessDeniedException();
#        }
#
#        $this->admin->setSubject($object);
#
#        /** @var $form \Symfony\Component\Form\Form */
#        $form = $this->admin->getForm();
#        $form->setData($object);
#
#        // Trick is here ###############################################
#        // Method to find rooms for this event
#        // And set the data in form
#        $role = $em->getRepository('NwpAssessmentBundle:Role')->findOneBy(array('name' => 'Room Leader' ));
#        //$eu = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('tableId' => null,'event' => $id,'role' =>$role->getId() ));
#        
#      
#        $queryBuilder = $em->getRepository('NwpAssessmentBundle:EventUser')
#                            ->createQueryBuilder('eu')
#                            ->select('eu')
#                           # ->join('eu.user', 'u')
#                            ->Where('eu.event='.$id)
#                            ->andWhere('eu.tableId is null')
#                           ->andWhere('eu.role='.$role->getId());
#        
#      
#                            
#        $query = $queryBuilder->getQuery();
#        $eu= $query->getResult();
#        
#        $form['eu']->setData($eu);
#        // #############################################################
#
#        if ($this->get('request')->getMethod() == 'POST')
#        {        
#            $form->bindRequest($this->get('request'));
#            $isFormValid = $form->isValid();
#
#            // persist if the form was valid and if in preview mode the preview was approved
#            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved()))
#            {
#                //Since user event collection is mapped to event, symfony code automatically deleted tables when rooms were
#                //modified or added.  So, wrote custom code that inserts/updates/deletes event_user table without dependence on event object
#                
#                try {
#                    //$this->admin->update($object); //out of the box way of updating object
#                    $db = $em->getConnection();
#                    $db->beginTransaction();
#                    
#                    $request = $this->getRequest();
#                    
#                    $formData = $form['eu']->getData();
#                    $count=0;
#                    $roomIds="";
#                    $rooms_to_delete=""; //if all Room Leaders for a room are deleted, entire Room should be deleted to prevent orphans
#                    
#                    foreach ($formData as $eu) { 
#
#                        if ($request->get('delete_checkbox_'.$count) && ($eu->getGradeLevel() !=null)) {
#                            $checkbox_value = $request->get('delete_checkbox_'.$count);
#                            if ($checkbox_value==1) {
#                                //build delete query of room ids to delete from event_user table
#                                if ($eu->getId()) {
#                                    $roomIds .= $eu->getId().",";
#                                    $rooms_to_delete .= $eu->getGradeLevel()->getId().",";
#                                }
#                            }
#                        } else {                   
#                            //select the record from db, if exists update, otherwise insert new record
#                            $eu_record = $em->getRepository('NwpAssessmentBundle:EventUser')->findBy(array('id' => $eu->getId()));
#                            if ($eu->getTarget()!="") {
#                               $target=$eu->getTarget();
#                            } else {
#                                $target="NULL";
#                            }
#                            
#                            if ($eu_record) {
#                                $sql = "UPDATE event_user eu set eu.grade_level_id=".$eu->getGradeLevel()->getId().",eu.user_id = ".$eu->getUser()->getId().",eu.target = ".$target." WHERE eu.id=".$eu->getId();
#                                $stmt = $db->prepare($sql); 
#                                $stmt->execute();           
#                            } else {
#                                $sql = "INSERT INTO event_user(event_id,grade_level_id,table_id,user_id,role_id,target) values (".$id.",".$eu->getGradeLevel()->getId().",null,".$eu->getUser()->getId().",".$role->getId().",".$target.")";
#                                $stmt = $db->prepare($sql); 
#                                $stmt->execute();                  
#                            }        
#                        }
#
#                        $count++;
#                    }
#                    
#                    
#
#                    $roomIds = substr($roomIds, 0, -1); //strip last comma
#
#                    if ($roomIds !="") {
#                        $sql = "DELETE FROM event_user WHERE id IN (".$roomIds.")";   
#                        $stmt = $db->prepare($sql);
#                        $stmt->execute();
#                    }
#
#                    //if All Room Leaders are deleted for a particular Room, entire Room should be deleted to prevent orphan records
#                    if ($rooms_to_delete !="") {
#                        $rooms_to_delete = substr($rooms_to_delete, 0, -1); //strip last comma
#                        $rooms_to_delete_array =  explode(",",$rooms_to_delete);
#                        foreach ($rooms_to_delete_array as $ra) {
#                            $sql="select * from event_user where event_id = ".$id." and grade_level_id = ".$ra." and role_id = ".$role->getId();
#                            $stmt = $db->query($sql); 
#                            $room_leaders_left = $stmt->fetchAll(\PDO::FETCH_ASSOC);
#                            if (count($room_leaders_left)==0) {
#                                $sql = "DELETE FROM event_user WHERE event_id = ".$id." and grade_level_id = ".$ra;   
#                                $stmt = $db->prepare($sql);
#                                $stmt->execute();
#                            }
#                        }
#                    }
#                    $sql="UPDATE event SET name = :name,start_date=:start_date,end_date=:end_date,location=:location,
#                          description=:description,announcements=:announcements,event_type_id=:eventType,
#                          scoring_rubric_id=:scoringRubric,adjudication_trigger=:adjudicationTrigger,
#                          second_scoring_table_trigger=:secondScoringTableTrigger
#                          where id=:id";
#                    $stmt = $db->prepare($sql);
#                    $stmt->bindValue(':name',$object->getName());
#                    $stmt->bindValue(':start_date',$object->getStartDate()->format('Y-m-d H:i'));
#                    $stmt->bindValue(':end_date',$object->getEndDate()->format('Y-m-d H:i'));
#                    $stmt->bindValue(':location',$object->getLocation());
#                    $stmt->bindValue(':description',$object->getDescription());
#                    $stmt->bindValue(':announcements',$object->getAnnouncements());
#                    $stmt->bindValue(':eventType',$object->getEventType()->getId());
#                    $stmt->bindValue(':scoringRubric',$object->getScoringRubric()->getId());
#                    $stmt->bindValue(':adjudicationTrigger',$object->getAdjudicationTrigger());
#                    $stmt->bindValue(':secondScoringTableTrigger',$object->getSecondScoringTableTrigger());
#                    $stmt->bindValue(':id',$id);
#
#                    $stmt->execute();
#                    $db->commit();
#                    $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');
#
#                    if ($this->isXmlHttpRequest())
#                    {
#                        return $this->renderJson(array(
#                            'result'    => 'ok',
#                            'objectId'  => $this->admin->getNormalizedIdentifier($object)
#                        ));
#                    }
#                    // redirect to edit mode
#                    return $this->redirectTo($object);
#                    
#               } catch (Exception $e){
#                    $db->rollback();
#                    $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
#               } 
#            }
#
#            // show an error message if the form failed validation
#            if (!$isFormValid)
#            {
#                $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
#            }
#            elseif ($this->isPreviewRequested())
#            {
#                // enable the preview template if the form was valid and preview was requested
#                $templateKey = 'preview';
#            }
#        }
#
#        $view = $form->createView();
#
#        // set the theme for the current Admin Form
#        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
#
#        return $this->render($this->admin->getTemplate($templateKey), array(
#            'action' => 'edit',
#            'form'   => $view,
#            'object' => $object,
#        ));
#    }
    
    
     public function redirectTo($object)
    {
        $url = false;

        if ($this->get('request')->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list');
        }
        
        if ($this->get('request')->get('btn_update_and_list')) {
          $url = $this->generateUrl("nwp.assessment.admin.eventroom_list", array("filter[event][type]" => 1,"filter[event][value]" => $object->getEvent()->getId()));     
            
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
 
}
