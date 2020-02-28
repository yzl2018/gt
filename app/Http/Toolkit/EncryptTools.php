<?php
/**
 * Class EncryptTools
 *
 * NOTE: php version >= 5.1
 */
namespace App\Http\Toolkit;
class EncryptTools
{
    private $sec_key = '';
    private $salt_key = '';
    private $resp_data;

    public function __construct($sec_key, $salt_key)
    {
        $this->sec_key = $sec_key;
        $this->salt_key = $salt_key;
    }

    /**
     * @param array $params
     * @return array
     */
    public function createRequestData(array $params)
    {
        $data = array();
        $data['Sign'] = $this->sign($params);
        $data['Data'] = $this->buildDataText($params);
        return $data;
    }

    /**
     * @param array $response
     * @return bool
     */
    public function verify(array $response)
    {
        if (! isset($response['Sign']) || ! isset($response['Data'])) return false;
        $this->resp_data = $this->recoverData($response['Data']);
        if ($this->resp_data == false) return false;
        if ($response['Sign'] != $this->sign($this->resp_data)) return false;
        return true;
    }

    /**
     * fetch response data
     * @return array|null
     */
    public function getResponseData()
    {
        if (! is_array($this->resp_data)) return null;
        return (array) $this->resp_data;
    }

    /**
     * @param array $params
     * @return string
     */
    private function sign(array $params)
    {
        ksort($params);
        $keys_str = implode('%#', array_keys($params));
        $values_str = implode(array_values($params));
        return $this->computeHash($keys_str . $values_str . $this->sec_key);
    }

    /**
     * @param string $data
     * @return string
     */
    private function computeHash($data)
    {
        return hash_hmac('sha512', $data, $this->salt_key);
    }

    /**
     * @param array $data
     * @return string
     */
    private function buildDataText(array $data)
    {
        $data_str = json_encode($data);
        $data_str = base64_encode($data_str);

        $suffix = substr($data_str, strlen($data_str) - 3, 3);
        $data_str = substr($data_str, 0, strlen($data_str) - 3);
        // replace
        $data_str = str_replace('J', '%', $data_str);
        $data_str = str_replace('w', '*', $data_str);
        $data_str = str_replace('6', '@', $data_str);
        $data_str = str_replace('8', ',', $data_str);
        $data_str = str_replace('a', '6', $data_str);
        $data_str = str_replace('Q', 'w', $data_str);
        // reverse
        $data_str = strrev($data_str);
        if (strlen($data_str) % 3 == 1) {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len);
            $data_str_sub2 = substr($data_str, $substr_len, $substr_len);
            $data_str_sub3 = substr($data_str, $substr_len * 2, $substr_len + 1);
            $data_str = $data_str_sub2 . $data_str_sub1 . $data_str_sub3;
        }
        else if (strlen($data_str) % 3 == 2) {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len);
            $data_str_sub2 = substr($data_str, $substr_len, $substr_len + 2);
            $data_str_sub3 = substr($data_str, $substr_len * 2 + 2, $substr_len);
            $data_str = $data_str_sub2 . $data_str_sub3 . $data_str_sub1;
        }
        else {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len);
            $data_str_sub2 = substr($data_str, $substr_len, $substr_len);
            $data_str_sub3 = substr($data_str, $substr_len * 2, $substr_len);
            $data_str = $data_str_sub3 . $data_str_sub1 . $data_str_sub2;
        }

        $data_str = $data_str . $suffix;
        return $data_str;
    }

    /**
     * @param string $data
     * @return array|bool
     */
    private function recoverData($data)
    {
        if (! is_string($data)) return false;
        $data_str = preg_replace('/\s/', '', $data);
        if ($data_str != $data) return false;

        $suffix = substr($data_str, strlen($data_str) - 3, 3);
        $data_str = substr($data_str, 0, strlen($data_str) - 3);
        // reverse
        if (strlen($data_str) % 3 == 1) {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len);
            $data_str_sub2 = substr($data_str, $substr_len, $substr_len);
            $data_str_sub3 = substr($data_str, $substr_len * 2, $substr_len + 1);
            $data_str = $data_str_sub2 . $data_str_sub1 . $data_str_sub3;
        }
        else if (strlen($data_str) % 3 == 2) {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len + 2);
            $data_str_sub2 = substr($data_str, $substr_len + 2, $substr_len);
            $data_str_sub3 = substr($data_str, $substr_len * 2 + 2, $substr_len);
            $data_str = $data_str_sub3 . $data_str_sub1 . $data_str_sub2;
        }
        else {
            $substr_len = (int) floor(strlen($data_str) / 3);
            $data_str_sub1 = substr($data_str, 0, $substr_len);
            $data_str_sub2 = substr($data_str, $substr_len, $substr_len);
            $data_str_sub3 = substr($data_str, $substr_len * 2, $substr_len);
            $data_str = $data_str_sub2 . $data_str_sub3 . $data_str_sub1;
        }
        $data_str = strrev($data_str);
        // replace
        $data_str = str_replace('6', 'a', $data_str);
        $data_str = str_replace('w', 'Q', $data_str);
        $data_str = str_replace('%', 'J', $data_str);
        $data_str = str_replace('*', 'w', $data_str);
        $data_str = str_replace('@', '6', $data_str);
        $data_str = str_replace(',', '8', $data_str);

        $data_str = $data_str . $suffix;
        $data_str = base64_decode($data_str);
        if (! $data_str) return false;
        $data_array = json_decode($data_str, true);
        if (! is_array($data_array)) return false;

        return $data_array;
    }

}