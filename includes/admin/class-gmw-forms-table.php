<?php
/**
 * GEO my WP Forms table.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * GMW_Forms_Table class.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_Forms_Table extends WP_List_Table {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Table columns.
	 *
	 * @return [type] [description]
	 */
	public function get_columns() {

		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'ID'        => _x( 'ID', 'Column label', 'geo-my-wp' ),
			'title'     => _x( 'Title', 'Column label', 'geo-my-wp' ),
			'type'      => _x( 'Type', 'Column label', 'geo-my-wp' ),
			'extension' => _x( 'Extension', 'Column label', 'geo-my-wp' ),
			'shortcode' => _x( 'Shortcode', 'Column label', 'geo-my-wp' ),
		);

		return $columns;
	}

	/**
	 * Sortable columns.
	 *
	 * @return [type] [description]
	 */
	protected function get_sortable_columns() {

		$sortable_columns = array(
			'ID'        => array( 'ID', false ),
			'title'     => array( 'title', false ),
			'type'      => array( 'type', false ),
			'extension' => array( 'extension', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Default column value.
	 *
	 * @param  [type] $item        [description].
	 *
	 * @param  [type] $column_name [description].
	 *
	 * @return [type]              [description]
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
			case 'type':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // phpcs:ignore.
		}
	}

	/**
	 * Column checkbox.
	 *
	 * @param  array $item record items.
	 *
	 * @return [type]       [description]
	 */
	protected function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['ID']
		);
	}

	/**
	 * Get title column value.
	 *
	 * @param  array $item record items.
	 *
	 * @return [type]       [description]
	 */
	protected function column_title( $item ) {

		$page    = ! empty( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '1'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		$actions = array();

		if ( ! gmw_is_addon_active( $item['extension'] ) ) {

			$actions['manage_extensions'] = sprintf(
				'<span style="color:#444">%1$s</span> <a href="%2$s">%3$s</a>',
				esc_attr__( 'Extension is inactive.', 'geo-my-wp' ),
				esc_url( 'admin.php?page=gmw-extensions' ),
				esc_attr__( 'Manage extenions.', 'geo-my-wp' ),
			);

			return sprintf(
				'<strong>%1$s</strong> %2$s',
				$item['title'],
				$this->row_actions( $actions )
			);
		}

		// Build edit row action.
		$edit_query_args = array(
			'page'       => $page,
			'gmw_action' => 'edit_form',
			'form_id'    => $item['ID'],
			// phpcs:disable.
			//'slug'       => $item['slug'],
			//'prefix'     => $item['prefix'],
			// phpcs:enable.
		);

		$edit_link  = esc_url( wp_nonce_url( add_query_arg( $edit_query_args, 'admin.php' ), 'editform_' . $item['ID'] ) );
		$edit_link .= '&current_tab=general_settings';

		$actions['edit'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$edit_link,
			_x( 'Edit', 'Forms table row action', 'geo-my-wp' )
		);

		// Build delete row action.
		$duplicate_query_args = array(
			'page'       => $page,
			'gmw_action' => 'duplicate_form',
			'form_id'    => $item['ID'],
			'slug'       => $item['slug'],
			'nonce'      => wp_create_nonce( 'gmw_duplicate_form' ),
		);

		$actions['duplicate'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $duplicate_query_args, 'admin.php' ), 'duplicateform_' . $item['ID'] ) ),
			_x( 'Duplicate', 'Forms table row action', 'geo-my-wp' )
		);

		// Build delete row action.
		$delete_query_args = array(
			'page'       => $page,
			'gmw_action' => 'delete_form',
			'form_id'    => $item['ID'],
			'nonce'      => wp_create_nonce( 'gmw_delete_form' ),
		);

		$delete_message = esc_attr__( 'This action cannot be undone. Would you like to proceed?', 'geo-my-wp' );

		$actions['delete'] = sprintf(
			'<a href="%1$s" onclick="return confirm( \'%2$s\' );">%3$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $delete_query_args, 'admin.php' ), 'deletform_' . $item['ID'] ) ),
			$delete_message,
			_x( 'Delete', 'Forms table row action', 'geo-my-wp' )
		);

		$form_title = sprintf(
			'<a href="%1$s"><strong>%2$s</strong></a>',
			$edit_link,
			! empty( $item['title'] ) ? $item['title'] : 'form_id_' . $item['ID']
		);

		// Return the title contents.
		return sprintf(
			'%1$s %2$s',
			$form_title,
			$this->row_actions( $actions )
		);
	}

	/**
	 * Get extension column value.
	 *
	 * @param  array $item record items.
	 *
	 * @return [type]       [description]
	 */
	protected function column_extension( $item ) {
		return ucwords( str_replace( '_', ' ', $item['extension'] ) );
	}

	/**
	 * Get shortcode column value.
	 *
	 * @param  array $item record items.
	 *
	 * @return [type]       [description]
	 */
	protected function column_shortcode( $item ) {

		$form_shortcode = '[gmw form="' . $item['ID'] . '"]';
		$form_shortcode = apply_filters( 'gmw_forms_page_form_shortcode', $form_shortcode, $item );
		$form_shortcode = apply_filters( 'gmw_forms_page_' . $item['extension'] . '_form_shortcode', $form_shortcode, $item );

		// '<span class="gmw-form-shortcode"><code>' . esc_attr( $form_shortcode ) . '</code><i class="gmw-shortcode-ctc gmw-icon-lifebuoy"></i></span>';

		return '<code>' . esc_attr( $form_shortcode ) . '</code>';
	}

	/**
	 * Bulk actions.
	 *
	 * @return [type] [description]
	 */
	protected function get_bulk_actions() {

		$actions = array(
			'delete' => _x( 'Delete', 'Forms table bulk action', 'geo-my-wp' ),
		);

		return $actions;
	}

	/**
	 * Process bulk actions.
	 *
	 * @return [type] [description]
	 */
	protected function process_bulk_action() {

		if ( 'delete' === $this->current_action() ) {

			if ( empty( $_POST['gmw_page'] ) || 'gmw-forms' !== $_POST['gmw_page'] || empty( $_POST['form'] ) || empty( $_POST['action'] ) || 'delete' !== $_POST['action'] ) { // phpcs:ignore CSRF ok.
				return;
			}

			// run a quick security check.
			if ( ! check_admin_referer( 'gmw_forms_page', 'gmw_forms_page' ) ) {
				wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) ); // phpcs:ignore: XSS ok.
			}

			global $wpdb;

			$db       = esc_sql( $wpdb->prefix . 'gmw_forms' );
			$form_ids = array_map( 'absint', $_POST['form'] );
			$form_ids = esc_sql( implode( ',', $form_ids ) );

			// phpcs:disable
			// delete forms from database.
			$wpdb->query( "DELETE FROM $db WHERE ID IN ( $form_ids )" ); // phpcs:ignore: db call ok, CSRF ok, cache ok, unprepared sql ok.
			// phpcs:enable

			// update forms in cache.
			GMW_Forms_Helper::update_forms_cache();

			// phpcs:ignore.
			// wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_deleted&gmw_notice_status=updated' ) );

			// phpcs:ignore.
			//exit;
		}
	}

	/**
	 * Prepare forms.
	 */
	public function prepare_items() {

		global $wpdb;

		// Per page.
		$per_page = 20;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$keywords = '';
		$orderby  = ( ! empty( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ), true ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'ID'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		$order    = ( ! empty( $_REQUEST['order'] ) && 'desc' === $_REQUEST['order'] ) ? 'desc' : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		if ( ! empty( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$search   = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$escaped  = '%' . $wpdb->esc_like( $search ) . '%';
			$keywords = $wpdb->prepare(
				'WHERE id LIKE %s
				OR name LIKE %s
				OR title LIKE %s
				OR slug LIKE %s
				OR component LIKE %s
				OR addon LIKE %s',
				$escaped,
				$escaped,
				$escaped,
				$escaped,
				$escaped,
				$escaped,
			);
		}

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		// phpcs:disable
		// Get forms from database.
		$data = $wpdb->get_results(
			"SELECT ID, title, name as type, addon as extension, slug, prefix
			FROM {$wpdb->prefix}gmw_forms
			$keywords
			ORDER BY $orderby $order",
			ARRAY_A
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, cache ok, unprepared sql ok.
		// phpcs:enable

		// Current page.
		$current_page = $this->get_pagenum();

		// Total forms.
		$total_items = count( $data );

		// Data to display.
		$this->items = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                     // WE have to calculate the total number of items.
				'per_page'    => $per_page,                        // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			)
		);
	}

	/**
	 * Generate the search forms box.
	 *
	 * @param string $text search button label.
	 *
	 * @param string $input_id input box ID.
	 */
	public function search_box( $text, $input_id ) {

		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		}
		if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		}

		if ( ! empty( $_REQUEST['detached'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) . '" />'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		}
		?>
		<p class="search-box gmw-form-search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
			<input type="text" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php
			submit_button(
				$text,
				'',
				'',
				false,
				array( 'id' => 'search-submit' )
			);
			?>
		</p>
		<?php
	}

	/**
	 * No forms found message.
	 */
	public function no_items() {
		esc_attr_e( 'No forms found.', 'geo-my-wp' );
	}
}
