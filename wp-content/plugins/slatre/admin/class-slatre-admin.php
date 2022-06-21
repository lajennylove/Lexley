<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/slatre/
 * @since      1.0.0
 *
 * @package    Slatre
 * @subpackage Slatre/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Slatre
 * @subpackage Slatre/admin
 * @author     javmah <jaedmah@gmail.com>
 */
class Slatre_Admin {

	/**
	 * The ID of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 *  trello Application key of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $key    trello Application key of this plugin.
	*/
	private $key = '7385fea630899510fd036b6e89b90c60';
	
	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $active_plugins;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $formID_titles = array();

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $form_fields = array();

	/**
	 * Form field types.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $form_field_types = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 		= $plugin_name;
		$this->version 	  		= $version;
		$this->active_plugins 	= get_option('active_plugins');

		# For Ninja Form
		$ninja = $this->ninja_forms_and_fields();
		if ( $ninja[0] ) {

			foreach ( $ninja[1] as $form_id => $form_name ) {
				$this->formID_titles[ $form_id ] = $form_name;
			}

			foreach ($ninja[2] as $form_id => $fields_array) {
				$this->form_fields[ $form_id ] = $fields_array;
			}

			foreach ($ninja[3] as $form_id => $field_type) {
				$this->form_field_types[ $form_id ] = $field_type;
			}
		}

		# formidable form
		$formidable = $this->formidable_forms_and_fields();
		if ( $formidable[0] ) {

			foreach ( $formidable[1] as $form_id => $form_name ) {
				$this->formID_titles[$form_id] = $form_name;
			}

			foreach ( $formidable[2] as $form_id => $fields_array ) {
				$this->form_fields[$form_id] = $fields_array;
			}

			foreach ( $formidable[3] as $form_id => $field_type ) {
				$this->form_field_types[$form_id] = $field_type;
			}
		}

		# wpforms-lite/wpforms.php
		$wpforms = $this->wpforms_forms_and_fields();
		if ( $wpforms[0] ) {
			foreach ( $wpforms[1] as $form_id => $form_name) {
				$this->formID_titles[ $form_id ] = $form_name;
			}

			foreach ( $wpforms[2] as $form_id => $fields_array) {
				$this->form_fields[ $form_id ] = $fields_array;
			}

			foreach ( $wpforms[3] as $form_id => $field_type) {
				$this->form_field_types[ $form_id ] = $field_type;
			}
		}

		# Bangladeshi forms 
		# we forms-lite/wpforms.php
		$weforms = $this->weForms_forms_and_fields();
		if ( $weforms[0] ) {
			
			foreach ( $weforms[1] as $form_id => $form_name ) {
				$this->formID_titles[ $form_id] = $form_name;
			}

			foreach ( $weforms[2] as $form_id => $fields_array ) {
				$this->form_fields[ $form_id] = $fields_array;
			}

			foreach ( $weforms[3] as $form_id => $field_type ) {
				$this->form_field_types[ $form_id ] = $field_type;
			}

		}

	}

	/**
	 * Register the stylesheets for the admin area.
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/slatre-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/slatre-admin.js', array( 'jquery' ), $this->version, false );
		$slatre_data = array(
							'slatreAjaxURL'	=> admin_url( 'admin-ajax.php' ),
							'security' 		=> wp_create_nonce( 'wootrello-ajax-nonce' )
							);
		wp_localize_script( $this->plugin_name, 'slatre_data', $slatre_data );
	}
	
	/**
	 *  Plugin main Menu page || Nothing important here just another WP function s
	 *  @since    1.0.0
	*/
	public function slatre_menu_pages( ) {
		add_menu_page( __('Forms to Slack & Trello', 'slatre'), __('Forms to Slack & Trello', 'slatre'), 'manage_options', 'slatre', array($this, 'slatre_settings_view'), 'dashicons-share');
	}

