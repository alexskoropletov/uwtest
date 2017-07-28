<?php
namespace UWTest;

use Doctrine\ORM\EntityManager;
use Twig_Environment;
use UWTest\Entity\LikeGiven;
use UWTest\Entity\Message;
use UWTest\Entity\User;

class App
{
    private static $config;

    private static $instance;

    private static $doctrine;

    private static $twig;

    private static $user;

    /**
     * Singletone initializing
     *
     * @param array $config
     * @param EntityManager $doctrine
     * @param Twig_Environment $twig
     * @param User $user
     * @return App
     */
    public static function init(array $config, EntityManager $doctrine, Twig_Environment $twig, User $user)
    {
        if (!isset(self::$instance)) {
            self::$instance = new App;
            self::$config = $config;
            self::$doctrine = $doctrine;
            self::$twig = $twig;
            self::$user = $user;
        }

        return self::$instance;
    }

    /**
     * Displaying page depending on current URL
     */
    public static function show()
    {
        $methodName = self::getMethodName($_SERVER['REQUEST_URI']);
        if (method_exists('\UWTest\App', $methodName)) {
            \UWTest\App::$methodName();
        } else {
            $messages = self::$doctrine
                ->getRepository('UWTest\Entity\Message')
                ->findBy(
                    [],
                    ['created_at' => 'DESC'],
                    self::$config['chat']['message_limit']
                );
            $messages = array_reverse($messages);

            echo self::$twig->render(
                'index.html',
                [
                    'user'     => self::$user,
                    'messages' => $messages,
                ]
            );
        }
    }

    /**
     * Getting list of messages
     */
    public static function getChatLog()
    {
        $messages = self::$doctrine
            ->getRepository('UWTest\Entity\Message')
            ->findBy(
                [],
                ['created_at' => 'DESC'],
                200
            );
        $messages = array_reverse($messages);

        echo self::$twig->render('messages.html', ['messages' => $messages, 'user' => self::$user]);
    }

    /**
     * Adding message to stack
     *
     * @param string $message
     */
    public static function saveMessage($messageText = '', $echo = true)
    {
        $status = 'error';
        $messageText || $messageText = $_POST['message'];
        $user = self::$doctrine->getRepository('UWTest\Entity\User');
        $user = $user->find($_POST['user_id']);
        if ($user) {
            $message = new Message();
            $message->setMessage($messageText);
            $message->setUser($user);
            $message->setCreatedAt(new \DateTime());
            self::$doctrine->persist($message);
            self::$doctrine->flush();

            $status = 'ok';
        }

        if ($echo) {
            echo json_encode(['status' => $status]);
        }
    }

    public static function deleteMessage()
    {
        $status = 'error';
        $user = self::$doctrine->getRepository('UWTest\Entity\User');
        $user = $user->find($_POST['user_id']);
        if ($user) {
            $message = self::$doctrine->getRepository('UWTest\Entity\Message');
            $message = $message->find($_POST['message_id']);
            if ($message && $message->getUser()->getId() == $user->getId()) {
                // I could have made some kind of status flag, but I don't want to
                self::$doctrine->remove($message);
                self::$doctrine->flush();
            }
            $status = 'ok';
        }

        echo json_encode(['status' => $status]);
    }

    public static function likeMessage()
    {
        $status = 'error';
        $user = self::$doctrine->getRepository('UWTest\Entity\User');
        $user = $user->find($_POST['user_id']);
        $message = self::$doctrine->getRepository('UWTest\Entity\Message');
        $message = $message->find($_POST['message_id']);
        if ($user && $message && $message->getUser()->getId() != $user->getId()) {
            $likeGiven = self::$doctrine
                ->getRepository('UWTest\Entity\LikeGiven')
                ->findBy(
                    [
                        'user' => $user,
                        'message' => $message,
                    ]
                );
            if (!$likeGiven) {
                $likeGiven = new LikeGiven();
                $likeGiven->setUser($user);
                $likeGiven->setMessage($message);
                self::$doctrine->persist($likeGiven);
                self::$doctrine->flush();

                $status = 'ok';
            }
        }

        echo json_encode(['status' => $status]);
    }

    /**
     * Changing user name
     */
    public static function setUserName()
    {
        self::$user->setName($_POST['name']);
        self::$doctrine->flush();

        echo json_encode(['status' => 'ok']);
    }

    /**
     * Changing user name
     */
    public static function uploadImages()
    {
        $results = [];
        $upload_directory = "/upload/" . date("Y-m-d");
        if (!empty($_FILES)) {
            if (!file_exists(dirname(__DIR__) . '/web' . $upload_directory)) {
                mkdir(dirname(__DIR__) . '/web' . $upload_directory);
            }
            foreach ($_FILES as $file) {
                if (in_array(exif_imagetype($file['tmp_name']), [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
                    $info = pathinfo($file['name']);
                    $file_name = $upload_directory . DIRECTORY_SEPARATOR . sha1(time() . $file['name']) . "." . $info['extension'];
                    if (!in_array($file_name, $results)) {
                        $results[] = $file_name;
                        move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/web' . $file_name);
                        self::saveMessage($file_name, false);
                    }
                }
            }
        }

        echo json_encode(['status' => $results, 'incoming' => $_FILES]);
    }

    /**
     * Figuring out method name from url
     * @param $uri string URL used
     * @return string method name
     */
    public static function getMethodName($uri)
    {
        $uri = str_replace(["-", "_"], "/", $uri);

        return lcfirst(
            implode(
                "",
                array_map(
                    function ($item) {
                        return ucfirst($item);
                    },
                    array_filter(
                        explode("/", $uri),
                        function ($item) {
                            return !empty(trim($item));
                        }
                    )
                )
            )
        );
    }
}