<?php

/* Require loader */
require(plugin_dir_path( __FILE__ ) . 'WpCdnKolossLoader.php');
require(plugin_dir_path( __FILE__ ) . 'WpCdnKolossCdnConnector.php');
require(plugin_dir_path( __FILE__ ) . 'WpCdnKolossDbConnector.php');

class WpCdnKoloss {
  /**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

  /**
	 * The plugin root dir
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_dir    The root dir of the plugin.
	 */
	protected $plugin_dir;

  /**
	 * The plugin root url
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_url    The root url of the plugin.
	 */
	protected $plugin_url;

  /**
	 * The plugin database table
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_table    The database table of the plugin.
	 */
	protected $plugin_table;

  /**
	 * The plugin database table prefix
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_table_prefix    The database table prefix of the plugin.
	 */
	protected $plugin_table_prefix;

  /**
	 * The loader object
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $Loader    The loader object
	 */
	protected $Loader;

  /**
	 * The CDN connector object
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $CdnConnector    The CDN connector object
	 */
	protected $CdnConnector;

  /**
	 * The Db connector object
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $CdnConnector    The Db connector object
	 */
	protected $DbConnector;

  /**
   * Included files
   */
  var $included_files = array(
    'result' => '',
    'ordered' => array()
  );

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
    /* Global */
    global $wpdb;
    $this->plugin_table_prefix = "wpcdnkoloss_";

    /* Setup */
    $this->plugin_name = 'wpcdnkoloss';
		$this->version = '0.0.1';
    $this->plugin_table = $wpdb->prefix . $this->plugin_table_prefix . 'files';
    $this->plugin_dir = plugin_dir_path( __DIR__ );
    $this->plugin_url = plugins_url('', __DIR__ );
    $this->Loader = new WpCdnKolossLoader();
    $this->CdnConnector = new wpCdnKolossCdnConnector();
    $this->DbConnector = new wpCdnKolossDbConnector();

    /* Setup included files */
    $this->loadIncludedFiles();

    /* Enqueue scripts */
    if (!is_admin()) {
      add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }
	}

  /* Enqueue Scripts */
  function enqueueScripts() {
    /* Check for include files */
    if (!empty($this->included_files['ordered'])) {
      /* Walk */
      foreach ($this->included_files['ordered'] as $library => $version) {
        /* Walk files */
        if (!empty($version)) {
          foreach ($version as $_version => $files) {
            if (!empty($files)) {
              foreach ($files as $file => $Info) {
                /* Check for type */
                if ($Info->type == 'css') {
                  /* Add style */
                  wp_enqueue_style('wpcdnkoloss-'.$Info->library.'-'.$Info->file.'-'.$Info->version, $this->CdnConnector->getAssetUrl($Info->library, $Info->version, $Info->file), array(), $Info->version);
                } else if ($Info->type == 'js') {
                  /* Check for footer inclusion */
                  if ($Info->position == 'footer')
                    $in_footer = true;
                  else
                    $in_footer = false;

                  /* Add style */
                  wp_enqueue_script('wpcdnkoloss-'.$Info->library.'-'.$Info->file.'-'.$Info->version, $this->CdnConnector->getAssetUrl($Info->library, $Info->version, $Info->file), array(), $Info->version, $in_footer);
                }
              }
            }
          }
        }
      }
    }
  }

  function loadIncludedFiles() {
    /* Setup added files */
    $this->included_files['result'] = $this->DbConnector->getCollection($this->plugin_table);

    /* Walk included files */
    if (!empty($this->included_files['result'])) {
      /* Walk */
      foreach ($this->included_files['result'] as $key => $File) {
        /* Set library - version - file */
        $this->included_files['ordered'][$File->library][$File->version][$File->file] = $File;
      }
    }

    /* Return */
    return $this->included_files;
  }

  public function getCdnConnector() {
    return $this->CdnConnector;
  }

  /**
   * WP Activation function
   */
  static function wpcdnkoloss_activation() {
    /* Add table holding activated cdn files */
    global $wpdb;
    $table = $wpdb->prefix . 'wpcdnkoloss_files';
    $prefix = 'wpcdnkoloss_';
    $sql = "CREATE TABLE " . $table . " (
  	  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
      library VARCHAR(255) NOT NULL,
  	  file VARCHAR(255) NOT NULL,
      version VARCHAR(255) NOT NULL,
      type VARCHAR(255) NOT NULL,
  	  position VARCHAR(255) NOT NULL,
  	  PRIMARY KEY " . $prefix . "files_id (id),
      KEY " . $prefix . "files_library (library),
      KEY " . $prefix . "files_file (file),
      KEY " . $prefix . "files_version (version),
      KEY " . $prefix . "files_type (type)
  	) CHARACTER SET utf8 COLLATE utf8_general_ci;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * WP Deactivation function
   */
  static function wpcdnkoloss_deactivation() {}
}

?>
