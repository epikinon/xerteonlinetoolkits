<?php

require_once("../../../config.php");

require("../user_library.php");
require("management_library.php");

if(is_user_admin()){

    security_list();

}else{

    management_fail();

}

?>
