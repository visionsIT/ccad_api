<?php
$mysqli = new mysqli(env('DB_HOST','localhost'), env('DB_USERNAME', 'root'), env('DB_PASSWORD', 'root'), env('DB_DATABASE', 'laravel_my_rewards2'));
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
$ssoHostUrl = env('APP_URL');
$idp_entityId = '';
$idp_sso_url = '';
$idp_sl_url = '';
$idp_x509cert = '';
$spSlsUrl = env('APP_URL');

// If you choose to use ENV vars to define these values, give this IdP its own env var names
// so you can define different values for each IdP, all starting with 'SAML2_'.$this_idp_env_id
$this_idp_env_id = 'ccad';

//This is variable is for simplesaml example only.
// For real IdP, you must set the url values in the 'idp' config to conform to the IdP's real urls.
$idp_host = ($ssoHostUrl)?$ssoHostUrl:'http://localhost:8000/simplesaml';

$result = $mysqli->query("SELECT * FROM sso_login_details where id = 1");
while ($row = $result->fetch_assoc())
{
    $idp_entityId = ($row['entity_id'])?$row['entity_id']:$idp_host . '/saml2/idp/metadata.php';
    $idp_sso_url = ($row['sso_url'])?$row['sso_url']:$idp_host . '/saml2/idp/SSOService.php';
    $idp_sl_url = ($row['sl_url'])?$row['sl_url']:$idp_host . '/saml2/idp/SingleLogoutService.php';
    $idp_x509cert = ($row['x509'])?$row['x509']:'MIID/TCCAuWgAwIBAgIJAI4R3WyjjmB1MA0GCSqGSIb3DQEBCwUAMIGUMQswCQYDVQQGEwJBUjEVMBMGA1UECAwMQnVlbm9zIEFpcmVzMRUwEwYDVQQHDAxCdWVub3MgQWlyZXMxDDAKBgNVBAoMA1NJVTERMA8GA1UECwwIU2lzdGVtYXMxFDASBgNVBAMMC09yZy5TaXUuQ29tMSAwHgYJKoZIhvcNAQkBFhFhZG1pbmlAc2l1LmVkdS5hcjAeFw0xNDEyMDExNDM2MjVaFw0yNDExMzAxNDM2MjVaMIGUMQswCQYDVQQGEwJBUjEVMBMGA1UECAwMQnVlbm9zIEFpcmVzMRUwEwYDVQQHDAxCdWVub3MgQWlyZXMxDDAKBgNVBAoMA1NJVTERMA8GA1UECwwIU2lzdGVtYXMxFDASBgNVBAMMC09yZy5TaXUuQ29tMSAwHgYJKoZIhvcNAQkBFhFhZG1pbmlAc2l1LmVkdS5hcjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMbzW/EpEv+qqZzfT1Buwjg9nnNNVrxkCfuR9fQiQw2tSouS5X37W5h7RmchRt54wsm046PDKtbSz1NpZT2GkmHN37yALW2lY7MyVUC7itv9vDAUsFr0EfKIdCKgxCKjrzkZ5ImbNvjxf7eA77PPGJnQ/UwXY7W+cvLkirp0K5uWpDk+nac5W0JXOCFR1BpPUJRbz2jFIEHyChRt7nsJZH6ejzNqK9lABEC76htNy1Ll/D3tUoPaqo8VlKW3N3MZE0DB9O7g65DmZIIlFqkaMH3ALd8adodJtOvqfDU/A6SxuwMfwDYPjoucykGDu1etRZ7dF2gd+W+1Pn7yizPT1q8CAwEAAaNQME4wHQYDVR0OBBYEFPsn8tUHN8XXf23ig5Qro3beP8BuMB8GA1UdIwQYMBaAFPsn8tUHN8XXf23ig5Qro3beP8BuMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAGu60odWFiK+DkQekozGnlpNBQz5lQ/bwmOWdktnQj6HYXu43e7sh9oZWArLYHEOyMUekKQAxOK51vbTHzzw66BZU91/nqvaOBfkJyZKGfluHbD0/hfOl/D5kONqI9kyTu4wkLQcYGyuIi75CJs15uA03FSuULQdY/Liv+czS/XYDyvtSLnu43VuAQWN321PQNhuGueIaLJANb2C5qq5ilTBUw6PxY9Z+vtMjAjTJGKEkE/tQs7CvzLPKXX3KTD9lIILmX5yUC3dLgjVKi1KGDqNApYGOMtjr5eoxPQrqDBmyx3flcy0dQTdLXud3UjWVW3N0PYgJtw5yBsS74QTGD4=';
}
$mysqli -> close();

