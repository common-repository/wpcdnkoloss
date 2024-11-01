<?php

class WpCdnKolossLoader {
  var $filter = array();
  var $action = array();

  function __construct() {}

  function registerLoader($type, $hookpoint, $function) {
    $this->{$type} = array (
      'hookpoint' => $hookpoint,
      'function' => $function
    );
  }

  function executeLoader($type, $hookpoint = null) {
    switch ($type) {
      case 'action':
        $this->_loadActions($hookpoint);
      break;
      case 'filter':
        $this->_loadFilters($hookpoint);
      break;
      default:
      break;
    }
  }

  function _loadActions($hookpoint) {
    if ($hookpoint) {
      foreach ($this->action[$hookpoint] as $action) {
        add_action($action['hookpoint'], $action['function']);
      }
    } else {
      foreach ($this->action as $action) {
        add_action($action['hookpoint'], $action['function']);
      }
    }
  }

  function _loadFilters($hookpoint) {
    if ($hookpoint) {
      foreach ($this->filter[$hookpoint] as $filter) {
        add_filter($filter['hookpoint'], $action['function']);
      }
    } else {
      foreach ($this->filter as $filter) {
        add_filter($filter['hookpoint'], $action['function']);
      }
    }

  }
}

?>