	/**
	 *  View Page of the Menu Also Save Form Inputs!
	 *  @since    1.0.0
	*/
	public function slatre_settings_view( ) {

		# Save Submitted Form Specific Settings 
		if ( isset( $_POST['actionFrom'], $_POST['formKey'] ) &&  $_POST['actionFrom'] == 'formSettings'  ){

			$data = array();
			$data["slackStatus"]	= ( isset( $_POST['slackStatus'] ) && $_POST['slackStatus'] == 'on' ) ? TRUE : FALSE ;
			$data["slackWebHookUrl"]= esc_url( $_POST['slackWebHookUrl'] );  // WP change request done 
			$data["trelloStatus"]  	= ( isset( $_POST['trelloStatus'] ) && $_POST['trelloStatus'] == 'on' ) ? true : FALSE ;
			$data["trelloBoard"]	= wp_strip_all_tags( $_POST['slatreTrelloBoard'] );
			$data["trelloListID"]	= wp_strip_all_tags( $_POST['slatreTrelloListID'] );
			$data["trelloLabel"]	= wp_strip_all_tags( $_POST['slatreTrelloColour'] );
			$data["trelloDue"]		= wp_strip_all_tags( $_POST['slatreTrelloDue'] );
			$data["formName"]		= $this->formID_titles[ $_POST['formKey'] ];
			
			$postData = array(
				'post_title'	=> wp_strip_all_tags( $_POST['formKey'] ),
				'post_content'	=> json_encode( $data ),
				// 'post_excerpt'	=> json_encode( $_POST['fields'] ),  # WP Change Request 
				'post_excerpt'	=> json_encode( array_map( 'wp_strip_all_tags', $_POST['fields'] ) ),  # WP Change Request 
				'post_status'	=> 'publish',
				'post_type'		=> 'slatre'
			);

			$saved_entries   =   get_page_by_title( $_POST['formKey'],  $output = OBJECT, $post_type = 'slatre' );
			
			if ( !empty( $saved_entries ) && $saved_entries  ){

				$postData['ID'] = $saved_entries->ID  ;
				$r = wp_update_post( $postData );
				if( $r ){
					// Success || save to log
				}
			} else {

				$r = wp_insert_post( $postData );
				if( $r ){
					// Success || save to log 
				}
			}
		}

		# Save trello credentials || trello API key 
		if( isset( $_POST['actionFrom'] ) &&  $_POST['actionFrom'] == 'credentials'  ){

			if ( isset($_POST['slatre_trelloApiKey']) ) {
				update_option("slatre_trelloApiKey", wp_strip_all_tags( $_POST['slatre_trelloApiKey'] ) );
			}
		}

		# Getting Trello API Key 
		$trelloApiKey =  get_option( 'slatre_trelloApiKey' );
		# Empty Holder for Saved Form Specific Settings 
		$entries 	  = array();
		# getting Database entry
		$db_entries   =  get_posts(array('post_type' => 'slatre', 'posts_per_page' => -1));
		# Looping The database entries And Inserting to the $entries holders Arr
		foreach ( $db_entries as $entry ) {
			$entries[ $entry->post_title ]['FormID'] 	=  $entry->post_title ;
			$entries[ $entry->post_title ]['fields'] 	=  json_decode( $entry->post_excerpt , TRUE );
			$settings = array();
			$settings = json_decode( $entry->post_content , TRUE );
			$entries[ $entry->post_title ]['settings'] 	=  $settings; 

			# 
			if( isset( $settings ['slackStatus'] )  &&  !empty( $settings ['slackStatus'] )   ){
				$entries[ $entry->post_title ]['slackStatus'] 	= $settings['slackStatus'];
			}else{
				$entries[ $entry->post_title ]['slackStatus'] 	= FALSE ;
			}

			#
			if( isset( $settings ['slackWebHookUrl'] )  &&  !empty( $settings ['slackWebHookUrl'] )   ){
				$entries[ $entry->post_title ]['slackWebHookUrl'] 	= $settings['slackWebHookUrl'];
			}else{
				$entries[ $entry->post_title ]['slackWebHookUrl'] 	= '' ;
			}

			#
			if( isset( $settings ['trelloStatus'] )  &&  !empty( $settings ['trelloStatus'] )   ){
				$entries[ $entry->post_title ]['trelloStatus'] 	= $settings['trelloStatus'];
			}else{
				$entries[ $entry->post_title ]['trelloStatus'] 	= FALSE ;
			}

			# 
			if( isset( $settings ['trelloBoard'] )  &&  !empty( $settings ['trelloBoard'] )  ){
				
				$list =  $this->slatre_board_lists( $trelloApiKey, $settings['trelloBoard']);
				if( $list[0] ){
					$entries[ $entry->post_title ]['TrelloList'] = $list[1];
				}
				$entries[ $entry->post_title ]['trelloBoard'] 	 = $settings['trelloBoard'];
				
			}else{
				$entries[ $entry->post_title ]['trelloBoard']    = '' ;
			}

			# 
			if( isset( $settings ['trelloListID'] )  &&  !empty( $settings ['trelloListID'] )   ){
				$entries[ $entry->post_title ]['trelloListID'] 	= $settings['trelloListID'];
			}else{
				$entries[ $entry->post_title ]['trelloListID'] 	= '' ;
			}

			# 	
			if( isset( $settings ['trelloLabel'] )  &&  !empty( $settings ['trelloLabel'] )   ){
				$entries[ $entry->post_title ]['trelloLabel'] 	= $settings['trelloLabel'];
			}else{
				$entries[ $entry->post_title ]['trelloLabel'] 	= '' ;
			}

			# 
			if( isset( $settings ['trelloDue'] )  &&  !empty( $settings ['trelloDue'] )   ){
				$entries[ $entry->post_title ]['trelloDue'] 	= $settings['trelloDue'];
			}else{
				$entries[ $entry->post_title ]['trelloDue'] 	= '' ;
			}
		}

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/slatre-settings-display.php';
	}

