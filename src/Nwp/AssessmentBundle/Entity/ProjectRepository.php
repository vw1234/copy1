<?php

namespace Nwp\AssessmentBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository
{
    public function findUserProjects($user,$request=null, $classSession=null)
    {
        echo "in findUserProjects function";
        $values="";
        $em = $this->getEntityManager();
        
        if (($classSession != "") && ($classSession !=null)) {
            $session = $request->getSession();
            if ($session->has($classSession)) {
                $values = $session->get($classSession);
               //this code fixes "Entities passed to the choice field must be managed" symfony error message  
               foreach ($values as $key => $value) { 
                    if (is_object($value)) {
                        $values[$key] = $em->merge($value);
                    }
                }
               //echo "got values from session: ".$classSession."</br>";
            }
        }      
        if ($values == "") {
            $query = $em->createQuery('SELECT p FROM NwpAssessmentBundle:Project p LEFT JOIN p.pu pu WHERE pu.user='.$user.' ORDER BY p.name ASC');
            $values=  $query->getResult();
            if ($classSession != "") {
               $session->set($classSession, $values);
                //echo "created session ".$classSession."</br>";    
            }   
        }
        return $values;
    }
    
    //public function findUserProjects($user)
   // {
    //    return $this->getEntityManager()
    //        ->createQuery('SELECT p FROM NwpAssessmentBundle:Project p LEFT JOIN p.pu pu WHERE pu.user='.$user.
    //                      ' ORDER BY p.name ASC')
    //        ->getResult();
   // }
}