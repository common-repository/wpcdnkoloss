<?php

/* Require loader */

class WpCdnKolossAdmin extends WpCdnKoloss {
  /**
   * Constructor
   */
  function __construct() {
    /* Call parent class constructor */
    parent::__construct();

    /* Add menu page */
    add_action('admin_menu', array($this, 'addSubMenuPage'));

    /* Enqueue scripts */
    add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
    add_action('wp_ajax_searchCdnAjax', array($this, 'searchCdnAjax'));
    add_action('wp_ajax_getLibraryDetailAjax', array($this, 'getLibraryDetailAjax'));
    add_action('wp_ajax_addFileAjax', array($this, 'addFileAjax'));
    add_action('wp_ajax_removeFileAjax', array($this, 'removeFileAjax'));
    add_action('wp_ajax_getIncludedFilesTableAjax', array($this, 'getIncludedFilesTableAjax'));
    add_action('wp_ajax_updateIncludedFilesAjax', array($this, 'updateIncludedFilesAjax'));
  }

  /* Add menu */
  function addSubMenuPage() {
    add_theme_page(
      'wpCdnKoloss',
      'wpCdnKoloss',
      'manage_options',
      $this->plugin_dir . 'admin/tpl/view.php',
      null
    );
  }

  /* Enqueue Scripts */
  function enqueueAdminScripts() {
    /* Enqueue */
    wp_enqueue_style('wpcdnkolossadmin-material-icons', $this->plugin_url . '/admin/css/styles.css');
    wp_enqueue_style('wpcdnkolossadmin-admin', '//fonts.googleapis.com/icon?family=Material+Icons');
    wp_enqueue_script('wpcdnkolossadmin-admin', $this->plugin_url . '/admin/js/app.js', array('jquery'));

    /* Localize data */
    wp_localize_script('wpcdnkolossadmin-admin', 'files', $this->included_files['ordered']);
  }

  /* Search CDN function */
  function searchCdnAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-search-cdn', 'security');

    /* Search cdn */
    $result = $this->CdnConnector->searchCdn($_POST['search_string']);

    /* send result */
    echo $result;

    /* Die for correct end */
    die();
  }

  /* Search CDN function */
  function getLibraryDetailAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-get-library-detail', 'security');

    /* Search cdn */
    $result = $this->CdnConnector->getLibraryDetail($_POST['search_string']);

    /* send result */
    echo $result;

    /* Die for correct end */
    die();
  }

  /* Add file to the indluded ones */
  function addFileAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-handle-file', 'security');

    /* Setup data */
    $data = array (
      'file' => $_POST['file'],
      'library' => $_POST['library'],
      'position' => $_POST['position'],
      'version' => $_POST['version']
    );

    /* Get type */
    $type = explode('.', $_POST['file']);
    $type = $type[count($type)-1];
    $data['type'] = $type;

    /* Check for id */
    if ($_POST['id'])
      $where = array('id' => $_POST['id']);
    else
      $where = null;

    /* Include file */
    echo $this->DbConnector->save($this->plugin_table, $data, $where);

    /* Die for correct end */
    die();
  }

  /* Add file to the indluded ones */
  function removeFileAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-handle-file', 'security');

    /* Setup data */
    $data = array (
      'id' => $_POST['id']
    );

    /* Include file */
    echo $this->DbConnector->delete($this->plugin_table, $data);

    /* Die for correct end */
    die();
  }

  /* Get included files table with AJAX */
  function getIncludedFilesTableAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-files-list', 'security');

    /* Get files list */
    echo $this->getIncludedFilesTable();

    /* Die for correct end */
    die();
  }

  /* Update included files */
  function updateIncludedFilesAjax() {
    /* Check nonce */
    check_ajax_referer('wpcdnkoloss-files-list', 'security');

    /* Update included files */
    $files = $this->loadIncludedFiles();

    /* Return */
    echo json_encode($files);

    /* Die for correct end */
    die();
  }

  /* Get included files table */
  function getIncludedFilesTable() {
    $table = '';

    if (empty($this->included_files['ordered'])):
      $table = '<tr>
        <td class="row-title" colspan="5">'.__('No files included.').'</td>
      </tr>';
    else:
      ksort($this->included_files['ordered']);
      $libCount = 0;

      foreach ($this->included_files['ordered'] as $library => $version):
        foreach ($version as $_version => $files):
          $fileCount = 0;

          foreach ($files as $file => $Info):
            $table .= '<tr'.(!($libCount%2) ? ' class="alternate"' : '').'></tr>
            <td class="row-title">'.$Info->library.'</td>
            <td>'.$this->getCdnConnector()->getAssetUrl($Info->library, $Info->version, $Info->file).'</td>
            <td>'.$Info->version.'</td>
            <td>
              <select name="include-in" data-wpcdnkoloss-context="include-in">
                <option value=""'.(!$Info->position ? ' selected="selected"': '').'>'.__('No inclusion').'</option>';

            if ($Info->type != 'css'):
              $table .= '<option value="footer"'.($Info->position == 'footer' ? ' selected="selected"': '').'>'.__('Footer').'</option>';
            endif;

            $table .= '<option value="header"'.($Info->position == 'header' ? ' selected="selected"': '').'>'.__('Header').'</option>
                </select>
              </td>
              <td align="right">
                <button class="button-primary" disabled="disabled" data-wpcdnkoloss-context="include-file" data-wpcdnkoloss-file="'.$Info->file.'" data-wpcdnkoloss-file-id="'.$Info->id.'" data-wpcdnkoloss-file-library="'.$Info->library.'" data-wpcdnkoloss-file-version="'.$Info->version.'">
                  <div class="spinner"></div>
                  <span>'.__('Update' ).'</span>
                </button>
                <button class="button-secondary" data-wpcdnkoloss-context="remove-file" data-wpcdnkoloss-file="'.$Info->file.'" data-wpcdnkoloss-file-id="'.$Info->id.'" data-wpcdnkoloss-file-library="'.$Info->library.'" data-wpcdnkoloss-file-version="'.$Info->version.'">
                  <div class="spinner"></div>
                  <span>'.__('Remove').'</span>
                </button>
              </td>
            </tr>';

            $fileCount++;
          endforeach;
        endforeach;

        $libCount++;
      endforeach;
    endif;

    return $table;
  }
}



?>
