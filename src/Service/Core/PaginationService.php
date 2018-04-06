<?php
/**
 * Service for getting limits & offsets and creating pagination links!
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */


namespace App\Service\Core;


use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{



    /**
     * SettingService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct()
    {
    }

    /**
     * Gets the pagination offset
     *
     * @param $Request Request - The Request
     *
     * @return string - Returns the offset;
     */
    public function getOffset(Request $request)
    {
        //
        if ($request->get('offset') !== null && $request->get('offset') !== '') {
            $offset = $request->get('offset');
        } else {
            $offset = 0;
        }
        return $offset;
    }


    /**
     * Gets the pagination limit
     *
     * @param $Request Request - The Request
     *
     * @return string - Returns the limit;
     */
    public function getLimit(Request $request)
    {
        //
        if ($request->get('limit') !== null && $request->get('limit') !== '') {
            $limit = $request->get('limit');
        } else {
            $limit = 0;
        }
        return $limit;
    }





}