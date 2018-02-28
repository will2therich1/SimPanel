<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 18/02/18
 * Time: 09:26
 */

namespace UserBundle\Service;


use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\Action;

class ActionsService
{
    /**
     * @var ObjectManager
     */
    private $em;


    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

    }

    /**
     * Gets the recent actions for a user
     */
    public function getRecentActionsForAUser($ownerId , $offset , $limit)
    {
        $queryBuilder = $this->em->createQueryBuilder();

        // Create the queries!
        $queryBuilder->select('a')
            ->from('UserBundle:Action', 'a')
            ->where('a.ownerId = :id')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->setParameter('id', $ownerId);


        $result = $queryBuilder->getQuery()->execute();


        return $result;


    }

    /**
     * Creates a new action
     *
     */
    public function createANewAction($actionText , $userCreatedName , $forName , $forId)
    {

        $action = new Action();
        $action->setAction($actionText);
        $action->setUser($userCreatedName);
        $action->setOwner($forName);
        $action->setOwnerId($forId);

        $this->em->persist($action);

        try {
            $this->em->flush();

            return true;
        }catch (\Exception $e){
            return false;
        }


    }



    /**
     * Deletes a action
     */
    public function deleteAAction($actionId)
    {
        $action = $this->em->getRepository('UserBundle:Action')->find($actionId);
        $this->em->remove($action);

        try {
            $this->em->flush();
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

}