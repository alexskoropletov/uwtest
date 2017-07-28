<?php
namespace UWTest;

use UWTest\App;

require_once dirname(__DIR__) . "/app/bootstrap.php";

$app = App::init($config, $entityManager, $twig, $user);
$app::show();
