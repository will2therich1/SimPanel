<?php
/**
 * The data array compiler used by controllers.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */


namespace App\Service\Core;


use App\Entity\Setting;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataCompiler
{
    /**
     * @var EntityManagerInterface $em - Doctrine Interface
     */
    private $em;

    /**
     * @var SettingService $settingService - The SimPanel Setting Service
     */
    private $settingService;

    /**
     * @var User - Current logged in user
     */
    private $user;

    /**
     * DataCompiler constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(
      EntityManagerInterface $em,
      SettingService $settingService,
      TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->settingService = $settingService;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * Creates the data array needed for all pages
     *
     * @param $active  string - currently active page in order to set what is active in the front end
     * @param $tab   string - For use when in tabbed pages to show currently opened tab
     *
     * @return array - Returns the data array.
     */
    public function createDataArray($active , $tab = null)
    {
        return [
            'active' => $active,
            'branding' => $this->settingService->getSiteInformation(),
            'currentUser' => $this->user->getUserInfo(),
        ];
    }


}