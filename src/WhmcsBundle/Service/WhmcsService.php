<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 15/02/2018
 * Time: 20:57
 */

namespace WhmcsBundle\Service;


use AppBundle\Entity\User;
use GuzzleHttp\Client;

class WhmcsService
{

    /**
     * @var User
     */
    private $user;

    /**
     * @var Array
     */
    private $whmcsSettings;
    /**
     *
     * WhmcsService constructor.
     */
    public function __construct(User $user , $whmcsSettings)
    {
        $this->user = $user;
        $this->whmcsSettings = $whmcsSettings;

    }

    public function getWhmcsUser()
    {
        $client = new Client();

        $whmcsUrl = $this->whmcsSettings['whmcs_url'];
        $whmcsApiUrl = $whmcsUrl . '/includes/api.php';


        $postfields = array(
            'identifier' => $this->whmcsSettings['whmcs_identifier'],
            'secret' => $this->whmcsSettings['whmcs_secret'],
            'action' => 'GetClients',
            'search' => $user->getEmail(),
            'responsetype' => 'json',
        );


        try {
            $apiRequest = $client->post($whmcsApiUrl, ['form_params' => $postfields]);

            $apiResult = $apiRequest->getBody()->getContents();

            $apiResultArray = json_decode($apiResult);

            // Get through the maze of objects and variables
            $apiResultArray = get_object_vars($apiResultArray);
            $apiResultClients = get_object_vars($apiResultArray['clients']);
            $apiResultClient = $apiResultClients['client'];
            $apiClient = get_object_vars($apiResultClient[0]);

            return $apiClient;
        }catch (\Exception $e){
            return false;
        }

        }
}