<?php
/**
* Plugin Name: Test DB
*/

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_DB {

	public $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * @return mysqli_result|bool
	 */
	public function query( $query ) {

		$query = trim( $query );

		if ( ! $query ) {
			return;
		}

		$dbh = $this->wpdb->dbh;

		if ( ! $dbh ) {
			return;
		}

		if ( $this->wpdb->use_mysqli ) {
			return \mysqli_query( $dbh, $query ); // @phpcs:ignore
		}

		if ( function_exists( 'mysql_query' ) ) {
			return \mysql_query( $query, $dbh ); // @phpcs:ignore
		}
	}

}

function test_db_custom_page() {
	add_menu_page(
		'Test DB',
		'Test DB',
		'manage_options',
		'test-db',
		function() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$test_db = new Test_DB();

			if ( empty( $_POST['test_db_query'] ) ) {
				?>
				<div class="wrap">
					<form method="post">
						<textarea name="test_db_query"></textarea>

						<?php wp_nonce_field( '_test_db_query_nonce', 'test_db_query_nonce' ); ?>

						<br>

						<input type="submit" value="Submit">

						<br>

						<?php echo "DB Name: " . $test_db->wpdb->dbname; ?>

						<br>

						<?php echo "Prefix: " . $test_db->wpdb->prefix; ?>

					</form>
				</div>
				<?php
				return;
			}

			if ( ! wp_verify_nonce( $_POST['test_db_query_nonce'], '_test_db_query_nonce' ) ) {
				return;
			}

			$result = $test_db->query( wp_unslash( $_POST['test_db_query'] ) );

			echo '<pre>';
			print_r(wp_json_encode( $result->fetch_all(), JSON_PRETTY_PRINT ));
			echo '</pre>';

			$result->free_result();

			die;
		},
		'',
		6
	);
}

function test_db_init() {
	add_action( 'admin_menu', 'test_db_custom_page' );
}
add_action( 'plugins_loaded', 'test_db_init' );
