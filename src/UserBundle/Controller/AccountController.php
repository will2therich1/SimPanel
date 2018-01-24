<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;

class AccountController extends Controller
{
    /**
     * @Route("/user/settings", name="userSettingsMain")
     */
    public function userIndexAction(Request $request)
    {

        $user = $this->getUser();

        $data = [];
        $data['active'] = "Dash";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $this->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.main.html.twig' , $data);
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
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings');
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

            $this->getDoctrine()->getManager()->persist($newSetting);
            $this->getDoctrine()->getManager()->flush();

            return $newSetting;
        }

        $result = $result[0];
        $id = $result['id'];


        $returnObject = $this->getDoctrine()->getRepository('AppBundle:Settings')->find($id);

        return $returnObject;
    }

    public function setSetting($settingName, $settingValue)
    {
        $setting = $this->getSetting($settingName);

        $setting->setSettingValue($settingValue);
        $em = $this->getDoctrine()->getManager();
        $em->persist($setting);
        $em->flush();

        return;

    }

    public function getSiteInformation()
    {

        $returnArray = [];
        $returnArray['termsandconditions'] = $this->getSetting('TermsAndConditions')->getSettingValue();


        return $returnArray;
    }

}