	/**
	 *  Contact form 7,  Inserter || if Possible Change it on later Iteration 
	 * Insert CF7 fields on Global Holder;
	*/
	public function slatre_settings_notice(  ) {
		# Contact Form 7
		$cf7 = $this->cf7_forms_and_fields();
		if ( $cf7[0] ) {
			# inserting Forms 
			foreach ($cf7[1] as $form_id => $form_name) {
				$this->formID_titles[$form_id] = $form_name;
			}

			# inserting form Fields 
			foreach ($cf7[2] as $form_id => $fields_array) {
				$this->form_fields[$form_id] = $fields_array;
			}

			# inserting to Fields Type
			foreach ($cf7[3] as $form_id => $field_type) {
				$this->form_field_types[$form_id] = $field_type;
			}
		}

		# Testing Area Starts 
	}

	/**
	 *  Contact form 7,  form  fields 
	 *  @param    int     $user_id     		username
	 *  @param    int     $old_user_data   	username
	 *  @since    1.0.0
	*/
	public function cf7_forms_and_fields( ){
		if ( ! in_array('contact-form-7/wp-contact-form-7.php' , $this->active_plugins ) ) {
			return array(FALSE, " Contact form 7 is Not Installed " );
		}

		$cf7forms 			= array();
		$fieldsArray 		= array();																							# Final Output Holder Array ;
		$fieldTypeArray 	= array();																							# Final Output Holder Array ;
		$cf7Forms 			= get_posts( array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1) );					# Getting CF7 Custom Post ;
		
