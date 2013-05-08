<?php 
	$action = admin_url('admin.php?page=addnew-user-group');
?>


<style>
	.group-selectbox{
		min-width: 255px;
	}
</style>

<div class="wrap">

	<h2>Group Information</h2>
	
	<form action="<?php echo $action; ?>" method="post">
	
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
		<input type="hidden" name="add-new-group" value="Y" />
		
		<table class="form-table" >
			<tbody>
				<tr>
					<th scope="row"><label for="group_name">Group Name</label></th>
					<td><input id="group_name" size="40" type="text" name="group_name" value="" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="group_domain">Group Domain</label></th>
					<td><input size="40" type="text" name="group_domain" value="" id="group_domain" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="group_password">Group Password </label></th>
					<td><input size="40" type="text" id="group_password" name="group_password" value="" /></td>
				</tr>
				
				<tr>
					<th scope="row"> <label for="group_interspire_list" > InterSpire List </label></th>
					<td>
						<select id="group-interspire_list" class="group-selectbox" name="group_interspire_list">
							<option value="">List A</option>
							<option value="">List B</option>
						</select>
					</td>
				</tr>
				
			</tbody>
		</table>
		
		<p><input type="submit" value="Save Group" class="button button-primary" /></p>
		
	</form>
	
</div>