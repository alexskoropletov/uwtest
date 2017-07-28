<?php
namespace UWTest;

session_start();

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Twig_Loader_Filesystem;
use Twig_Environment;
use UWTest\Entity\User;

// Loading libraries from composer
$loader = require dirname(__DIR__) . "/vendor/autoload.php";
// And Doctrine entities
$loader->addPsr4('UWTest\\Entity\\', dirname(__DIR__) . "/app/entity");
// And application controller
$loader->addPsr4('UWTest\\', dirname(__DIR__) . "/app");

// App config
$config = Yaml::parse(file_get_contents(dirname(__DIR__) . "/app/config/app.yml"));

// Database
$entityManager = EntityManager::create(
    Yaml::parse(file_get_contents(dirname(__DIR__) . "/app/config/database.yml")),
    Setup::createYAMLMetadataConfiguration([dirname(__DIR__) . "/app/config/entity"], $isDevMode = true)
);

// Templates
$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/app/templates');
$twig = new Twig_Environment(
    $loader,
    [
        'cache'       => dirname(__DIR__) . '/app/cache',
        'auto_reload' => true,
    ]
);
$twig->addExtension(new AppTwigFilters());

// User
if (!empty($_COOKIE["uid"])) {
    $sql = sprintf("SELECT id FROM user WHERE SHA1(CONCAT(id, id)) = '%s'", $_COOKIE["uid"]);
    $stmt = $entityManager->getConnection()->prepare($sql);
    $stmt->execute();
    $user_id = $stmt->fetch();
    if (!empty($user_id)) {
        $user = $entityManager->getRepository('UWTest\Entity\User');
        $user = $user->find($user_id['id']);
    }
}
if (empty($user)) {
    $user = new User();
    $user->setName('anonymous' . substr(sha1(time() * rand(0, 500)), -6));
    $entityManager->persist($user);
    $entityManager->flush();
    setcookie("uid", sha1($user->getId() . $user->getId()));
}