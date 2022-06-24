<?php
/**
 * Betheme Child Theme
 *
 * @package Betheme Child Theme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Child Theme constants
 * You can change below constants
 */

/**
 * Load Textdomain
 * @deprecated please use BeCustom plugin instead
 */

// define('WHITE_LABEL', false);

/**
 * Load Textdomain
 */

load_child_theme_textdomain('betheme', get_stylesheet_directory() . '/languages');
load_child_theme_textdomain('mfn-opts', get_stylesheet_directory() . '/languages');

/**
 * Enqueue Styles
 */

function mfnch_enqueue_styles() {
	// enqueue the parent RTL stylesheet
	if ( is_rtl() ) {
		wp_enqueue_style('mfn-rtl', get_template_directory_uri() . '/rtl.css');
	}

	// enqueue the child stylesheet
	wp_dequeue_style('style');
	wp_enqueue_style('style', get_stylesheet_directory_uri() .'/style.css');
}
add_action('wp_enqueue_scripts', 'mfnch_enqueue_styles', 101);


/**
 * Enqueue Scripts for trello viewer integration
 * 
 */
function smls_enqueue_scripts( $trelloboard, $id ) {
	$theme = wp_get_theme();
	// enqueue the trello stylesheet
	wp_enqueue_style( 'stylesheet', get_stylesheet_directory_uri().'/css/style.css', array(), $theme->get( 'Version' ) );

	// enqueue the trello json data
	wp_register_script( 'my-global-vars', '', array("jquery"), '', true );
	wp_enqueue_script( 'my-global-vars'  );
	wp_add_inline_script( 'my-global-vars', 'const site = ' . json_encode( array(
		'theme_path' => get_stylesheet_directory_uri(),
		'home_url' => home_url(),
		'main_home' => get_site_url(),
		'endpoint' => home_url().'/reader/'.$trelloboard,
		'title' => get_the_title( $id ),
	) ) );
	
	// enqueue the trello scripts
	wp_enqueue_script('jquery-2.1.3', 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js', array(), '', true);
	wp_enqueue_script('knockout', 'https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js', array(), '', true);
	wp_enqueue_script('markdown-it', 'https://cdnjs.cloudflare.com/ajax/libs/markdown-it/11.0.0/markdown-it.js', array(), '', true);
	wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js', array(), '', true);
	wp_enqueue_script('knockout-markdown',  get_stylesheet_directory_uri().'/js/knockout-markdown.js', array(), $theme->get( 'Version' ), true );
	wp_enqueue_script('script',  get_stylesheet_directory_uri().'/js/script.js', array("jquery-2.1.3"), $theme->get( 'Version' ), true );
}
//add_action( 'wp_enqueue_scripts', 'smls_enqueue_scripts' );


/**
 * Shortcode to display Trello board
 * 
 */
function smlTrelloViewer( $atts ) {
	
	// get the post ID from the custom post type
	$id = get_queried_object_id();

	// get the trelloID
	$trelloID = get_trelloID( $id );
	
	// enqueue the scripts
	smls_enqueue_scripts( $trelloID, $id );
	 
	 // call the template
	 ob_start();
	 get_template_part( 'template-parts/content', 'trelloboard', array('id' => $trelloID, 'postid' => $id ) ); 
	 return ob_get_clean(); 
 }
 add_shortcode( 'trello', 'smlTrelloViewer' );

// Rewrite url for json curl request
function add_trelloID($vars){
    $vars[] = 'trelloID';
    return $vars;
}
add_filter('query_vars', 'add_trelloID', 0, 1);

add_rewrite_rule('^reader/([^/]+)/?$','index.php?pagename=reader&trelloID=$matches[1]','top');


function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');


// function go thet the board id from the url
function get_trelloID( $id ){
	// retrieve the advanced custom field value for the trello board ID
	$trelloID = get_field('trello_board_id', $id);

	// explode the $trelloID variable into an array and get the 5th element
	$trelloID = explode('/', $trelloID);
	$trelloID = $trelloID[4];
	
	return $trelloID;

}

// function to get the title and permalink of the custom post type documents with the postID
function showDocuments() {
	
	$contenido = '';

	// verify if current user is admin
	if ( current_user_can('administrator') ) {
		// get all post in custom post type documentos and return the title and permalink
		$args = array(
			'post_type' => 'documentos',
			'posts_per_page' => -1,
			'post_status' => 'private',
			'orderby' => 'title',
			'order' => 'ASC',
		);
		$documents = new WP_Query( $args );
		if ( $documents->have_posts() ) {
			while ( $documents->have_posts() ) {
				$documents->the_post();
				$contenido .= '<li><a href="'.get_permalink().'">'.get_the_title().'</a></li>';
			}
		}
		wp_reset_postdata(); 
	}
	else {
		// get the acf field selector_cliente's value and store it in $postID from the current user
		$postID = get_field('selector_cliente', 'user_'.get_current_user_id());

		// get the post with the $postID
		$post = get_post( $postID );
		$title = $post->post_title;
		$permalink = get_permalink( $postID );
		$contenido .= '<li><a href="'.$permalink.'">'.$title.'</a></li>';
	}
	return $contenido;
}

// Secure zone for the trello board functionality
add_shortcode( 'exclusivo', 'contenido_registrados' );
function contenido_registrados( ) {
	
	$contenido = "";
	
    if( is_user_logged_in() ) {
    	ob_start(); ?>

    	<ul>
			<?php echo showDocuments(); ?>
    		<li><a href="<?= get_home_url(); ?>/wp-login.php?action=logout">Cerrar Sesión</a></li>
    	</ul>

    <?php $contenido = ob_get_clean(); 
    }
    else {
	    ob_start(); ?>
	    <h3>Inicia sesión</h3>
	    <form name="loginform" id="loginform" action="<?=get_home_url(); ?>/wp-login.php" method="post">
			
			<p class="login-username">
				<input type="text" name="log" id="user_login" placeholder="Nombre de usuario o correo electrónico" class="input" value="" size="20">
			</p>
			<p class="login-password">
				<input type="password" name="pwd" id="user_pass" placeholder="Contraseña" class="input" value="" size="20">
			</p>
			
			<p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever"> Recuérdame</label></p>
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary" value="Acceder">
				<input type="hidden" name="redirect_to" value="<?= get_home_url(); ?>/area-privada">
				<a class="boton_blanco" href="<?=get_home_url(); ?>/wp-login.php?action=register">Registro</a>
			</p>
			
		</form>
	    
	<?php $contenido = ob_get_clean();    
    }
    
    return $contenido;

}

// Remove the "private" label from the post title
add_filter( 'private_title_format', function ( $format ) {
    return '%s';
} );

// Avoid the 404 page for private posts, send to the sign up page
add_action('wp','redirect_stuffs', 0);
function redirect_stuffs(){
	global $wpdb; 
    if ($wpdb->last_result[0]->post_status == "private" && !is_user_logged_in() ):
        wp_redirect(home_url('/area-privada'), 301 );
        exit();
    endif;
}

// Go to hompage when the user logs out
add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
         wp_redirect( home_url(), 301 );
         exit();
}

// Allow subscribers to see Private posts and pages
$subRole = get_role( 'subscriber' );
$subRole->add_cap( 'read_private_posts' );
$subRole->add_cap( 'read_private_pages' );