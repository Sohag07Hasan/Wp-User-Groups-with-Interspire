<?php 
	if($_POST['interspire-credentials-submitted'] == 'Y'){
		$options = array(
			'username' => trim($_POST['username']),
			'token' => trim($_POST['token']),
			'path'   => trim($_POST['path']),
		);
		
		update_option('interspire_credentials', $options);
	}
	
	
	$options = self::get_interspire_credentials();
	
	//var_dump($options);
	
?>


<div class="wrap">
	
	<h2> InterSpire API settings </h2>
	
	<?php 
		if($_POST['interspire-credentials-submitted'] == 'Y'){
			echo '<div class="updated"><p>saved</p></div>';	
		}
	?>
	
	<form action="" method="post">
		<input type="hidden" name="interspire-credentials-submitted" value="Y" />
		
		<table class="form-table">
			<tr>
				<th scope="row"><label for="username">XML Username</label></th>
				<td><input size="40" type="text" name="username" id="username" value="<?php echo $options['username']; ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="token">XML Token</label></th>
				<td><input size="40" type="text" name="token" id="token" value="<?php echo $options['token']; ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="path">XML Path</label></th>
				<td><input size="40" type="text" name="path" id="path" value="<?php echo $options['path']; ?>" /></td>
			</tr>
		</table>
		
		<p><input class="button button-primary" type="submit" value="Save" /></p>
		
	</form>
	
</div>