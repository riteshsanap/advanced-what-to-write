<?php 
/* 
Plugin Name: Advanced What to Write Next
Version: 1.0.3
Author: Ritesh Sanap
Description: A Plugin based and Inspired by "What Should We Write About Next" plugin by Vladimir prelovac, Which allows users to quickly leave feedback at the end of your posts.
Author URI: http://www.best2know.info/
Plugin URI: http://wpden.net/advanced-write-next-wordpress-plugin/
Text Domain: awtw_plugin
Domain Path: /lang
*/
/************************************************************************************/
/*	Plugin Version	*/		
/************************************************************************************/	
global $awtw_plugin_version;
$awtw_plugin_version = "1.0";

/************************************************************************************/
/*	Create Database Table for Plugin	*/		
/************************************************************************************/		
/**
 * Add necessary data to database for proper plugin functioning.
 *
 * @version 1.0
 * @since   AWTW 1.0
 * @author Ritesh Sanap <riteshsanap@gmail.com>
 *
 * @return  none
 */
function awtw_install() {

	global $awtw_plugin_version;

	// Run SQL Query to create a new Table
	Awtw_DB::createTable();

	// Add plugin version to Options table in WordPress
	add_option( "awtw_plugin_db_version", $awtw_plugin_version );

	// Default settings 
	$args = array(
	'awtw_label_text' => __('What should we write about next?', 'awtw_plugin'),
	'awtw_min_length' => 10,
	'awtw_num_entry' => 10,
	'awtw_post_content' => 1
	);

	// store in Options table
	update_option('awtw_options',$args);
}	
/**
 * When plugin is activated run awtw_install()
 */
register_activation_hook( __FILE__, 'awtw_install' );

function plugin_settings_page( $links ) {
	$settings_link = '<a href="admin.php?page=awtw-feedback-options&sub=settings">'.__( 'Settings', 'awtw_plugin' ).'</a>';
	array_unshift( $links, $settings_link );
	return $links;
}	

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'plugin_settings_page' );

/************************************************************************************/
/*	Plugin form HTML	*/		
/************************************************************************************/		
function awtw_plugin_html() {
	$options = get_option('awtw_options');

	$form_html = '<div class="awtw-quick-feedback-form">
				<div class="shadow3"></div>
				<span id="awtw-feedback-result"></span>
				<form id="awtw-feedback-form" method="POST">
				<label class="feedback_title">'.$options['awtw_label_text'].'</label>
				<label><input type="text" name="awtw-feedback-msg" id="awtw-feedback-msg"/>
				<input type="submit" class="button" value="Send" id="awtw-feedback-send-msg-btn"/></label>
				<input type="hidden" name="awtw-feedback-nonce" value="'.wp_create_nonce().'"/>
			</form>
			</div>';
			return $form_html;
}
/**
 * Attach Form HTML after post content.
 *
 * @version 1.0
 * @since   AWTW 1.0
 * @author Ritesh Sanap <riteshsanap@gmail.com>
 *
 * @param   string   $post 
 * @return  string  
 */
function awtw_plugin_html_content($post){
		if(is_single()){
			$form_html = awtw_plugin_html();
			return $post.$form_html;
		} 
		return $post;
}

$options = get_option('awtw_options'); // get Options

/**
 * If Attach to post content is enabled.
 * add the filter.
 */
if(isset($options['awtw_post_content'])) {
add_filter('the_content','awtw_plugin_html_content');	
}

/************************************************************************************/
/*	AJAX script	*/		
/************************************************************************************/
add_action('wp_enqueue_scripts','awtw_feedback_scripts');
function awtw_feedback_scripts(){
	if(is_single()){

		/* Get Options */
		$options = get_option('awtw_options');
		if(!is_array($options))
			$options = array();
			extract($options);

		$plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
		
		wp_enqueue_script('awtw-feedback-js', "$plugin_url/awtw-ajax.js", array('jquery'));  

		//Set params for JS
		$params = array('url' => admin_url( 'admin-ajax.php' ).'/awtw-ajax.php', 'minLenght' => $awtw_min_length);
		wp_localize_script( 'awtw-feedback-js', 'awtw_params', $params);
        
    wp_enqueue_style('awtw-feedback-css', "$plugin_url/awtw.css");
 
	}
} 

function awtw_plugin_ajax() {
$output ='
<script>
jQuery(document).ready(function($) {
	$("#awtw-feedback-form").submit(function(e) {
		e.preventDefault();
		var message = jQuery("#awtw-feedback-msg").val();
		$.post(ajaxurl, message, function(response){
			console.log(response);
		});
		console.log(ajaxurl);
	});
});
</script>';
return $output;
}
add_action('wp_enqueue_scripts', 'awtw_plugin_ajax');

