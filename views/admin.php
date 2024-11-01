<?php
if ( !class_exists('GetResponseOptions') ) {
	
	class GetResponseOptions
	{
		
		function __construct() {
			add_action('admin_menu', array(&$this, 'gr_option_page' ) );
		}
		function gr_option_page() {
				add_options_page('Get Response', 'Get Response', 8, __FILE__, array(&$this, 'gr_options') );
		}
		
		
		
	//build admin interface
	function gr_options() 
	{
		global $wpdb, $table_prefix;
			
			$new_options = array(
				'gr_api_key'				=> $_POST["gr_api_key"],
				'gr_campaign'				=> $_POST["gr_campaign"],
				'gr_add_full_name'			=> $_POST["gr_add_full_name"]

			);
			
		if($_POST['action'] == "save") 
		{
			echo "<div class=\"updated fade\" id=\"updatenotice\"><p>" . __('Your Get Response settings have been updated.', 'GetResponse') . "</p></div>";
			update_option("gr_options", $new_options);

		}
		
		if($_POST['action'] == "reset") 
		{ 
			echo "<div class=\"updated fade\" id=\"limitcatsupdatenotice\"><p>" . __('Your default settings have been <strong>updated</strong>. </p>', 'GetResponse') . "</div>";
			delete_option("gr_options", $new_options);
		}
		

		$options = get_option('gr_options');
		
		?>

	<div class="wrap">
		<h2><?php _e('Get Response:', 'GetResponse'); ?> <?php echo $this->version; ?></h2>
			<form method="post">
				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="gr_api_key"><?php _e('Get Response API Key','GetResponse'); ?></label></th>
							<td>
								<input name="gr_api_key" type="text" id="gr_api_key" value="<?php echo $options['gr_api_key'];?>" class="regular-text">
								<span class="description">In order to use GetResponse API, the unique API KEY is required. The key is assigned to every <strong>pro</strong> account and you can obtain it <a href="http://www.getresponse.com/my_api_key.html">here</a>.</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="gr_campaign"><?php _e('Get Response Campaign','GetResponse'); ?></label></th>
							<td>
								<select id="gr_campaign" name="gr_campaign">
									<?php
										$get_getresponse_campaigns = get_getresponse_campaigns();
										foreach ($get_getresponse_campaigns as $campaigns => $camp ) {
											$name = $camp['name'];
											?>
											<option<?php if ($options['gr_campaign'] == $name ) { echo ' selected="selected"'; } ?> value="<?php echo $name; ?>"><?php echo $name; ?></option>
											<?php

										} ?>
					            </select>
					
					
								<span class="description"></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Add a full name field to the registration page.</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span>Add a full name field to the registration page. </span></legend>
									<p>
										<label><input name="gr_add_full_name" type="checkbox" value="TRUE" <?php if ($options['gr_add_full_name'] == 'TRUE' ) echo 'checked'; ?>> Include Full Name Field</label>
									</p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				
				</table>


		<p class="submit">
			<input type="hidden" name="action" value="save" />
			<input type="submit" value="<?php _e('Update Options', 'GetResponse') ?>" />
		</p>
	</form>

	<div class="info">
		<div style="float: left; padding-top:4px;"><?php _e('Developed by Dan Cameron of', 'GetResponse'); ?> <a href="http://sproutventure.com?wp-get-response" title="Custom WordPress Development"><?php _e('Sprout Venture', 'GetResponse'); ?></a>. <?php _e('We provide custom WordPress development and more.', 'GetResponse') ?>
		</div>
		<div style="float: right; margin:0; padding:0; " class="submit">
			<form method="post">
				<input name="reset" type="submit" value="<?php _e('Reset Button', 'GetResponse') ?>" />
				<input type="hidden" name="action" value="reset" />
			</form>
		<div style="clear:both;"></div>
	</div>
	<div style="clear: both;"></div>

	<small><?php _e('Find a bug?', 'GetResponse') ?> <a href="https://redmine.sproutventure.com/projects/wp-get-response/issues" target="blank"><?php _e('Post it as a new issue','GetResponse')?></a>.</small>
</div>


		<?php
	}	//end gr_option_page
}
}
?>