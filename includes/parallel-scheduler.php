<?php 
	@set_time_limit(0);
	
	if($_POST['parallel-syncrinization'] == "Y"){
		InterspireScheduler::process_scheduler();		
	}
	
?>


<div class="wrap">
	<h2>Force Scheduler</h2>
	
	<?php if($_POST['parallel-syncrinization'] == "Y"){ ?>
	
	<div class="updated">
		<p> Please check the Interspire list  </p>
	</div>
	
	<?php } ?>
	<p style="color: green"> 
		<span style="font-size: 15px;">Run button will synchronize the users with interspire (300 users/per click).</span> <br/>
		The plguin uses the offset from default cron. So, if you don't get a user in interspire by clicking for one time, <br/> 
		it is not necessary to think of cron error.	The user/users are skipped due to the offset. Dont' worry, the offset <br/>
		will reset automatically after reaching the last user. 
	
	</p>
	
	<form action="" method="post">
		
		<input type="hidden" name="parallel-syncrinization" value="Y">
		
		<p> <input class="button button-secondary" type="submit" value="Run" /> </p>
		
	</form>
	
	<h4>Alterative Approach</h4>
	<p style="color: green">
		Use this url to run a cron script externally. It will synchronize 300 contacts for each operation
	</p>
	<p> <input type="text" size="65" value="<?php echo add_query_arg(array('interspire_cron'=>'process_scheduler'), get_option('siteurl')); ?>" readonly /> </p>
	
</div>