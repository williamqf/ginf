<?php
/* Copyright 2024 Flávio Ribeiro

This file is part of OCOMON.

OCOMON is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

OCOMON is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/session_start();

/**
 * Get an OAuth2 token from an OAuth2 provider.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/ocomon/ocomon_desenv/admin/oauth/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft account
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file,
 * revoke access to your app and run the script again.
 */

/**
 * Aliases for League Provider Classes
 * Make sure you have added these to your composer.json and run `composer install`
 * Plenty to choose from here:
 * @see https://oauth2-client.thephpleague.com/providers/thirdparty/
 */
//@see https://github.com/thephpleague/oauth2-google
// use League\OAuth2\Client\Provider\Google;
//@see https://packagist.org/packages/hayageek/oauth2-yahoo
// use Hayageek\OAuth2\Client\Provider\Yahoo;
//@see https://github.com/stevenmaguire/oauth2-microsoft
// use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
//@see https://github.com/greew/oauth2-azure-provider
// use Greew\OAuth2\Client\Provider\Azure;


if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

$basicConfig = getConfig($conn);
$siteUrl = $basicConfig['conf_ocomon_site'];
$siteUrl = rtrim($siteUrl, '/');
$thisFileRelativePath = '/admin/oauth/get_oauth_token.php';
$redirectUri = $siteUrl . $thisFileRelativePath;


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>
<body>
    
<?php

if (!isset($_GET['code']) && !isset($_POST['provider'])) {
?>
    <div class="container">
        <form method="post">
            
            <div class="form-group row mt-2 mb-4">

                <h4 class="w-100 mt-5 ml-4 mb-4"><i class="fas fa-key text-secondary"></i>&nbsp;<?= TRANS('CREDENTIALS_TO_OAUTH_IMAP'); ?></h4>
                <?= message('info', TRANS('TXT_IMPORTANT'), '<hr>' . TRANS('HELPER_CREDENTIALS_OAUTH_IMAP'), '', '', true); ?>
            
                <?php
                    $hasClientID = getConfigValue($conn, 'IMAP_OAUTH_CLIENT_ID');
                    $hasClientSecret = getConfigValue($conn, 'IMAP_OAUTH_CLIENT_SECRET');
                    $hasTenantID = getConfigValue($conn, 'IMAP_OAUTH_TENANT_ID');
                    $hasRefreshToken = getConfigValue($conn, 'IMAP_OAUTH_REFRESH_TOKEN');

                    if ($hasClientID && $hasClientSecret && $hasTenantID && $hasRefreshToken) {
                        echo message('success', '', TRANS('MSG_CREDENTIALS_AND_TOKEN_ALREADY_EXISTS'), '', '', true);
                    }
                
                ?>



                <div class="w-100"></div>


                <label for="redirect_url" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_REDIRECT_URL'); ?>"><?= TRANS('REDIRECT_URL'); ?></label>
                <div class="form-group col-md-10">
                    <input type="text" class="form-control" name="redirect_url" id="redirect_url" autocomplete="off" required placeholder="<?= TRANS('REDIRECT_URL'); ?>" disabled value="<?= $redirectUri; ?>" />
                    <small class="form-text text-muted"><?= TRANS('HELPER_REDIRECT_URL'); ?></small>
                </div>

                <label for="clientId" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CLIENT_ID'); ?>"><?= firstLetterUp(TRANS('CLIENT_ID')); ?></label>
                <div class="form-group col-md-10">
                    <input type="text" class="form-control" name="clientId" id="clientId" autocomplete="off" required placeholder="<?= TRANS('CLIENT_ID'); ?>" />
                    <small class="form-text text-muted"><?= TRANS('HELPER_CLIENT_ID'); ?></small>
                </div>

                <label for="clientSecret" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CLIENT_SECRET'); ?>"><?= firstLetterUp(TRANS('CLIENT_SECRET')); ?></label>
                <div class="form-group col-md-10">
                    <input type="password" class="form-control" name="clientSecret" id="clientSecret" autocomplete="off" required placeholder="<?= TRANS('CLIENT_SECRET'); ?>" />
                    <small class="form-text text-muted"><?= TRANS('HELPER_CLIENT_SECRET'); ?></small>
                </div>

                <label for="tenantId" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_TENANT_ID'); ?>"><?= firstLetterUp(TRANS('TENANT_ID')); ?></label>
                <div class="form-group col-md-10">
                    <input type="text" class="form-control" name="tenantId" id="tenantId" autocomplete="off" required placeholder="<?= TRANS('TENANT_ID'); ?>" />
                    <small class="form-text text-muted"><?= TRANS('HELPER_TENANT_ID'); ?></small>
                </div>

                <input type="hidden" name="provider" value="Azure">

                <input type="submit" class="btn btn-primary" value="Obter Token">
            </div>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

require __DIR__ . "/" .  '../../api/ocomon_api/vendor/autoload.php';

//@see https://github.com/greew/oauth2-azure-provider
use Greew\OAuth2\Client\Provider\Azure;

$providerName = '';
$clientId = '';
$clientSecret = '';
$tenantId = '';

if (array_key_exists('provider', $_POST)) {
    $providerName = $_POST['provider'];
    $clientId = $_POST['clientId'];
    $clientSecret = $_POST['clientSecret'];
    $tenantId = $_POST['tenantId'];
    $_SESSION['provider'] = $providerName;
    $_SESSION['clientId'] = $clientId;
    $_SESSION['clientSecret'] = $clientSecret;
    $_SESSION['tenantId'] = $tenantId;
} elseif (array_key_exists('provider', $_SESSION)) {
    $providerName = $_SESSION['provider'];
    $clientId = $_SESSION['clientId'];
    $clientSecret = $_SESSION['clientSecret'];
    $tenantId = $_SESSION['tenantId'];
}

//If this automatic URL doesn't work, set it yourself manually to the URL of this script
// $redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$params = [
    'clientId' => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri' => $redirectUri,
    'accessType' => 'offline'
    // ,'prompt' => 'consent',
];

