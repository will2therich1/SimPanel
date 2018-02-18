<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 25/01/18
 * Time: 20:56
 */

namespace AppBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use AppBundle\Entity\Settings;

class SettingService
{
    /**
     * @var ObjectManager
     */
    private $em;

    public $version = '0.50';

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets the site information and returns this.
     * CALLED ON EVERY PAGE
     *
     * @return array
     */
    public function getSiteInformation()
    {

        $returnArray = [];
        $returnArray['panelName'] = $this->getSetting('PanelName')->getSettingValue();
        $returnArray['panelNamePart1'] = $this->getSetting('PanelNamePart1')->getSettingValue();
        $returnArray['PanelNamePart2'] = $this->getSetting('PanelNamePart2')->getSettingValue();
        $returnArray['PanelNameShortPart1'] = $this->getSetting('PanelNameShortPart1')->getSettingValue();
        $returnArray['PanelNameShortPart2'] = $this->getSetting('PanelNameShortPart2')->getSettingValue();
        $returnArray['termsandconditions'] = $this->getSetting('TermsAndConditions')->getSettingValue();
        $returnArray['version'] = $this->version;

        return $returnArray;
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
            ->setParameter('setting', $settingName)
            ->getQuery()
            ->execute();

        if (empty($result)) {
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

    /**
     * Sets the value of a setting object
     *
     * @param $settingName
     * @param $settingValue
     *
     */
    public function setSetting($settingName, $settingValue)
    {
        $setting = $this->getSetting($settingName);

        $setting->setSettingValue($settingValue);
        $this->em->persist($setting);
        $this->em->flush();

        return;

    }

}