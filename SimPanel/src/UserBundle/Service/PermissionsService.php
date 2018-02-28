<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 02/02/18
 * Time: 22:56
 */

namespace UserBundle\Service;


use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class PermissionsService
{

    /**
     * @var User
     */
    private $user;

    /**
     * @var ObjectManager
     */
    private $em;



    public function __construct(User $user, ObjectManager $em)
    {
        $this->user = $user;
        $this->em = $em;
    }

    public function checkPermission($action)
    {
        $userPermissions = $this->user->getSubUserPermissions();
        $userPermissionsArray = array($userPermissions);


        if ($userPermissionsArray[0][$action] == 1) {
            return true;
        }else{
            return false;
        }


    }

    public function getSubUserOwner()
    {

        $subUserOwner = $this->em->getRepository('AppBundle:User')->find($this->user->getSubUserFor());

        return $subUserOwner;

    }

}