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
     *
     * @param EntityManagerInterface $em
     */
    public function __construct()
    {
    }

    /**
     * Gets the pagination offset.
     *
     * @param $Request Request - The Request
     *
     * @return string - Returns the offset;
     */
    public function getOffset(Request $request)
    {
        if (null !== $request->get('offset') && '' !== $request->get('offset')) {
            $offset = $request->get('offset');
        } else {
            $offset = 0;
        }

        return $offset;
    }

    /**
     * Gets the pagination limit.
     *
     * @param $Request Request - The Request
     *
     * @return string - Returns the limit;
     */
    public function getLimit(Request $request)
    {
        if (null !== $request->get('limit') && '' !== $request->get('limit')) {
            $limit = $request->get('limit');
        } else {
            $limit = 10;
        }

        return $limit;
    }

    /**
     * @param $url - The url for the pagination to link to
     * @param $offset - The current Offset
     * @param $limit - The current Limit
     *
     * @return array
     *               Returns array containing the two necessary links
     */
    public function createPagination($url, $offset, $limit)
    {
        // Setting the limit
        $nextLimit = $limit + 10;

        if (10 == $limit) {
            $lastLimit = 10;
        } else {
            $lastLimit = $limit - 10;
        }
        // Setting the offset
        $nextOffset = $offset + 10;
        if (0 == $offset) {
            $lastOffset = 0;
        } else {
            $lastOffset = $offset - 10;
        }
        // Create the links
        $nextPageLink = "$url?limit=".$nextLimit.'&offset='.$nextOffset;
        $lastPageLink = "$url?limit=".$lastLimit.'&offset='.$lastOffset;

        $data['nextlink'] = $nextPageLink;
        $data['lastlink'] = $lastPageLink;

        return $data;
    }
}
