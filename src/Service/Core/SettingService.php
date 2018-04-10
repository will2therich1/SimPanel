<?php
/**
 * The Service for setting & getting settings.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Service\Core;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

class SettingService
{
    /**
     * @var EntityManagerInterface - Doctrine Interface
     */
    private $em;

    const APP_VERSION = '0.60';

    /**
     * SettingService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Gets a setting.
     *
     * @param $name  string - Name of the setting
     * @param $default   string - The setting default to be created if it dosen't exist
     *
     * @return Setting - Returns the Setting object;
     */
    public function getSetting($settingName, $default = 0)
    {
        // Get the settings repo
        $settings = $this->em->getRepository('App:Setting');

        // Create a query builder
        $query = $settings->createQueryBuilder('s');

        // Query for the requested setting
        $result = $query->select('s.id')
          ->where('s.settingName = :setting')
          ->setParameter('setting', $settingName)
          ->getQuery()
          ->execute();

        // If no results create a new setting
        if (empty($result)) {
            $newSetting = new Setting();

            $newSetting->setSettingName($settingName);
            $newSetting->setSettingValue($default);
            $newSetting->setSettingUpdatedTime(new \DateTime());
            $this->em->persist($newSetting);
            $this->em->flush();

            return $newSetting;
        }

        // Else find the setting requested
        $result = $result[0];
        $id = $result['id'];
        // Get the setting object
        $returnObject = $this->em->getRepository('App:Setting')->find($id);
        // Return the setting object
        return $returnObject;
    }

    /**
     * Sets a setting.
     *
     * @param $settingName - Name of the setting to set
     * @param $settingValue - Value of the setting
     *
     * @throws \Exception - When a error occours setting the setting
     *
     * @return bool - true if the update works correctly
     */
    public function setSetting($settingName, $settingValue)
    {
        // Get the setting using the get setting function
        $setting = $this->getSetting($settingName, $settingValue);
        // Set the settings value
        $setting->setSettingValue($settingValue);
        // Persist the setting
        $this->em->persist($setting);

        // Try to update the DB
        try {
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            return new \Exception('An Error occoured updated the setting with message '.$e->getMessage());
        }
    }

    /**
     * Queries the database to get the branding options.
     * This is called on every page load so optimisation will never go a miss.
     *
     * @return array
     */
    public function getSiteInformation()
    {
        $returnArray = [];
        $returnArray['panelName'] = $this->getSetting('PanelName', 'SimPanel')->getSettingValue();
        $returnArray['panelNamePart1'] = $this->getSetting('PanelNamePart1', 'Sim')->getSettingValue();
        $returnArray['PanelNamePart2'] = $this->getSetting('PanelNamePart2', 'Panel')->getSettingValue();
        $returnArray['PanelNameShortPart1'] = $this->getSetting('PanelNameShortPart1', 'S')->getSettingValue();
        $returnArray['PanelNameShortPart2'] = $this->getSetting('PanelNameShortPart2', 'P')->getSettingValue();
        $returnArray['termsandconditions'] = $this->getSetting('TermsAndConditions', 'To Be filled in by the panel admins')->getSettingValue();
        $returnArray['version'] = SettingService::APP_VERSION;

        return $returnArray;
    }
}