		foreach ( $cf7Forms as $form ) {																						# Loop the Custom Post ;
			if ( $form->ID ){	

				$ContactForm 		= WPCF7_ContactForm::get_instance( $form->ID  );
				$form_fields 		= $ContactForm->scan_form_tags();
				# inserting data to holder array
				$cf7forms[ "cf7_" . $form->ID ] = "Cf7 - " . $form->post_title;	
				# Looping the Fields 
				foreach ( $form_fields as $obj ) {
					
					$field_list  = array("text", "tel", "email", "textarea", "phone");

					if ( in_array( $obj->basetype, $field_list  ) ){
						$fieldsArray[ "cf7_". $form->ID ][ $obj->name ]    =  $obj->name;
					}
					//Inserting field type
					if( $obj->name ){
						$fieldTypeArray[ "cf7_". $form->ID ][ $obj->name ]  =   $obj->basetype;
					}
				}	
			}
		}

		return array( TRUE, $cf7forms, $fieldsArray, $fieldTypeArray );
	}


	/**
	 *  Ninja  form  fields 
	 *  @param     int     $user_id     username
	 *  @param     int     $old_user_data     username
	 *  @since    1.0.0
	*/
	public function ninja_forms_and_fields( ) {
		
		if ( ! in_array('ninja-forms/ninja-forms.php', $this->active_plugins ) ) {
			return array( FALSE, " Ninja form 7 is Not Installed "  );
		}
		global $wpdb;	
		$FormArray 	 	= array();																								# Empty Array for Value Holder 
		$fieldsArray 	= array();	
		$fieldTypeArray = array();	
		
		$ninjaForms 	= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nf3_forms", ARRAY_A);
		foreach ($ninjaForms as $form ) {
			$FormArray[ "ninja_". $form["id"] ] = "Ninja - ". $form["title"];	
			$ninjaFields 	=  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nf3_fields where parent_id = '".$form["id"]."'", ARRAY_A);
			foreach ($ninjaFields as $field) {
				
				$field_list = array("textbox", "email", "textarea", "phone");

				if( in_array( $field["type"], $field_list  ) ){
					$fieldsArray[ "ninja_". $form["id"] ] [ $field["key"] ] = $field["label"];
				}
				//Inserting field type
				$fieldTypeArray[ "ninja_". $form["id"] ] [ $field["key"] ] = $field["type"];

			}
		}
		
		return array( TRUE, $FormArray, $fieldsArray, $fieldTypeArray  );
	}
 
	/**
	 *  formidable form  fields 
	 *  @param     int     $user_id     username
	 *  @param     int     $old_user_data     username
	 *  @since    1.0.0 formidable-pro.php
	*/
	public function formidable_forms_and_fields( ){
		
		if ( ! in_array( 'formidable/formidable.php', $this->active_plugins ) ) {
			return array( FALSE, " formidable form 7 is Not Installed "  );
		}
		
		global $wpdb;
		$FormArray 	 	= array();																									# Empty Array for Value Holder 
		$fieldsArray 	= array();		
		$fieldTypeArray = array();																							# Empty Array for Holder 
		$frmForms 	    = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}frm_forms");														# Getting  Forms Database 
		
		foreach ( $frmForms as $form ) {
			$FormArray["frm_".$form->id] =  "Formidable - " . $form->name ;														# Inserting ARRAY title 
			# Getting Meta Fields || maybe i don't Know ;-D
			$fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}frm_fields WHERE form_id= " . $form->id . " ORDER BY field_order"); 	# Getting  Data from Database 
			foreach ($fields as $field) {
				
				$field_list = array("text", "email", "textarea", "phone");
				
				if( in_array( $field->type, $field_list  ) ){
					$fieldsArray["frm_".$form->id][$field->id] = $field->name;
				}
				//Inserting field type
				$fieldTypeArray["frm_".$form->id][$field->id] = $field->type;
			
			}
		}

		return array( TRUE, $FormArray, $fieldsArray, $fieldTypeArray  );																	    # Inserting Data to the Main [$eventsAndTitles ] Array 
	}

	
	/**
	 *  wpforms fields 
	 *  @param     int     $user_id     username
	 *  @param     int     $old_user_data     username
	 *  @since    1.0.0
	*/
	public function wpforms_forms_and_fields( ){
		
		if ( ! in_array('wpforms-lite/wpforms.php', $this->active_plugins ) || ! in_array('wpforms/wpforms.php', $this->active_plugins ) ) {
			return array( FALSE, " wpForms is Not Installed "  );
		}

		$FormArray	 	= array();
		$fieldsArray 	= array();	
		$fieldTypeArray = array();
		$wpforms = get_posts( array('post_type' => 'wpforms', 'posts_per_page' => -1) );
		foreach ( $wpforms as $wpform ) {
			$FormArray[ "wpforms_". $wpform->ID ] = "WPforms - ".$wpform->post_title ;	
			$post_content =  json_decode( $wpform->post_content );
			foreach($post_content->fields as $field){
				
				$field_list = array("text", "email", "textarea", "number");

				if( in_array( $field->type, $field_list  ) ){
					$fieldsArray["wpforms_". $wpform->ID ][$field->id] = $field->label;
				}
				//Inserting field type
				$fieldTypeArray["wpforms_". $wpform->ID ][$field->id]  =  $field->type;
				
			}	
		}
		
		return array( TRUE, $FormArray, $fieldsArray, $fieldTypeArray );	
	}

	/**
	 *  WE forms fields 
	 *  @param     int     $user_id     	username
	 *  @param     int     $old_user_data   username
	 *  @since    1.0.0
	*/
	public function weForms_forms_and_fields( ) {
		if ( ! in_array('weforms/weforms.php', $this->active_plugins ) ) {
			return array( FALSE, " weForms  is Not Active "  );
		}
		
		global $wpdb;
		$FormArray	 	= array();
		$fieldsArray 	= array();
		$fieldTypeArray = array();

		$weforms 		= get_posts( array('post_type' => 'wpuf_contact_form', 'posts_per_page' => -1) );
		$weFields 		= get_posts( array('post_type' => 'wpuf_input', 'posts_per_page' => -1) );
		
		foreach ($weforms as $weform ) {
			$FormArray[ "we_" . $weform->ID ] = 'weForms - '. $weform->post_title;
		}

		foreach ( $weFields as $Field ) {

			foreach ($FormArray as $weformID => $weformTitle ) {
				if( $weformID  ==  "we_" .$Field->post_parent ){
					$content_arr = unserialize(  $Field->post_content );
					$fieldsArray[ $weformID ][ $content_arr['name'] ] 	  =   $content_arr['label'] ;
					$fieldTypeArray[ $weformID ][ $content_arr['name'] ]  =   $content_arr['template'] ;
				}
			}
		}
		
		return array( TRUE, $FormArray, $fieldsArray, $fieldTypeArray );	
	}

	/**
	 *  Function That Create Slack Comment !
	 *  @param    string    $slackURL     		Slack Incoming Web Hook URL 
	 *  @param    array     $formInfo   		form information Array
	 *  @param    array     $fields_name_data   Form Fields array
	 *  @since    1.0.0
	*/
	public function slatre_create_slack_comment( $slackURL = '' , $formName ='', $formInfo = '' ,$fields_name_data = '') {
		
		if ( empty( $slackURL ) ) {
			return array( 0, "slack Web Hook URL  is empty" );
		}

		if ( empty( $formInfo ) ) {
			return array( 0, " Form Info is empty !" );
		}

		if ( empty( $fields_name_data ) ) {
			return array( 0, " fields name data  is empty" );
		}
		
		$title  	  = "Contact form name" ;
		$title 		 .= " # ". date("Y/m/d");
		#
		$description  = "";
		# 
		foreach ( $formInfo as $key => $value ) {
			$description .= "*".  $key  .":* " .  $value . " \n ";
		}
		# 
		$description  .=  "----------------------------------------\n";
		$i = 1 ;
		foreach ( $fields_name_data as $key => $value ) {
			$description .= "*". $i .". ".  $key  .":* " .  $value  ;
			$description .= "\n";
			$i++ ;
		}
		$final 			= array(); 
		$final['text'] 	= $description ;

		// Add URL And Remote Request 
		$r	= wp_remote_post( $slackURL, array(
			'headers'	=> [
				'Content-Type' 	=> 'application/json',
			],
			'body'	=> json_encode( $final )  ,
		));

		//  
		if( isset( $r['response']['code'])  &&  $r['response']['code'] == 200  ){
			return array(TRUE, $r );
		} else {
			return array(FALSE, $r );
		}
	}


	/**
	 *  Function That Create Trello Card !
	 *  @param    string    $trelloListID     	Form Key
	 *  @param    array     $formInfo   		form information Array
	 *  @param    array     $fields_name_data   Form Fields array
	 *  @since    1.0.0
	*/
	public function slatre_create_trello_card( $trelloListID='', $formName ='', $formInfo ='' ,$fields_name_data = '' ) {
		
		$token 	= get_option('slatre_trelloApiKey');

		if ( empty( $trelloListID ) ||  empty( $trelloListID )  ) {
			return array( 0, "Trello list ID is Empty" );
		}

		if ( empty( $formInfo ) || ! is_array( $formInfo )  ) {
			return array( 0, "Error on  formInfo" );
		}

		if ( empty( $fields_name_data ) || ! is_array( $fields_name_data )  ) {
			return array( 0, "Error on  fields_name_data" );
		}

		$title  	  = $formName ;
		$title 		 .= " # ". date("Y/m/d");
		$description  = "";
		# 
		foreach ($formInfo as $key => $value) {
			$description .= " **  ".  $key  ." : ** " . urlencode(  $value ) . " %0A ";
		}
		# 
		$description  .=  "  %0A ---------------  %0A ";
		$i = 1 ;
		foreach ( $fields_name_data as $key => $value ) {
			$description .= " **  ". $i .". ".  $key  ." : ** " . urlencode(  $value ) ;
			$description .= "  %0A  ";
			$i++ ;
		}

		$card_url 	= 'https://api.trello.com/1/cards?name=' . urlencode( $title ) . '&desc=' . $description . '&pos=top&idList=' . $trelloListID . '&keepFromSource=all&key=' . $this->key . '&token='. $token  . '';
		$r			= wp_remote_post( $card_url, array() );

		return $r['response'];
	}

	/**
	 * getting Open Boards
	 * @param    string    $token     Trello token;
	 * @since    1.0.0
	*/
	public function slatre_trello_boards( $token='' ){
		
		if ( empty( $token ) ) {
			return array( 0, "Empty trello token" );
		}

		$url = 'https://api.trello.com/1/members/me/boards?&filter=open&key='. $this->key .'&token='. $token .'';
		$trello_returns = wp_remote_get( $url , array());
		$boards 		= array();

		if ( ! is_wp_error( $trello_returns  ) && isset( $trello_returns['response']['code'] ) &&  $trello_returns['response']['code'] == 200 ) {
			foreach ( json_decode($trello_returns['body'] , true) as $key => $value ) {
				$boards[ $value['id'] ] = $value['name'];
			}
			return array( $trello_returns['response']['code'], $boards );
		} else {
			# Error Log 
			
			# Change The Code 
			return array( 410, array() );
		}
	}

	/**
	 * Getting Lists
	 * @param    string    $token     Trello token;
	 * @param    string    $board_id  Trello board ID;
	 * @since    1.0.0
	*/
	public function slatre_board_lists( $token='', $board_id=''){
		
		if ( empty( $token ) || empty( $board_id ) ) {
			return;
		}

		$url = 'https://api.trello.com/1/boards/'.$board_id.'/lists?filter=open&key='. $this->key .'&token='.$token.'';

		$trello_returns = wp_remote_get( $url , array());
		$lists 			= array();

		if ( isset( $trello_returns['response']['code'] ) &&  $trello_returns['response']['code'] == 200) {
			foreach ( json_decode( $trello_returns['body'], true ) as $key => $value) {
				$lists[ $value['id'] ] = $value['name'];
			}
		} else {
			# Error Log 
			
		}
		return array( $trello_returns['response']['code'], $lists );
	}

	/**
	 * AJAX function for Frontend getting Trello List ID & Name 
	 * @since    1.0.0
	*/
	public function slatre_ajax( ) {
		
		if( isset( $_POST['boardID'] ) && !empty( $_POST['boardID'] ) ){ 
			$token = get_option('slatre_trelloApiKey');
			$list  = $this->slatre_board_lists( $token, wp_strip_all_tags($_POST['boardID']) ); # WP Change Request  ###
			print_r( json_encode( $list ) );
		}

		exit;
	}

	# ============================================== ice & Fire || firing the form Events =================================================
	
	/**
	 * slatre_cf7_submission 
	 * @since    1.0.0
	*/
	public function slatre_cf7_submission( $contact_form ) {
		# I don't Know Why 
		# @set_time_limit(300);
		# 
		$submission  = WPCF7_Submission::get_instance();
		$posted_data = $submission->get_posted_data();
		$form_id     = $posted_data['_wpcf7'];
		# Unseating 

		if (isset( $posted_data['_wpcf7_version'] )) {
			unset( $posted_data['_wpcf7_version'] );
		}
		
		if (isset( $posted_data['_wpcf7_locale'] )) {
			unset( $posted_data['_wpcf7_locale'] );
		}
		
		if (isset( $posted_data['_wpcf7_unit_tag'] )) {
			unset( $posted_data['_wpcf7_unit_tag'] );
		}
		
		if (isset( $posted_data['_wpcf7_container_post'] )) {
			unset( $posted_data['_wpcf7_container_post'] );
		}

		if (isset( $posted_data['_wpcf7'] )) {
			unset( $posted_data['_wpcf7'] );
		}
		
		# Calling Event Boss 
		$this->slatreEvents( 'cf7', 'cf7_' . $form_id ,$posted_data, $form_id );
	}

	/**
	 * ninja_forms_after_submission 
	 * @param    array    $form_data     form data;
	 * @since    1.0.0
	*/
	public function slatre_ninja_forms_after_submission( $form_data ) {
		$data = array();
		foreach ($form_data["fields"] as $field ) {
			$data [ $field["key"] ] = $field["value"];
		}

		$this->slatreEvents( 'ninja', 'ninja_' . $form_data["form_id"], $data, $form_data["form_id"] );
	}

	/**
	 * formidable forms_after_submission 
	 * @param    string   $entry_id   entry_id;
	 * @param    string   $form_id     form_id;
	 * @since    1.0.0
	*/
	public function slatre_formidable_after_save( $entry_id, $form_id ) {
		global $wpdb;
		$dataArray = array();
		$entrees   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}frm_item_metas WHERE item_id = ". $entry_id ." ORDER BY field_id");
		foreach ( $entrees as $entre ) {
			$dataArray[$entre->field_id] = $entre->meta_value;
		}

		$this->slatreEvents( 'formidable', 'frm_'.$form_id, $dataArray, $form_id );
	}

	/**
	 * wpforms forms_after_submission 
	 * @param    array   $fields   		fields;
	 * @param    array   $entry     	entry;
	 * @param    array   $form_data    	form_data;
	 * @since    1.0.0
	*/
	public function slatre_wpforms_process( $fields, $entry, $form_data ) {
		$this->slatreEvents('wpforms', 'wpforms_' . $form_data["id"], $entry["fields"], $form_data["id"]);
	}

	/**
	 * weforms forms_after_submission 
	 * @param    string   $entry_id   		entry_id;
	 * @param    string   $form_id   		form_id;
	 * @param    string   $page_id     		page_id;
	 * @param    array    $form_settings    form_data;
	 * @since    1.0.0
	*/
	public function slatre_weforms_entry_submission( $entry_id, $form_id, $page_id, $form_settings  ) {
		# code
		$dataArray = array();
		global $wpdb;
		$entrees = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}weforms_entrymeta WHERE weforms_entry_id = ". $entry_id ." ORDER BY meta_id DESC");
		foreach ( $entrees as $entre ) {
			$dataArray[ $entre->meta_key ] = $entre->meta_value;
		}

		$r = $this->slatreEvents('weforms', 'we_' . $form_id, $dataArray, $form_id );
	}

	
	/**
	 * slatreEvents is a Event bearer;  This Function Will Fire For Slack  trello and Discoed 
	 * @param    string   $data_source   		data_source;
	 * @param    string   $event_name   		event_name;
	 * @param    array    $data_array     		data_array;
	 * @param    string   $id    				id;
	 * @since    1.0.0
	*/
	public function slatreEvents( $data_source = '', $event_name = '', $data_array = '', $id = '' ) {
		$token 					= get_option('slatre_trelloApiKey');
		$formSpecificSettings 	= get_page_by_title( $event_name,  $output = ARR, $post_type = 'slatre');

		if ( empty( $token ) || ! $formSpecificSettings  ){
			return array( FALSE, "No Token or Database Entry is Empty !" );
		}

		if ( $formSpecificSettings ) {
				
			$post_title 	 = $formSpecificSettings->post_title;
			$fields 		 = json_decode(  $formSpecificSettings->post_excerpt, TRUE );
			$settings 		 = json_decode(  $formSpecificSettings->post_content, TRUE );
			$slackStatus	 = $settings['slackStatus'];
			$slackWebHookUrl = $settings['slackWebHookUrl'];
			$trelloStatus	 = $settings['trelloStatus'];
			$trelloListID	 = $settings['trelloListID'];
			$trelloLabel	 = $settings['trelloLabel'];
			$trelloDue	 	 = $settings['trelloDue'];
			$formName	 	 = $settings['formName'];
			$enable_fields   = array();

			foreach ( $data_array as $key => $value ) {
				if ( array_key_exists( $key, $fields ) && isset( $this->form_fields[ $event_name ][ $key ] ) ){
					$enable_fields[ $this->form_fields[ $event_name ][ $key ] ] = $value;
				}
			}

			# Sending To Slack 
			if ( $slackStatus &&  ! empty( $slackWebHookUrl ) ){
				# send to Slack  || $slackURL = '' , $formInfo = '' ,$fields_name_data = ''
				$sr = $this->slatre_create_slack_comment( $slackWebHookUrl, $formName, array('Form name'=>$formName, 'Date'=>date("Y/m/d")), $enable_fields );
			}

			# Send Data to Trello 
			if ( $trelloStatus && ! empty( $trelloListID ) ){
				# send to trello  ||  $trelloListID='' , $formInfo ='' ,$fields_name_data = '' 
				$tr =  $this->slatre_create_trello_card( $trelloListID, $formName, array('Form name'=>$formName, 'Date'=>date("Y/m/d")), $enable_fields );
			}

			return array( $sr, $tr );
		}
	}

	/**
	 * LOG ! For Good , This the log Method 
	 * @since    1.0.0
	 * @param      string    $function_name     Function name.	 [  __METHOD__  ]
	 * @param      string    $status_code       The name of this plugin.
	 * @param      string    $status_message    The version of this plugin.
	*/
	public function slatre_log( $status_message = '', $function_name = __METHOD__ ){
		
		if ( empty( $status_message ) ){
			return  array( FALSE, "status_code or status_message is Empty");
		}

		
		$r = wp_insert_post( 
			array(	
				'post_content'  => $status_message,
				'post_title'  	=> '',
				'post_status'  	=> "publish",
				'post_excerpt'  => $function_name ,
				'post_type'  	=> "slatre_log",
			)
		);

		if ( $r ){
			return  array( TRUE, "Successfully inserted to the Log")  ; 
		}
	}

}


# Looking For a Fulltime Job or Part-time Job , if you have one Please let me Know . Thankyou 
# javmah >> jaedmah@gmail.com
# Dhaka Bangladesh 