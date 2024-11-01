<?php
/**
 * Created by PhpStorm.
 * User: floriankrebber
 * Date: 13.10.15
 * Time: 11:27
 */

class wpCdnKolossDbConnector {
  /* Wordpress Database */
  protected $Db;

  function __construct() {
    /* Set database */
    global $wpdb;
    $this->setDatabase($wpdb);
  }

  function getCollection($table) {
    /* Get result by table */
    $result = $this->getDatabase()->get_results("SELECT * FROM ".$table);

    /* Return */
    return $result;
  }

  function save($table, $data, $where = null, $format=null) {
    /* Insert or update */
    if ($where) {
      /* Entry already exists, update */
      $this->getDatabase()->update($table, $data, $where, $format);
      return $where['id'];
    } else {
      /* Entry is new, insert */
      if ($this->getDatabase()->insert($table, $data, $format))
        return $this->getDatabase()->insert_id;
    }

    /* Return false */
    return false;
  }

  function delete($table, $data, $format=null) {
    /* Delete */
    return $this->getDatabase()->delete($table, $data, $format);
  }

  function setDatabase($Db) {
    $this->Db = $Db;
  }

  function getDatabase() {
    return $this->Db;
  }
}