$options = [];
$provider = null;

$params['tenantId'] = $tenantId;

$provider = new Azure($params);
$options = [
    'scope' => [
        'https://outlook.office.com/IMAP.AccessAsUser.All',
        // 'https://outlook.office.com/SMTP.Send',
        'offline_access'
    ]
];

$configKeys = [];
$configKeys['IMAP_OAUTH_PROVIDER'] = $providerName;
$configKeys['IMAP_OAUTH_CLIENT_ID'] = $clientId;
$configKeys['IMAP_OAUTH_CLIENT_SECRET'] = $clientSecret;
$configKeys['IMAP_OAUTH_TENANT_ID'] = $tenantId;


if (null === $provider) {
    exit('Provider missing');
}

if (!isset($_GET['code'])) {
    //If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
    //Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    unset($_SESSION['provider']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider']);
    //Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(
        'authorization_code',
        [
            'code' => $_GET['code']
        ]
    );
    //Use this to interact with an API on the users behalf
    //Use this to get a new access token if the old one expires
    // echo 'Refresh Token: ', htmlspecialchars($token->getRefreshToken());

    $tokenChars = $token->getToken();
    $refreshToken = $token->getRefreshToken();

    if (empty($tokenChars) || empty($refreshToken)) {
        exit('Failed to get access token');
    }

    $configKeys['IMAP_OAUTH_ACCESS_TOKEN'] = $tokenChars;
    $configKeys['IMAP_OAUTH_REFRESH_TOKEN'] = $refreshToken;

    $keyErrors = [];
    foreach ($configKeys as $key => $value) {
        if (!setConfigValue($conn, $key, $value)) {
            $keyErrors[] = $key;
        }
    }

    ?>
        <div class="container">
    <?php

    if (empty($configKeys['IMAP_OAUTH_REFRESH_TOKEN']) || !empty($keyErrors)) {
        $exception .= "<hr>" . TRANS('ERROR_ON_SAVE_CONFIG') . "<hr>" . implode("<hr>", $keyErrors);  
        return;
        exit();      
    }

    echo "<br/><br/>";
    echo message('success', 'Yeahh!', TRANS('SUCCESS_ON_RETRIEVE_TOKEN'), '', '', true);
    ?>
            <div class="row w-100"></div>
            <div class="form-group col-10 col-md-10 d-none d-md-block"></div>
            
            <div class="form-group col-12 col-md-2">
                <button type="reset" class="btn btn-primary btn-block" onClick="window.close();"><?= TRANS('BT_CLOSE'); ?></button>
            </div>

        </div>
    </body>
</html>
    <?php
}
