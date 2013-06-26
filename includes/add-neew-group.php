<?php 

	/*
	 * multisite compatible
	 * */
	
	if(is_multisite()){
		$Ugdb = new UgDbManagement();
		$status = $Ugdb->check_if_tables_installed();

		if(false === $status){
			$Ugdb->manage_db();
		}
	}



	$action = admin_url('admin.php?page=addnew-user-group');
	
	
	if($_REQUEST['group_id'] > 0){
		$group = $Ugdb->get_group($_REQUEST['group_id']);
		
		if($group){
			$metas = $Ugdb->get_group_metas($_REQUEST['group_id']);
		}
		
		//var_dump($group);
		//var_dump($metas);
	}
	
	$lists = self::get_interspire_lists();
?>


<style>
	.group-selectbox{
		min-width: 255px;
	}
</style>

<div class="wrap">

	<h2>Group Information</h2>
	
	<?php 
		if($_REQUEST['message'] == 1){
			?>
			<div class="updated"><p> Group Information saved </p></div>
			<?php 
		}
		
		if($_REQUEST['message'] == 2){
			
			?>
			<div class="error"><p> Group Information (Role) is alreay exists </p></div>
			<?php 
		}
	?>
	
	<form action="<?php echo $action; ?>" method="post">
	
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
		<input type="hidden" name="add-new-group" value="Y" />
		
		<?php 
			if($_REQUEST['group_id'] > 0){
				?>
				<input type="hidden" name="group_id" value="<?php echo trim($_REQUEST['group_id']); ?>" />
				<input type="hidden" name="group_role" value="<?php echo $metas['role']; ?>" />
				<?php 
			}
		?>
		
		<table class="form-table" >
			<tbody>
				<tr>
					<th scope="row"><label for="group_name">Group Name</label></th>
					<td><input id="group_name" size="40" type="text" name="group_name" value="<?php echo $group['name']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="group_domain">Group Domain</label></th>
					<td><input size="40" type="text" name="group_domain" value="<?php echo $group['domain']; ?>" id="group_domain" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="group_password">Group Password </label></th>
					<td><input size="40" type="text" id="group_password" name="group_password" value="<?php echo $metas['group_password']; ?>" /></td>
				</tr>
				
				<tr>
					<th scope="row"> <label for="group_interspire_list" > InterSpire List </label></th>
					<td>
						<select id="group-interspire_list" class="group-selectbox" name="group_interspire_list">
							<option value="">Choose</option>
							<?php 
								if(count($lists) > 0){
									foreach($lists as $list){
										?>
										<option <?php selected($list['listid'], $metas['group_interspire_list']); ?> value="<?php echo $list['listid']; ?>"><?php echo $list['name']; ?></option>
										<?php 
									}
								}							
							?>							
						</select>
					</td>
				</tr>
				
			</tbody>
		</table>
		
		<p>
			<?php if($_REQUEST['group_id'] > 0) : ?>
				<input type="submit" value="Update Group" class="button button-primary" />
			<?php else: ?>
				<input type="submit" value="Add Group" class="button button-primary" />
			<?php endif; ?>
		</p>
		
	</form>
	
	<?php if($_REQUEST['group_id'] > 0) : ?>	
		<p><a href="<?php echo admin_url('admin.php?page=addnew-user-group&csv=csv&group_id='.$_REQUEST['group_id']); ?>"> Import CSV</a></p>
	<?php endif; ?>
	
</div>