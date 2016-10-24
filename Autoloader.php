<?php


    class Autoloader
    {
        public static function loadClass($class)
        {

            echo $class;
            if ($class != 'Database\PDO') {

                $class = ltrim($class, '\\');

                if (!defined('PATH_SEPARATOR'))
                    define('PATH_SEPARATOR', getenv('COMSPEC')? ';' : ':');
                ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR.dirname(__FILE__));

                $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
                var_dump('$filePath: ' . $filePath);
                var_dump('$class: ' . $class);
                include $filePath;
            }

        }

        public static function autoloadRegister()
        {
            spl_autoload_register('self::loadClass');
        }

    }
