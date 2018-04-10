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
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DataCompiler
{
    /**
     * @var EntityManagerInterface - Doctrine Interface
     */
    private $em;

    /**
     * @var SettingService - The SimPanel Setting Service
     */
    private $settingService;

    /**
     * @var User - Current logged in user
     */
    private $user;

    /**
     * @var FilesystemAdapter - The cache
     */
    private $cache;

    /**
     * DataCompiler constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(
      EntityManagerInterface $em,
      SettingService $settingService,
      TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->settingService = $settingService;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->cache = new FilesystemAdapter('app.cache');
    }

    /**
     * Creates the data array needed for all pages.
     *
     * @param $active  string - currently active page in order to set what is active in the front end
     * @param $tab   string - For use when in tabbed pages to show currently opened tab
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return array - Returns the data array
     */
    public function createDataArray($active, $tab = null)
    {
        $dataArray = [
            'active' => $active,
            'branding' => $this->getCachedSiteInfo(),
        ];

        if ($this->user instanceof User) {
            $dataArray['currentUser'] = $this->user->getUserInfo();
        } else {
            $dataArray['currentUser'] = '';
        }

        return $dataArray;
    }

    /**
     * Refresh's/gets the cached branding information for the site.
     *
     * @return mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getCachedSiteInfo()
    {
        $brandingCache = $this->cache->getItem('branding-cache');

        if (null == $brandingCache->get()) {
            $brandingCache->set($this->settingService->getSiteInformation());
            $brandingCache->tag('branding-cache');
            $brandingCache->expiresAfter(\DateInterval::createFromDateString('1 hour'));

            $this->cache->save($brandingCache);
        }

        return $brandingCache->get();
    }

    /**
     * Deletes the current cache for branding and reinstates it to deal with updates.
     *
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function refreshBrandingCache()
    {
        $this->cache->deleteItem('branding-cache');
        $this->getCachedSiteInfo();

        return true;
    }
}