/************************************************************************************/
/*	Admin Page Registration	*/		
/************************************************************************************/		
add_action('admin_menu','awtw_feedback_add_options');
function awtw_feedback_add_options(){        
	add_menu_page('Advanced What to write', 'What to Write', 'administrator', 'awtw-feedback-options', 'awtw_feedback_options_page');
}
function awtw_feedback_save_options($args){
		update_option('awtw_options',$args);
}	
function awtw_feedback_options_page(){
	echo '<div class="wrap">';
	echo '<h2>';
	_e('Advanced What to write next ?', 'awtw_plugin');
	echo '</h2>';

	echo '<ul class="subsubsub">'; ?>
	<li><a href="?page=<?php echo $_REQUEST['page']; ?>" class=" <?php echo !isset($_GET['sub']) ? ' current' : ''; ?>"><?php _e('Approved', 'awtw_plugin'); ?></a> <span class="count">(<?php echo Awtw_DB::count(2); ?>)</span>|</li>
	<li><a href="?page=<?php echo $_REQUEST['page']; ?>&sub=spams" <?php if(isset($_GET['sub']) && $_GET['sub'] == 'spams') echo 'class="current"'; ?>><?php _e('Spams', 'awtw_plugin'); ?></a> <span class="count">(<?php echo Awtw_DB::count(1); ?>)</span>|</li>
	<li><a href="?page=<?php echo $_REQUEST['page']; ?>&sub=pending" <?php if(isset($_GET['sub']) && $_GET['sub'] == 'pending') echo 'class="current"'; ?>><?php _e('Pending', 'awtw_plugin'); ?></a> <span class="count">(<?php echo Awtw_DB::count(0); ?>)</span>|</li>
	<li><a href="?page=<?php echo $_REQUEST['page']; ?>&sub=settings" <?php if(isset($_GET['sub']) && $_GET['sub'] == 'settings') echo 'class="current"'; ?>><?php _e('Settings', 'awtw_plugin'); ?></a></li>
	<?php echo '</ul>';

	if( isset($_GET['sub']) && $_GET['sub'] == 'settings') {
	
	$options = get_option('awtw_options');
	if(!is_array($options))
		$options = array();
	extract($options);
	$awtw_post_content = isset($awtw_post_content) ? 'checked' : ''; 
	

	if(isset($_POST['awtw_save_options'])){
		echo '<br/><br/><div id="message" class="updated"><p>';
		_e('Options updated.', 'awtw_plugin');
		echo '</p></div>';
		awtw_feedback_save_options($_POST);
	}

	?>
	<form method="POST">
		<table class="form-table">
			<tr>
				<th><label><?php _e('Label Text:', 'awtw_plugin'); ?></label></th>
				<td> <input type="text" name="awtw_label_text" value="<?php echo $awtw_label_text; ?>" />
					<p class="description"><?php _e('The Heading text shown above the form.', 'awtw_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label><?php _e('Minimum Length:', 'awtw_plugin') ?></label></th>
				<td> <input type="number" name="awtw_min_length" min="1" value="<?php echo $awtw_min_length; ?>" />
					<p class="description"><?php _e('Minimum Length of Characters Accepted in a Feedback. if the Minimum Length is not met then an "Feedback too Short!" error will be shown.', 'awtw_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label><?php _e('Number of Entries:', 'awtw_plugin'); ?></label></th>
				<td> <input type="number" name="awtw_num_entry"  min="1" value="<?php echo $awtw_num_entry; ?>" />
					<p class="description"><?php _e('The Amount of Feedback entries shown on Admin page (eg: Approved/Pending/Spam etc.)', 'awtw_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label><?php _e('Attach to Post content:', 'awtw_plugin'); ?></label></th>
				<td> <label><input type="checkbox" name="awtw_post_content" value="1" <?php echo $awtw_post_content; ?>/> Automatically Display below the Post.</label>
						<?php if($awtw_post_content == '') {
							echo ' <p class="description">';
							_e('Auto display is disabled. Please add the following code in <code>single.php</code>, where you want to display the form.<br/><code>&lt;?php echo awtw_plugin_html(); &gt;</code>', 'awtw_plugin');
							echo '</p>';
						}
						?> 
				</td>
			</tr>
			<tr>
				<th><label></label></th>
				<td><input type="submit" value="<?php _e('Save Settings', 'awtw_plugin'); ?>" name="awtw_save_options" class="button button-primary" /></td>
			</tr>
		</table>
	</form>
<p class="help description"><?php printf(__('Have a problem? Suggestion or Need Help, just %s contact me%s.', 'awtw_plugin'), '<a href="mailto:riteshsanap@gmail.com" target="_blank">', '</a>'); ?></p>
	
	<?php 	} // end isset settings page.
	echo '<form id="awtw_actions_bulk_action" method="POST">';
	echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';

	if( isset($_GET['sub']) && $_GET['sub'] != 'settings') {
			$table = new Awtw_Log_Table;
			$table->prepare_items($_GET['sub']);
			$table->display();

	} elseif(!isset($_GET['sub']) || $_GET['sub'] != 'settings') {
		$table = new Awtw_Log_Table;
		$table->prepare_items();
		$table->display();
	}
		echo '</form>';
		echo '</div>';
	}

/************************************************************************************/
/*	AWTW Styling CSS	*/		
/************************************************************************************/	
/**
 * Admin panel CSS
 *
 * @version 1.0
 * @since   AWTW 1.0
 * @author Ritesh Sanap <riteshsanap@gmail.com>
 *
 * @return  string 
 */
	function awtw_table_style() { ?>
		<style>
		#created_at {width: 12%;}
		#ip_address {width: 15%;}
		#source_url {width: 23%;}
		p.help{margin-top: 20px;font-size: 16px;}
		span.mark_unapprove a{color: #d98500;}
		span.mark_approve a{color: #006505;}
		tr.mark_spam{background: #fef7f1;}
		tr.mark_approve{background: #CCFFD1;}
		tr.delete{background: #FFD6CC;}
		tr.mark_pending{background: #CCFFFF;}
		#action_message{padding: 15px;}
		</style>
	<?php }				
	add_action('admin_head', 'awtw_table_style');

/************************************************************************************/
/*	AJAX functions for Admin	*/		
/************************************************************************************/		
function awtw_ajax_admin_js() { ?>
<script type="text/javascript" >
jQuery(document).ready(function($) {

	jQuery("div.row-actions span a").click(function(e) {
		e.preventDefault(); // prevents default action
		var self = $(this).parent().parent().parent().parent();
		var id = $(this).data("id");
		var mark_action = $(this).data("action");
		
		if (mark_action === 'delete') {
		 j =	confirm("<?php _e('Are you sure ? Once deleted, cannot be recovered.', 'awtw_plugin'); ?>");
		};

		if ((mark_action != 'delete') || ( (mark_action === 'delete') && (j === true) ) ) {
		var data = {
			'action' : 'awtw_actions',
			'actions' : mark_action,
			'id' : id
		}
		$.post(ajaxurl, data, function(response){
			self.addClass(mark_action).fadeOut("slow", function(){
				self.remove();
				});
			console.log(response);
		});
	};
	});
});
</script>

<?php } 

/**
 * Add AJAX script to the Footer of the Admin page.
 */
if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'awtw-feedback-options') {
	add_action( 'admin_footer', 'awtw_ajax_admin_js' );
}

/************************************************************************************/
/*	PHP function for AJAX requests from Admin Panel 	*/		
/************************************************************************************/		

// register awtw_ajax function for Admin panel.
add_action('wp_ajax_awtw_actions', 'awtw_ajax');

/**
 * Run various actions based on AJAX request
 *
 * @version 1.0
 * @since   AWTW 1.0
 * @author Ritesh Sanap <riteshsanap@gmail.com>
 *
 * @return  none
 */
function awtw_ajax() {
	$action = $_POST['actions'];
	$id = $_POST['id'];
	
 	switch ($action) {
 		case 'mark_spam':
 			// send as Spam
 			Awtw_Akismet::submit($id, 'spam');
 			break;
 		case 'mark_approve':
 			// send as a HAM
 			Awtw_Akismet::submit($id, 'ham');
 			break;
 		case 'mark_pending':
 			// Mark as pending
 			Awtw_DB::statusUpdate($id, 0);
 			break;
 		case 'delete':
 			// Function for delete
 			Awtw_DB::delete($id);
 			break;
 	}
}	

/************************************************************************************/
/*	Handle request sent by AJAX from front end	*/		
/************************************************************************************/		
function awtw_ajax_front() {
	global $wpdb; 
	$tablename = $wpdb->prefix. 'awtw_plugin';

	/**
	 * When nonce is verified, store all the data in the database and
	 * then Send it to Akismet to check whether it is spam or not.
	 */
	if( wp_verify_nonce($_POST['awtw-feedback-nonce']) ) {

		$feedback = $_POST['awtw-feedback-msg'];
		$source_url =  $_SERVER['HTTP_REFERER'];
		$ip_address = $_SERVER["REMOTE_ADDR"];
		$user_agent = $_SERVER["HTTP_USER_AGENT"];

	if(!empty($feedback)) {
		/**
		 * Insert all the data into the database.
		 */
		$rows_affected = $wpdb->insert( $tablename, array( 
			'ip_address' => $ip_address,
			'source_url' => $source_url,
			'agent' => $user_agent,
			'created_at' => current_time('mysql'),
			'status' => 0,
			'feedback' => stripslashes_deep($feedback),
			) ); 

			$result = Awtw_Akismet::check($wpdb->insert_id); // Check if it is SPAM !

			echo $result;

		//return true;
		die();
	} // End if !empty()

	return false;
	} // End if WP_verify_nonce()
}
/**
 * Add AJAX action for front end AJAX script
 */
add_action( 'wp_ajax_awtw_ajax_front', 'awtw_ajax_front' );
add_action( 'wp_ajax_nopriv_awtw_ajax_front', 'awtw_ajax_front' );

/************************************************************************************/
/*	Load Files	*/		
/************************************************************************************/		
	// Load all SQL queries
	require_once('awtw-sql.php');
	//Load Admin Table to display feedback
	require_once('awtw-table.php');
	// Load Akismet
	require_once('awtw-akismet.php');

/************************************************************************************/
/*	THE END. Thank you for everything 	*/		
/************************************************************************************/
?>