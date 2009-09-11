<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$db = new Database($TWITALYTIC_CFG);
$conn = $db->getConnection();

$od = new OwnerDAO($db);
$owner = $od->getByEmail($_SESSION['user']);

$td = new TweetDAO($db);
$id = new InstanceDAO($db);

if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
    $username = $_REQUEST['u'];
    $oid = new OwnerInstanceDAO($db);
    if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
        echo 'Insufficient privileges. <a href="/">Back</a>.';
        $db->closeConnection($conn);
        die;
    } else {
        $tweets = $td->getAllTweetsByUsername($username);
    }
} else {
    echo 'No access';
    $db->closeConnection($conn);
    die;
}

$s = new SmartyTwitalytic();
$s->assign('tweets', $tweets);
$s->display('status.export.tpl', $username);



?>