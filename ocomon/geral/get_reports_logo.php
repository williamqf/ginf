<?php session_start();
/*      Copyright 2023 Flávio Ribeiro

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
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$data = [];
$data['success'] = true;
$data['message'] = "";
$post = $_POST;

$logos_dir = __DIR__ . "/../../includes/logos";

$logo_name = (isset($post['logo_name']) && !empty($post['logo_name']) ? $post['logo_name'] : 'MAIN_LOGO.png');

$logo = $logos_dir . '/' . $logo_name;

if (empty($logo) || !file_exists($logo)) {
    $data = [];
    $data['success'] = false;
    $data['message'] = "Logo not found";
    echo json_encode($data);
    return true;
}

// Read image path, convert to base64 encoding
$logoType = pathinfo($logo, PATHINFO_EXTENSION);
$logoData = file_get_contents($logo);
$imgData = base64_encode($logoData);
// Format the image SRC:  data:{mime};base64,{data};
$imgSrc = 'data:image/' . $logoType . ';base64,'.$imgData;

$data['logo'] = $imgSrc;

echo json_encode($data);
return true;