return $settings = array(

    /*****
     * One Login Settings
     */

    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true, //@todo: make this depend on laravel config

    // Enable debug mode (to print errors)
    'debug' => env('APP_DEBUG', true),

    // Service Provider Data that we are deploying
    'sp' => array(

        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        //'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
       //'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
       'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
       //'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password',
       //'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
       //'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified',


        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => '',
        'privateKey' => '',

        // Identifier (URI) of the SP entity.
        // Leave blank to use the '{idpName}_metadata' route, e.g. 'test_metadata'.
        'entityId' => env('APP_URL').'/saml2/ccad/metadata',

        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-POST binding.
            // Leave blank to use the '{idpName}_acs' route, e.g. 'test_acs'
            'url' => env('APP_URL').'/saml2/ccad/acs',
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        // Remove this part to not include any URL Location in the metadata.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-Redirect binding.
            // Leave blank to use the '{idpName}_sls' route, e.g. 'test_sls'
            'url' => $spSlsUrl,
        ),
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array(
        // Identifier of the IdP entity  (must be a URI)
        //'entityId' => env('SAML2_adports_IDP_ENTITYID', $idp_host . '/saml2/idp/metadata.php'),
        'entityId' => ($idp_entityId)?$idp_entityId:$idp_host . '/saml2/idp/metadata.php',
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message,
            // using HTTP-Redirect binding.
            //'url' => env('SAML2_adports_IDP_SSO_URL', $idp_host . '/saml2/idp/SSOService.php'),
            'url' => ($idp_sso_url)?$idp_sso_url:$idp_host . '/saml2/idp/SSOService.php',
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request,
            // using HTTP-Redirect binding.
            //'url' => env('SAML2_adports_IDP_SL_URL', $idp_host . '/saml2/idp/SingleLogoutService.php'),
            'url' => ($idp_sl_url)?$idp_sl_url:$idp_host . '/saml2/idp/SingleLogoutService.php',
        ),
        // Public x509 certificate of the IdP
        //'x509cert' => env('SAML2_adports_IDP_x509', 'MIID/TCCAuWgAwIBAgIJAI4R3WyjjmB1MA0GCSqGSIb3DQEBCwUAMIGUMQswCQYDVQQGEwJBUjEVMBMGA1UECAwMQnVlbm9zIEFpcmVzMRUwEwYDVQQHDAxCdWVub3MgQWlyZXMxDDAKBgNVBAoMA1NJVTERMA8GA1UECwwIU2lzdGVtYXMxFDASBgNVBAMMC09yZy5TaXUuQ29tMSAwHgYJKoZIhvcNAQkBFhFhZG1pbmlAc2l1LmVkdS5hcjAeFw0xNDEyMDExNDM2MjVaFw0yNDExMzAxNDM2MjVaMIGUMQswCQYDVQQGEwJBUjEVMBMGA1UECAwMQnVlbm9zIEFpcmVzMRUwEwYDVQQHDAxCdWVub3MgQWlyZXMxDDAKBgNVBAoMA1NJVTERMA8GA1UECwwIU2lzdGVtYXMxFDASBgNVBAMMC09yZy5TaXUuQ29tMSAwHgYJKoZIhvcNAQkBFhFhZG1pbmlAc2l1LmVkdS5hcjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMbzW/EpEv+qqZzfT1Buwjg9nnNNVrxkCfuR9fQiQw2tSouS5X37W5h7RmchRt54wsm046PDKtbSz1NpZT2GkmHN37yALW2lY7MyVUC7itv9vDAUsFr0EfKIdCKgxCKjrzkZ5ImbNvjxf7eA77PPGJnQ/UwXY7W+cvLkirp0K5uWpDk+nac5W0JXOCFR1BpPUJRbz2jFIEHyChRt7nsJZH6ejzNqK9lABEC76htNy1Ll/D3tUoPaqo8VlKW3N3MZE0DB9O7g65DmZIIlFqkaMH3ALd8adodJtOvqfDU/A6SxuwMfwDYPjoucykGDu1etRZ7dF2gd+W+1Pn7yizPT1q8CAwEAAaNQME4wHQYDVR0OBBYEFPsn8tUHN8XXf23ig5Qro3beP8BuMB8GA1UdIwQYMBaAFPsn8tUHN8XXf23ig5Qro3beP8BuMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAGu60odWFiK+DkQekozGnlpNBQz5lQ/bwmOWdktnQj6HYXu43e7sh9oZWArLYHEOyMUekKQAxOK51vbTHzzw66BZU91/nqvaOBfkJyZKGfluHbD0/hfOl/D5kONqI9kyTu4wkLQcYGyuIi75CJs15uA03FSuULQdY/Liv+czS/XYDyvtSLnu43VuAQWN321PQNhuGueIaLJANb2C5qq5ilTBUw6PxY9Z+vtMjAjTJGKEkE/tQs7CvzLPKXX3KTD9lIILmX5yUC3dLgjVKi1KGDqNApYGOMtjr5eoxPQrqDBmyx3flcy0dQTdLXud3UjWVW3N0PYgJtw5yBsS74QTGD4='),
        'x509cert' => ($idp_x509cert)?$idp_x509cert:'MIIDADCCAeigAwIBAgIQeCQmlfv0XqRCScXqTtugRjANBgkqhkiG9w0BAQsFADA8MTowOAYDVQQDEzFBREZTIEVuY3J5cHRpb24gLSBhZGZzLmNsZXZlbGFuZGNsaW5pY2FidWRoYWJpLmFlMB4XDTIwMTEwMzIxMDcxNFoXDTIzMTEwMzIxMDcxNFowPDE6MDgGA1UEAxMxQURGUyBFbmNyeXB0aW9uIC0gYWRmcy5jbGV2ZWxhbmRjbGluaWNhYnVkaGFiaS5hZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANRRjCOCHjAzKGE4Nc3nV98XQr3HYm7nAuixEhM7Bj0W1oBqj2Z4uvpv+7MHpMphbYRg0tKAEK8Yv0Mvk5FXypIy8kbYJwkVNzvubP3s+EzkI9z1exLnVqRJK2GaGbZASRTM4Ssc8GiHVYvVoCtLp/Uu1nijAdCR7gBuklNQy7Lhp4/aFGFiXK00WL8ANRW1id2dk+heK19G7ZxMnZUDT4i5QIxBhT52GfVzIjWHL+rF2ciso6lr4h/PxLBX/h79x6LPOH+D32T423HBL/qYmEp9G54akMooKXMrtbzoSBPhq7XogsQT2yLpNV9SnG4pDL9IkyZi9Es1M7GsH7parYcCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAibxXBS4wnKS6A72T4hccKg1Q1xRb74ggKYljDu2/YVYei2W8LVnwhZIrA0aUr8JNXzlOgoCPI0VKlqco5Y19afjEx4cB9sSUW9xC47BhXbEHuT+CSvNpeUIEWdIvhwOfg9cPVaK8bvKzzuuT6fWJ8rPEzdhLtiHpaJaK/r7NNjkBAetDl3GRMSEAGl+Ckeb6SvV1Hjz0vAgUJ2/JPgdZR0de1pZLpWHRJniE1gUEfLPHplhwqpS2pV6e8oO5559pIt2zSaRkLEvTnSuz3xP8viTnp0W1Xm4v4WzMndOztC6gVbCHXpYru79gTtRmsEPLFxKC6y24WISaX+u2EvNioA==',
        /*
         *  Instead of use the whole x509cert you can use a fingerprint
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it)
         */
        // 'certFingerprint' => '',
    ),



    /***
     *
     *  OneLogin advanced settings
     *
     *
     */
    // Security settings
    'security' => array(

        /** signatures and encryptions offered */

        // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        // will be encrypted.
        'nameIdEncrypted' => false,

        // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
        // will be signed.              [The Metadata of the SP will offer this info]
        'authnRequestsSigned' => false,

        // Indicates whether the <samlp:logoutRequest> messages sent by this SP
        // will be signed.
        'logoutRequestSigned' => false,

        // Indicates whether the <samlp:logoutResponse> messages sent by this SP
        // will be signed.
        'logoutResponseSigned' => false,

        /* Sign the Metadata
         False || True (use sp certs) || array (
                                                    keyFileName => 'metadata.key',
                                                    certFileName => 'metadata.crt'
                                                )
        */
        'signMetadata' => false,


        /** signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => false,

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed.        [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => false,

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => false,

        // Authentication context.
        // Set to false and no AuthContext will be sent in the AuthNRequest,
        // Set true or don't present thi parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
        //'requestedAuthnContext' => true,
        'requestedAuthnContext' => array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
    ),

    // Contact information template, it is recommended to suply a technical and support contacts
    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'name',
            'emailAddress' => 'no@reply.com'
        ),
        'support' => array(
            'givenName' => 'Support',
            'emailAddress' => 'no@reply.com'
        ),
    ),

    // Organization information template, the info in en_US lang is recomended, add more if required
    'organization' => array(
        'en-US' => array(
            'name' => 'Name',
            'displayname' => 'Display Name',
            'url' => 'http://url'
        ),
    ),

/* Interoperable SAML 2.0 Web Browser SSO Profile [saml2int]   http://saml2int.org/profile/current

   'authnRequestsSigned' => false,    // SP SHOULD NOT sign the <samlp:AuthnRequest>,
                                      // MUST NOT assume that the IdP validates the sign
   'wantAssertionsSigned' => true,
   'wantAssertionsEncrypted' => true, // MUST be enabled if SSL/HTTPs is disabled
   'wantNameIdEncrypted' => false,
*/

);
