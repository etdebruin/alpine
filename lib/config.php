<?php

  /**
   * Provides access methods for setting and retrieving instance-
   * or system-specific configuration attributes.
   *
   * @author   Justin "Jark" Stayton <jstayton@monkdevelopment.com>
   * @category Monastery
   */
  class Config {

    /**
     * Configuration attributes.
     *
     * @var array
     */
    private static $config = array();

    /**
     * Use {@link Config::set()} to set attributes and {@link Config::get()} to
     * retrieve attributes.
     *
     * @return Config
     */
    private function __construct() { }

    /**
     * Sets a configuration attribute.
     *
     * @param string $attribute name of attribute
     * @param mixed $value value of attribute
     * @uses  Config::$config
     */
    public static function set($attribute, $value) {
      self::$config[$attribute] = $value;
    }

    /**
     * Returns a configuration attribute.
     *
     * @param  string $attribute attribute name to return the value of
     * @return mixed
     * @uses   Config::$config
     */
    public static function get($attribute) {
      return self::$config[$attribute];
    }

  }

?>
