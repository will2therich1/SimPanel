<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 20/12/17
 * Time: 11:30
 */

namespace AppBundle\Service;


use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use AppBundle\Entity\Settings;

class MaintenanceListener
{

    protected $em;

    public function __construct(ObjectManager $entityManager )
    {
        $this->em = $entityManager;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
    $maintenanceModeSetting = $this->getSetting("Maintenance");
    $value = $maintenanceModeSetting->getSettingValue();
    $adminArea = strpos($event->getRequest()->getRequestUri() , 'admin');

    if ($event->getRequest()->getRequestUri() == '/Maintenance') {
        if ($value == 0)
        {
        return new RedirectResponse('/login_check');
        }
    }


    // Allow Access to certain areas!
    if ($event->getRequest()->getRequestUri() == '/Maintenance') return;
    if ($event->getRequest()->getRequestUri() == '/login') return;
    if ($event->getRequest()->getRequestUri() == '/login_check') return;
    if ($event->getRequest()->getRequestUri() == '/logout') return;
    if ($adminArea == true) return;

    // Check they are not already on the Maintenance mode page!
    if ($event->getRequest()->getRequestUri() !== '/Maintenance') {
        if ($value == 1) {
            $event->setResponse(new RedirectResponse('/Maintenance'));
        }
    }



    }

    /**
     * Returns the Setting Object!
     *
     * If the setting dosen't exist then it will be created.
     *
     * @param $settingName
     *          Name of the Setting
     * @return Settings|mixed
     */
    public function getSetting($settingName)
    {
        $settings = $this->em->getRepository('AppBundle:Settings');
        $query = $settings->createQueryBuilder('s');
        $result = $query->select('s.id')
            ->where('s.settingName = :setting')
            ->setParameter('setting' , $settingName)
            ->getQuery()
            ->execute();

        if (empty($result))
        {
            $newSetting = new Settings();
            $newSetting->setSettingName($settingName);
            $newSetting->setSettingValue(0);
            $newSetting->setSettingUpdatedTime(new \DateTime());

            $this->em->persist($newSetting);
            $this->em->flush();

            return $newSetting;
        }

        $result = $result[0];
        $id = $result['id'];

        $returnObject = $this->em->getRepository('AppBundle:Settings')->find($id);


        return $returnObject;
    }

}