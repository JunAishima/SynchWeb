<?php

namespace SynchWeb\Authentication\Type;

use simplesamlphp;
use SynchWeb\Authentication\AuthenticationInterface;
use SynchWeb\Authentication\AuthenticationParent;

require_once('../../simplesamlphp/lib/_autoload.php');

class SAML extends AuthenticationParent implements AuthenticationInterface
{
    function check()
    {
        global $sam_url, $saml_sso, $saml_cacert;

        if (!$saml_sso) return false;

        $as = new \SimpleSAML\Auth\Simple('default-sp');

        #$params = session_get_cookie_params();
        #setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        #session_unset();
        #session_destroy();

        if ($as->isAuthenticated()) {
          $attributes = $as->getAttributes();
          return substr($attributes['eduPersonPrincipalName'][0], 0,-8);
        }

    }

    function authenticate($login, $password) {
        if (!$this->check()) {
            $url = $as.getLoginUrl();
            echo "do something useful here";
            #print('<a href=>"'. htmlspecialchars($url) . '">Login</a>');
            return;
	}
    }
    function service($service)
    {
        global $saml_url, $saml_cacert;

        $fields = array(
            'service' => $service,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $saml_url . '' . $this->tgt);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CAINFO, $saml_cacert);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return rtrim($resp);
    }

    function validate($service, $ticket)
    {
        global $sam_url, $saml_cacert;

        $fields = array(
            'service' => $service,
            'ticket' => $ticket,
            'format' => 'JSON',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $saml_url . '');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CAINFO, $saml_cacert);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return rtrim($resp);
    }
}

