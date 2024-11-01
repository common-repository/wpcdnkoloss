<?php
/*
Plugin Name: wpCDNKoloss
Plugin URI: http://kolossum.io/wpcdnkoloss
Description: An plugin that let's you easily add and load files from <a href="https://cdnjs.com" target="_blank">cdnjs.com</a>
Version: 0.5
Author: Kolossum
Author URI: http://wearekolossum.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpcdnkoloss

wpcdnkoloss is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

wpcdnkoloss is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with wpcdnkoloss. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

/* If this file is called directly, abort. */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Check for already defined class */
if ( !class_exists( 'WpCdnKoloss' ) ) {
  /* Add class */
  require(dirname( __FILE__ ) . '/includes/WpCdnKoloss.php');
  $WpCdnKoloss = new WpCdnKoloss();

  /* Check for Admin */
  if ( is_admin() ) {
    require(dirname( __FILE__ ) . '/includes/WpCdnKolossAdmin.php');
    $WpCdnKolossAdmin = new WpCdnKolossAdmin();
  }

  /* Register activation hook */
  register_activation_hook(__FILE__, array('WpCdnKoloss', 'wpcdnkoloss_activation'));

  /* Register deactivation hook */
  register_deactivation_hook(__FILE__, array('WpCdnKoloss', 'wpcdnkoloss_deactivation'));
}

?>
