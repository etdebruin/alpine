<?php

  class Autoloader {

    private static $classes;

    public static function load($className) {
      // Load the classes and their paths into a static variable if this
      // variable has not yet been initialized. This should only happen the
      // first time load() is called.
      if (!isset(self::$classes)) {
        self::$classes = self::getClasses();

        if ($handle = opendir(dirname(dirname(__FILE__)) . '/model/')) {

          while (false !== ($file = readdir($handle))) {

            // evaluate files only, no directories or hidden files.
            if ( $file != "." && $file != ".." && $file[0] != "." ) {

              $class = ucwords(rtrim($file, ".php"));
              self::$classes[$class] = dirname(dirname(__FILE__)) . '/model/' .$file;

            }
          }
        }
      }

      // Find the class file's path.
      $classFilePath = self::$classes[$className];

      // Load the class file if a path was found for the path.
      if ($classFilePath) {
        require $classFilePath;
      }
    }

    private static function getClasses() {
      return array(
        'Alpine' => 'lib/alpine.php',
        'Config' => 'lib/config.php'
      );
    }

  }

  spl_autoload_register(array('Autoloader', 'load'));

?>
