<?php

  class Alpine {

    public static $Module;
    public static $types = array();

    private static $db = null;

    public static function render($module, $record, $action) {

      if ($record) {
        try {
          self::$Module = $module::getBy(array(
            'id' => $record
          ));
        }
        catch (Exception $e) {
          echo "$module record $record could not be found: " . $e->getMessage();
          exit;
        }
      }
      else {
        self::$Module = $module::stamp();
      }

      if ($_POST) {
        if (self::$Module->id) {
          self::$Module->save($_POST);
        }
        else {
          self::$Module = $module::create($_POST);
          header("Location: /$module/" . self::$Module->id);
        }
      }

      self::content($module, $action);

    }

    public static function content($module, $action) {

      $template_folder = dirname(dirname(dirname(__FILE__))) . '/template';

      if ($action) {
        $template_file = strtolower($module . '_' . $action . '.php');
      }
      else {
        $template_file = strtolower($module . '.php');
      }

      require $template_folder . '/alpine_head.php';
      $file = file_get_contents($template_folder . '/' . $template_file);

      if ($action == 'edit' || $action == 'add') {
        echo "<form method='POST'>";
      }

      $file = preg_replace("/\(\(edit_([a-z]+)\)\)/ie", 'self::injector(\\1, edit)', $file);
      $file = preg_replace("/\(\(([a-z]+)\)\)/ie", 'self::injector(\\1, view)', $file);
      echo $file;

      if ($action == 'edit' || $action == 'add') {
        echo "<input type=submit>";
        echo "</form>";
      }

      require $template_folder . '/alpine_foot.php';

    }

    static public function injector($string, $type) {
      if ($type == 'view') {
        return self::$Module->$string;
      }
      else {
        if (strstr(self::$types[$string], 'varchar')) {
          return "<input type=text name='$string' id='$string' value='".self::$Module->$string."' />";
        }
        if (self::$types[$string] == 'text') {
          return "<textarea name='$string'>".self::$Module->$string."</textarea>";
        }
      }
    }

    /**
     * Ingest a database row into an Object.
     *
     * @author Etienne de Bruin <etdebruin@gmail.com>
     * @param string $class name of object to instantiate
     * @param string $table name to read the row from
     * @param array $options to be flatted out into a where string
     * @return array of objects specified by $class
     **/
    public static function ingest($class, $table, array $options) {

      if (!count($options)) {
        return false;
      }

      self::database();

      $stmt = self::$db->prepare("SELECT * FROM $table WHERE " . self::toString($options, 'AND'));

      $stmt->execute(self::toValues($options));
      $stmt->setFetchMode(PDO::FETCH_CLASS, $class);

      $rows = $stmt->fetchAll();

      return $rows;

    }

    public static function stamp() {
      $class = get_called_class();
      $table = strtolower(get_called_class());

      self::database();

      $stmt = self::$db->prepare("DESCRIBE $table");
      try {
        $stmt->execute();
      }
      catch (Exception $e) {
        throw new Exception("Could not stamp $class: " . $e->getMessage());
      }

      $table_fields = $stmt->fetchAll();

      $Model = new $class;

      foreach ($table_fields as $table_field) {
        $attr = $table_field['Field'];
        $Model->$attr = '';
        $Model::$types[$attr] = $table_field['Type'];
      }

      return $Model;
    }

    public static function create($options) {
      $Model = self::stamp();

      foreach ($options as $key => $val) {
        $Model->$key = $val;
      }

      try {
        $Model->save();
      }
      catch (Exception $e) {
        throw new Exception("Could not create $class: " . $e->getMessage());
      }

      return $Model;

    }


    /**
     * @return object Model, throws exception if no Models found.
     **/
    public static function getBy($params) {
      $ModelStamp = self::stamp();

      $table = strtolower(get_called_class());

      $Models = self::ingest(get_called_class(), $table, $params);

      if (count($Models) == 0) {
        throw new Exception("Could not find: " . serialize($params));
      }

      $Model = $Models[0];

      return $Model;

    }

    /**
     * @return array Models, empty array if none found.
     **/
    public static function getAllBy($params) {

      $table = strtolower(get_called_class());

      $Models = self::ingest(get_called_class(), $table, $params);
      return $Models;
    }

    /**
     * Update a model with new parameter values
     **/
    function update($params) {

      foreach ($params[0] as $key => $value) {
        $this->$key = $value;
      }

    }

    /**
     * Missing Methods - always called in the context of an object.
     **/
    function __call($method, $params) {

      /**
       * Find 'has_many' associations (upwards)
       **/
      if (isset(static::$has_many)) {
        foreach (static::$has_many as $child => $child_id) {
          if (stristr($method, $child)) {
            $child_class = Inflector::singularize($child);
            $ChildModels = self::ingest($child_class,
                                        $child_class::table,
                                        array($child_id => $this->id));
            return $ChildModels;
          }
        }
      }

      /**
       * Find 'has_one' associations (downwards)
       **/
      if (isset(static::$has_one)) {
        foreach (static::$has_one as $child => $child_id) {
          if (stristr($method, $child)) {
            $child_class = Inflector::singularize($child);

            $ChildModels = self::ingest($child_class,
                                        $child_class::table,
                                        array($child_class::key => $this->$child_id));

            return $ChildModels[0];
          }
        }
      }

      /**
       * Handle 'saves'
       **/
      if ($method == 'save') {
        self::database();
        $table = strtolower(get_called_class());

        if (!$this->id) {

          foreach ($this as $key => $value) {
            $params[0][$key] = $value;
            $columnString .= $key . ',';
            $valueString .= ':' . $key . ',';
            $values[":$key"] = $value;
          }

          $columnString = rtrim($columnString, ',');
          $valueString = rtrim($valueString, ',');

          $stmt = self::$db->prepare('INSERT INTO ' . $table
                  . '(' . $columnString . ') '
                  . 'VALUES(' . $valueString . ')');

          try {
            $stmt->execute($values);
            $params[0]['id'] = self::$db->lastInsertId();
          }
          catch (PDOException $e) {
            throw new Exception($e->getMessage());
          }

          self::update($params);

          return true;

        }
        else if ($this->id) {
          $stmt = self::$db->prepare('UPDATE ' . $table
                  . ' SET '. self::toString($params[0])
                  . ' WHERE id = ' . $this->id);

          $values = self::toValues($params[0]);

          try {
            $stmt->execute($values);
          }
          catch (PDOException $e) {
            throw new Exception($e->getMessage());
          }

          self::update($params);

          return true;
        }

      }

      /**
       * Handle 'deletes'
       **/
      if ($method == 'delete') {
        self::database();
        $table = strtolower(get_called_class());

        if ($this->id) {

          $stmt = 'DELETE FROM ' . $table
                  . ' WHERE id = ' . $this->id;

          try {
            $result = self::$db->query($stmt);
          }
          catch (PDOException $e) {
            throw new Exception($stmt . '-' . $e->getMessage());
          }

        }
      }

      // if (strncasecmp($method, 'save', 4)) {
      //   self::saver(substr($method, 4));
      // }

      // $var = substr($method, 3);



      // if (strncasecmp($method, "get", 3)) {
      //    print "Getting: \n";
      //    return $this->$var;
      // }
      // if (strncasecmp($method, "set", 3)) {
      //    $this->$var = $params[0];
      // }

    }

    // private static function saver($which, $params) {
    //   print "saver\n";
    //   print "$which $params\n";
    // }

    public static function database() {
      if (self::$db == null) {
        self::$db = new PDO('mysql:host=localhost;dbname=' . Config::get('db'), Config::get('dbUser'), Config::get('dbPassword'));
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$db;
      }
    }

    public static function toString($options, $bind = ',') {
      foreach ($options as $key => $val) {
        $where .= " $key = :$key " . $bind;
      }

      $where = rtrim($where, $bind);
      return $where;
    }

    public static function toValues($options) {
      $values = array();

      foreach ($options as $key => $val) {
        $values[":$key"] = $val;
      }
      return $values;
    }


  }

?>
