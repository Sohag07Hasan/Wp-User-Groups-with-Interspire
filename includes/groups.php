<?php 
	$action = admin_url('admin.php?page=user-group-management');
	if($UgList->get_pagenum()){
		$action = add_query_arg(array('paged'=>$UgList->get_pagenum()), $action);
	}	
?>



<div class="wrap">
	<h2>Under Contructions</h2>
	
	<?php 
		if($message){
			?>
			<div class="updated"><p><?php echo $message; ?></p></div>
			<?php 
		}	
	?>
	
	<form action="<?php echo $action; ?>" method="post">
	
	<?php 
		$UgList->prepare_items();
		$UgList->display();
	?>
	
	</form>
</div>