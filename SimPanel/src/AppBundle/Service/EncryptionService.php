<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 18/12/17
 * Time: 10:36
 */

namespace AppBundle\Service;


class EncryptionService
{

    private $enc_secret;
    private $enc_iv;
    private $enc_cypher;

    public function __construct(array $encryption_params)
    {

        $this->enc_cypher = $encryption_params['enc_cypher'];
        $this->enc_secret = $encryption_params['enc_secret'];
        $this->enc_iv = $encryption_params['enc_iv'];

        return;

    }


    public function encrypt($data)
    {
        $returnEncryption = openssl_encrypt($data, $this->enc_cypher, $this->enc_secret, $options = 0, $this->enc_iv);

        return $returnEncryption;
    }

    public function decrypt($data)
    {
        $returnEncryption = openssl_decrypt($data, $this->enc_cypher, $this->enc_secret, $options = 0, $this->enc_iv);

        return $returnEncryption;
    }

}