<?php 
	$lists = self::get_interspire_lists();
	
	if($_POST['default-site-settings']){
		$options = array(
			'restrict-password-reset' => isset($_POST['restrict-password-reset']) ? 1 : 0,
			'restrict-registration'  => isset($_POST['restrict-registration']) ? 1 : 0,
			'default-interspire-list' => $_POST['default-interspire-list']
		);
		
		update_option('default_site_options', $options);
	}
	
	$options = self::get_site_default_options();
	
?>

<div class="wrap">
	
	<h2> Site Settings </h2>
	
	<?php 
		if($_POST['default-site-settings'] == 'Y'){
			echo '<div class="updated"><p>saved</p></div>';	
		}
	?>
	
	<form action="" method="post">
		<input type="hidden" name="default-site-settings" value="Y" />
		
		<table class="form-table">
			<tr>
				<th scope="row"><label for="restrict-password-rest">Redirect Password Reset</label></th>
				<td> <input <?php checked(1, $options['restrict-password-reset']); ?> id="restrict-password-rest" type="checkbox" value="1" name="restrict-password-reset" /> </td>
			</tr>
			<tr>
				<th scope="row"><label for="restrict-registration">Redirect Registration Page</label></th>
				<td> <input <?php checked(1, $options['restrict-registration']); ?> id="restrict-registration" type="checkbox" value="1" name="restrict-registration" /> </td>
			</tr>
			<tr>
				<th scope="row"><label for="default-interspire-list">Default Interspire List</label></th>
				<td>
					<select id="default-interspire-list" style="min-width: 140px;" name="default-interspire-list">
						<option value="0">Choose</option>
						
						<?php 
							if(count($lists) > 0){
								foreach($lists as $list){
									?>
									<option <?php selected($list['listid'], $options['default-interspire-list']); ?> value="<?php echo $list['listid']; ?>"><?php echo $list['name']; ?></option>
									<?php 
								}
							}							
						?>	
						
					</select>
				</td>
			</tr>
		</table>
		
		<p><input class="button button-primary" type="submit" value="Save" /></p>
		
	</form>
	
</div>