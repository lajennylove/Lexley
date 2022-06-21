<?php
# 786  Bismillahir Rahmaner Rahim 
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wordpress.org/plugins/slatre/
 * @since      1.0.0
 *
 * @package    Slatre
 * @subpackage Slatre/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( 'WordPress Forms to Slack & Trello ', 'WpAdminStyle' ); ?></h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<?php

				foreach ($this->formID_titles as $form_key => $form_title) {
					
					echo "<div class='meta-box-sortables ui-sortable'>";
					echo "<div class='postbox'>";

					echo "<button type='button' class='handlediv' aria-expanded='true' >";
						echo "<span class='screen-reader-text'>Toggle panel</span>";
						echo "<span class='toggle-indicator' aria-hidden='true'></span>";
					echo "</button>";
					// <!-- Toggle -->

					echo "<h2 class='hndle'><span>  " . $form_title . " </span></h2>";
					
					echo "<div class='inside'>";
						echo "<form name='Simple' action='' method='POST' >";
						echo "<input type='hidden' name='actionFrom' value='formSettings'>";
						echo "<input type='hidden' name='formKey' value='". $form_key."'>";
						//  Form Fields Starts from Here ;
						echo "<p style=' text-align: center; color: #e4e4e4;' > <i> Form Fields  </i> </p>";

						foreach ( $this->form_fields[$form_key] as $field_key => $field_name ) {
							echo "<div class='notice notice-info inline'>";
							echo "<p>";
							
							if( isset( $entries[$form_key]['fields'][$field_key] )  ){
								echo "<input name='fields[" . $field_key . "]' type='checkbox' checked  >";
							}else{
								echo "<input name='fields[" . $field_key . "]' type='checkbox'  >";
							}
						
							echo $field_name ;
							if ( isset(  $this->form_field_types[ $form_key ][ $field_key ] ) ){
								echo " <code> " . $this->form_field_types[ $form_key ][ $field_key ] . " </code>";
							} 
							
							echo "</p>";
							echo "</div>";
						}

						//  Settings Starts From Here
						echo "<p style=' text-align: center; color: #e4e4e4;' > <i>  Settings  </i> </p>";

						
						echo "<div class='notice notice-warning inline'>";
							echo "<p>";
							
							
							if ( isset( $entries[$form_key]['slackStatus'] ) && $entries[$form_key]['slackStatus'] ) {
								echo "<input name='slackStatus' type='checkbox' checked >";
							} else {
								echo "<input name='slackStatus' type='checkbox'>";
							}

							echo "<b> Slack : 		 </b> &nbsp; &nbsp; ";
							
							if ( isset( $entries[$form_key]['slackWebHookUrl']) && !empty( $entries[$form_key]['slackWebHookUrl'] ) ) {
								echo "<i> Incoming  WebHook URL : </i> <input type='text' style='width: 65%;'  name='slackWebHookUrl' value='". $entries[$form_key]['slackWebHookUrl'] ."' />   &nbsp; &nbsp;";
							} else {
								echo "<i> Incoming  WebHook URL : </i> <input type='text' style='width: 65%;' name='slackWebHookUrl'  />   &nbsp; &nbsp;";
							}

							echo "</p>";
						echo "</div>";
						

						if ( isset($trelloApiKey) && !empty($trelloApiKey) ){
							echo "<div class='notice notice-warning inline'>";
								echo "<p>";

								if (isset( $entries[$form_key]['settings']['trelloStatus']) && $entries[$form_key]['settings']['trelloStatus']) {
									echo "<input name='trelloStatus' type='checkbox' checked ><b> Trello  :</b>   &nbsp; ";
								} else {
									echo "<input name='trelloStatus' type='checkbox'><b> Trello  :</b>   &nbsp; ";
								}

								$trello_boards = $this->slatre_trello_boards( $trelloApiKey );
								if( $trello_boards[0] ){
									echo "<select name='slatreTrelloBoard' class='slatreTrelloBoard' style='min-width:215px;' >";
									echo "<option value=''>Select Trello board</option>";
									foreach ( $trello_boards[1] as $key => $value ) {
										// echo "<option value='". $key ."'> ". $value ." </option>";
										if( isset( $entries[$form_key]['trelloBoard'] ) && $entries[$form_key]['trelloBoard'] == $key  ){
											echo "<option value='". $key ."' selected > ". $value ." </option>";
										} else {
											echo "<option value='". $key ."' > ". $value ." </option>";
										}
									}
									echo "</select>";
								}

								echo "<select name='slatreTrelloListID' class='slatreTrelloList' style='min-width:215px;' >";
								echo "<option value=''>Select Trello List</option>";
								if(  isset(  $entries[$form_key]['TrelloList'] ) &&  ! empty(  $entries[$form_key]['TrelloList'] ) ){
									foreach ( $entries[$form_key]['TrelloList'] as $key => $value) {
										if(   $key == $entries[$form_key]['trelloListID']   ){
											echo "<option value='". $key ."' selected > ". $value ." </option>";
										} else {
											echo "<option value='". $key ."' > ". $value ." </option>";
										}
									}
								}
								echo "</select>";

								$colour = array(
									'5e95679e7669b22549eea64e'=>'green',
									'5e95679e7669b22549eea64d'=>'yellow',
									'5e95679e7669b22549eea64c'=>'orange',
									'5e95679e7669b22549eea64b'=>'red',
									'5e95679e7669b22549eea658'=>'purple',
									'5e95679e7669b22549eea658'=>'purple',
									'5e95679e7669b22549eea657'=>'blue',
									'5e9b5daa69fa0a77b396dd0a'=>'black',
								);

								echo "<select name='slatreTrelloColour' style='min-width:215px;' >";
									echo "<option value=''> Labels colour</option>";
									foreach ( $colour as $colourKey => $colourTitle) {
										if(isset( $entries[$form_key]['trelloLabel'] ) &&  $colourKey == $entries[$form_key]['trelloLabel']){
											echo "<option value='". $colourKey ."' selected > ". $colourTitle ." </option>";
										} else {
											echo "<option value='". $colourKey ."' > ". $colourTitle ." </option>";
										}
									}
								echo "</select>";

								$days = array( 
									'1d'=>'1 Day',
									'2d'=>'2 Day',
									'3d'=>'3 Day',
									'5d'=>'5 Day',
									'1w'=>'1 Week',
									'2w'=>'1 Week',
									'1m'=>'1 Month',
									'3m'=>'3 Months',
									'6m'=>'6 Months',
								);

								echo "<select name='slatreTrelloDue' style='min-width:215px;' >";
								echo "<option value=''>Select Due Date</option>";
								foreach ( $days as $key => $value) {
										if( isset( $entries[$form_key]['trelloDue'] ) &&  $key == $entries[$form_key]['trelloDue']   ){
											echo "<option value='". $key ."' selected > ". $value ." </option>";
										} else {
											echo "<option value='". $key ."' > ". $value ." </option>";
										}
								}
								echo "</select>";

								echo "</p>";
							echo "</div>";
						}

						echo " <button type='submit' class='button-secondary' value='Submit'> <span class='dashicons dashicons-welcome-add-page' style=' padding-top: 3px;'> </span> save </button> "; 

						echo"</form>";
					echo "</div>";
					// <!-- .inside -->

					echo "</div>";
					// <!-- .postbox -->
					echo "</div>";

				}
				
				?>


			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<button type="button" class="handlediv" aria-expanded="true" >
							<span class="screen-reader-text">Toggle panel</span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<!-- Toggle -->

						<h2 class="hndle"><span><?php esc_attr_e(
									'Sidebar Content Header', 'WpAdminStyle'
								); ?></span></h2>

						<div class="inside">
							<p>
								<form name='credentialsForm' method='POST' >
								<input type='hidden' name='actionFrom' value='credentials'>
								<!-- <b>slack web hook URL : </b>
								<?php
									// if( isset($slackWebHook) && !empty($slackWebHook) ){
									// 	echo"<input type='text' style='width: 100%; height: 3em;' name='slatre_slackWebHook' value='".$slackWebHook."'  class='' /> <br>";
									// } else {
									// 	echo "<input type='text' style='width: 100%; height: 3em;' name='slatre_slackWebHook'  class='' /> <br>";
									// }
								?> -->
								

								<b>Trello api key : </b>
								<?php
									if( isset($trelloApiKey) && !empty($trelloApiKey) ){
										echo"<input type='text' style='width: 100%; height: 3em;' name='slatre_trelloApiKey' value='".$trelloApiKey."'  class='' /> <br>";
									} else {
										echo "<input type='text' style='width: 100%; height: 3em;' name='slatre_trelloApiKey'  class='' /> <br>";
									}
								?>
								<a href='https://trello.com/1/authorize?expiration=never&name=Wootrello&scope=read%2Cwrite&response_type=token&key=7385fea630899510fd036b6e89b90c60'  style='margin-left:150px; text-decoration: none; ' target='_blank'>Trello access code</a>
								<br>

								<input class="button-secondary" type="submit" name="Example" value="SAVE" />
							</p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->