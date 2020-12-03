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

        $as = new \SimpleSaML\Auth\Simple('default-sp');
        $as->requireAuth();
        $attributes = $as->getAttributes();
        print_r($attributes); 

        $session = \SimpleSAML\Session::getSessionFromRequest();
        $session->cleanup();
    }

    function authenticate($login, $password)
    {
        global $saml_url, $saml_cacert;

        $fields = array(
            'username' => $login,
            'password' => $password,
        );

        #ignore what has been put in already - go to IdP page
        curl_close($ch);

        $this->tgt = null;
        foreach (explode("\n", $this->response) as $line) {
            if (preg_match('/^Location: .*\/(TGT.*)$/', $line, $mat)) {
                $this->tgt = rtrim($mat[1]);//str_replace('?bypassSPNEGO=true', '', $mat[1]);
            }
        }

        // CAS returns 201 = Created
        #return $code == 201;
        return;
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

