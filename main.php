<?php
/*
 * Plugin Name: Users' Group Management
 * Author: Mahibul Hasan Sohag
 * Description: Manages different groups based on email domains......
 * */

define("USERGROUPMANAGMENT_FILE", __FILE__);
define("USERGROUPMANAGMENT_DIR", dirname(__FILE__));

include USERGROUPMANAGMENT_DIR . '/classes/user-group-db.php';

include USERGROUPMANAGMENT_DIR . '/classes/class.group-management.php';
UgManagement::init();

include USERGROUPMANAGMENT_DIR . '/classes/class.scheduler.php';
InterspireScheduler::init();
