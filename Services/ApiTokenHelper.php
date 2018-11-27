<?php

namespace K2klettern\ApiTokenConexBundle\Services;

class ApiTokenHelper
{
    private $_user;
    private $_secret;
    private $_tokenurl;
    public $tokenonly;
    public $token;
    public $url;


    public function __construct(array $params)
    {
        $this->tokenonly = isset($_SESSION['token']) ? $_SESSION['token'] : false;
        $this->_user = array_key_exists('user',$params) ? $params['user'] : null;
        $this->_secret = array_key_exists('secret',$params) ? $params['secret'] : null;
        $this->_tokenurl = array_key_exists('tokenurl',$params) ? $params['tokenurl'] : null;
    }

    private function _curlExec(string $url, array $params): string
    {
        try {
            if(!$this->tokenonly && $url != $this->_tokenurl)
            {
                $this->getToken();
                $this->_curlExec($url, $params);
            }
            $curl = curl_init();

            if(!in_array('cache-control: no-cache', $params)) {
                $params[] = "Origin: test-and-inspect-ntres40.kennzeichenbox.de";
                $params[] = "cache-control: no-cache";
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $params,
            ));

            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception("cURL Error #:" . $err);
            } else {
                return $response;
            }
        } catch (\Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function getData(string $url): string
    {
        if(!$this->tokenonly) {
            $tokenarray = $this->getToken();
            $this->tokenonly = $this->_cleanToken($tokenarray);
        } else {
            $header = ["Authorization: Bearer " . $this->tokenonly];
        }
        $data = $this->_curlExec($url, $header);
        return $data;
    }
    /**
     * @return mixed
     */
    public function getToken(): string
    {
        $this->token = $this->_curlExec($this->_tokenurl, ["User:" . $this->_user, "Secret:" . $this->_secret]);
        $this->tokenonly = $this->_cleanToken($this->token);
        $_SESSION['token'] = $this->tokenonly;
        return $this->token;
    }

    /**
     * @param $token
     * @return string
     */
    private function _cleanToken($token): string
    {
            return json_decode($token)->token;
    }

}