<?php
/**
 * Created by PhpStorm.
 * User: floriankrebber
 * Date: 13.10.15
 * Time: 11:27
 */

class wpCdnKolossCdnConnector {
  /* Base url for cdn */
  var $base_url = 'https://cdnjs.cloudflare.com/ajax/libs/';

  /* Base API url */
  var $api_url = 'https://api.cdnjs.com/libraries/';

  /* Base API search url */
  var $api_search_url = 'https://api.cdnjs.com/libraries?search=';

  /* Base source url */
  var $base_source_url = '//cdnjs.cloudflare.com/ajax/libs/';

  public function searchCdn($search_string) {
    /* Search cdn and return result */
    if (function_exists('file_get_contents')) {
      /* Get search result */
      $result = file_get_contents($this->getApiSearchUrl().urlencode($search_string));

      /* Return */
      return $result;
    }
  }

  public function getLibraryDetail($library, $additional_fields = array('name', 'version', 'description', 'homepage', 'repository', 'author', 'assets')) {
    /* Search cdn and return result */
    if (function_exists('file_get_contents')) {
      /* Get search result */
      $result = file_get_contents($this->getApiUrl().urlencode($library).'?fields='.implode(',', $additional_fields));

      /* Return */
      return $result;
    }
  }

  public function getBaseUrl() {
      return $this->base_url;
  }

  public function getApiUrl() {
      return $this->api_url;
  }

  public function getApiSearchUrl() {
      return $this->api_search_url;
  }

  public function getBaseSourceUrl() {
      return $this->base_source_url;
  }

  public function getAssetUrl($library, $version, $file) {
    /* Return asset url */
    return $this->getBaseUrl().$library.'/'.$version.'/'.$file;
  }
};
