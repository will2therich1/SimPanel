<?php
/**
 * SimPanel Encryption Service.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Service\Security;

class EncryptionService
{
    /**
     * @var array - Encryption parameters, defined in services.yml
     */
    private $encParams;

    public function __construct($encParams)
    {
        $this->encParams = $encParams;
    }

    /**
     * Encrypt some data.
     *
     * @param $data - Data to encrypt
     *
     * @return string - Encrypted data.
     */
    public function encrypt($data)
    {
        $returnEncryption = openssl_encrypt($data, $this->encParams['enc_cypher'], $this->encParams['enc_secret'], $options = 0, $this->encParams['enc_iv']);

        return $returnEncryption;
    }

    /**
     * Decrypts some data.
     *
     * @param $data - Data to decrypt
     *
     * @return string - Unencrypted data
     */
    public function decrypt($data)
    {
        $returnEncryption = openssl_decrypt($data, $this->encParams['enc_cypher'], $this->encParams['enc_secret'], $options = 0, $this->encParams['enc_iv']);

        return $returnEncryption;
    }
}
