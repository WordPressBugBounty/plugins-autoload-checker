<?php
/*
Plugin Name: Autoload Checker
Version: 1.0
Description: Checks the autoloaded data size and lists the top autoloaded data entries sorted by size.
Author: Gerard Blanco
Author URI: https://accelerawp.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: autoload-checker
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

#
# Main Class: GRBLNC_Autoload_Checker - Solid prefix to avoid conflicts with other plugins
#

if( !class_exists( 'GRBLNC_Autoload_Checker' ) ){
	
	class GRBLNC_Autoload_Checker{
		
		function __construct(){
			// Call autoload_size_menu function to load plugin menu in dashboard
			add_action( 'admin_menu', [ $this, 'autoload_size_menu' ] );		
		}

		// Create WordPress admin menu
		function autoload_size_menu() {
			$parent_slug = 'tools.php';
			$page_title  = 'Autoload Checker';
			$menu_title  = 'Autoload Checker';
			$capability  = 'manage_options';
			$menu_slug	 = 'autoload-checker';
			$function	 = [ $this, 'autoload_checker' ];

			add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		}

		// Create WordPress plugin page
		function autoload_checker() {
			?>
			<div class="wrap">
				<h1><?php echo esc_html__( 'Total Autoload Size','autoload-checker' ); ?></h1>
				<?php
				global $wpdb;

				//Get table prefix
				$options_table = $wpdb->prefix . 'options';

				//Get total size
				$result =  
					$wpdb->get_results(
						$wpdb->prepare(
							"SELECT SUM( LENGTH( option_value ) / 1024.0 ) as autoload_size FROM %i WHERE autoload = 'yes'",
							$options_table
						),
						
					);		
				
				//Get top 20 autoloads
				$autoload_toplist =  
					$wpdb->get_results(
						$wpdb->prepare(
							"SELECT option_name, LENGTH( option_value ) / 1024.0 AS option_value_length FROM %i WHERE autoload = 'yes' ORDER BY option_value_length DESC LIMIT 20",
							$options_table
						),
						
					);

				//Show total
				foreach( $result as $object => $uno ) {
					$size = round( $uno->autoload_size ) . ' KB';
					?>
					<p style="font-weight:bold;font-size:1.5em;"><?php echo esc_html( $size ); ?></p>
					<?php
				}

				//Show top list
				?>
				<h2><?php echo esc_html__( 'Autoload top list:','autoload-checker' ); ?></h2>
				<table style="max-width:600px;" class="widefat striped">
					<thead><tr>
						<th scope="col"><?php echo esc_html__( '#','autoload-checker' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Option name','autoload-checker' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Size','autoload-checker' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $autoload_toplist as $k => $v ) : 
							$index			= $k + 1;
							$option_name 	= $v->option_name;
							$size 			= round( $v->option_value_length ) . ' KB';
						?>
						<tr>
							<td><?php echo esc_html( $index ); ?></td>
							<td><?php echo esc_html( $option_name ); ?></td>
							<td><?php echo esc_html( $size ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}


	# Run the class
	new GRBLNC_Autoload_Checker;

}
?>
