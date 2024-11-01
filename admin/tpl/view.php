<?php

  $WpCdnKolossAdmin = new WpCdnKolossAdmin();

?>

<div class="wrap wpcdnkoloss">
  <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
  <p>Search the database from <a href="https://cdnjs.com" target="_blank">cdnjs.com</a> and add libraries as easy as it goes</p>
  <div class="card full-width">
    <h2><?php echo __('Search cdnjs.com database'); ?></h2>
    <form action="" method="post" data-wpcdnkoloss-context="search-cdn" class="wpcdnkoloss-search-form">
        <?php wp_nonce_field('wpcdnkoloss-search-cdn'); ?>
        <input type="text" value="" class="regular-text" data-wpcdnkoloss-field="cdn-search-string" placeholder="<?php echo __('Type in the name of the library you are searching...'); ?>" />
        <button class="button" disabled="disabled">
          <div class="spinner"></div>
          <span><?php echo __('Search cdnjs.com'); ?></span>
        </button>
    </form>
  </div>
  <div class="card full-width hidden" data-wpcdnkoloss-context="search-result">
    <div class="wpcdnkoloss-search-result">
      <h2><?php echo _('Search result for'); ?> <span data-wpcdnkoloss-placeholder="search_string"></span> (Results: <span data-wpcdnkoloss-placeholder="total_results"></span>)</h2>
      <div class="wpcdnkoloss-search-result-list" data-wpcdnkoloss-context="search-result-list">
        <ul></ul>
      </div>
      <div class="wpcdnkoloss-search-result-detail" data-wpcdnkoloss-context="search-result-detail" data-wpcdnkoloss-nonce="<?php echo wp_create_nonce('wpcdnkoloss-get-library-detail'); ?>"></div>
    </div>
  </div>
  <div class="card full-width">
    <div class="wpcdnkoloss-files-list" data-wpcdnkoloss-context="files-list" data-wpcdnkoloss-nonce="<?php echo wp_create_nonce('wpcdnkoloss-files-list'); ?>">
      <h2><?php echo _('Included files'); ?></h2>
      <table class="wp-list-table widefat">
      	<thead>
        	<tr>
        		<th class="row-title"><?php echo __('Library'); ?></th>
        		<th class="row-title"><?php echo __('File'); ?></th>
            <th class="row-title"><?php echo __('Version'); ?></th>
            <th class="row-title" style="width: 10%;"><?php echo __('Include in'); ?></th>
            <th class="row-title" style="width: 20%;"></th>
        	</tr>
      	</thead>
      	<tbody>
          <?php echo $WpCdnKolossAdmin->getIncludedFilesTable(); ?>
      	</tbody>
      	<tfoot>
        	<tr>
            <th class="row-title"><?php echo __('Library'); ?></th>
        		<th class="row-title"><?php echo __('File'); ?></th>
            <th class="row-title"><?php echo __('Version'); ?></th>
            <th class="row-title"><?php echo __('Include in'); ?></th>
            <th class="row-title"></th>
        	</tr>
      	</tfoot>
      </table>
    </div>
  </div>
  <div class="hidden" data-wpcdnkoloss-context="templates">
    <div>
      <li class="search-cdn-result-item" data-wpcdnkoloss-template="search-cdn-result-li">
        <div class="item-wrapper">
          <a href="#showDetails" class="show-details" data-wpcdnkoloss-library="" data-wpcdnkoloss-context="show-details">
            <div class="spinner"></div>
            <span class="name" data-wpcdnkoloss-placeholder="name"></span>
            <i class="material-icons">chevron_right</i>
          </a>
        </div>
      </li>
    </div>
    <div>
      <div class="search-cdn-result-detail-item" data-wpcdnkoloss-template="search-cdn-result-detail-item">
        <div class="item-wrapper">
          <div class="item-header">
            <h2 data-wpcdnkoloss-placeholder="name"></h2>
            <p data-wpcdnkoloss-placeholder="description"></p>
            <p data-wpcdnkoloss-placeholder="homepage"><a href=""><?php echo __('Homepage'); ?></a></p>
          </div>
          <div class="item-assets">
            <div class="item-assets-header">
              <h3><?php echo __('Available assets'); ?></h3>
              <div class="choose-version" data-wpcdnkoloss-context="choose-library-version">
                <label><?php echo __('Version'); ?>: </label>
                <select name="library-version"></select>
              </div>
            </div>
            <div class="item-assets-list" data-wpcdnkoloss-context="assets-list" data-wpcdnkoloss-nonce="<?php echo wp_create_nonce('wpcdnkoloss-handle-file'); ?>">
              <ul></ul>
            </div>
        </div>
      </li>
    </div>
  </div>
  <div>
    <li class="asset-list-item" data-wpcdnkoloss-template="asset-list-item-li">
      <div class="item-wrapper">
        <div class="item-wrapper-name">
          <span class="name" data-wpcdnkoloss-placeholder="name"></span>
        </div>
        <div class="item-wrapper-include">
          <button class="button-primary" disabled="disabled" data-wpcdnkoloss-context="include-file" data-wpcdnkoloss-file data-wpcdnkoloss-file-id>
            <div class="spinner"></div>
            <span><?php echo __('Include' ); ?></span>
          </button>
          <button class="button-secondary hidden" data-wpcdnkoloss-context="remove-file" data-wpcdnkoloss-file data-wpcdnkoloss-file-id>
            <div class="spinner"></div>
            <span><?php echo __('Remove'); ?></span>
          </button>
        </div>
        <div class="item-wrapper-include-in">
          <span class="include-in"><?php echo __('Include at'); ?> </span>
          <select name="include-in" data-wpcdnkoloss-context="include-in">
            <option value=""><?php echo __('No inclusion') ?></option>
            <option value="footer"><?php echo __('Footer') ?></option>
            <option value="header"><?php echo __('Header') ?></option>
          </select>
        </div>
      </div>
    </li>
  </div>
</div>
