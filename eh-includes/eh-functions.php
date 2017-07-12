<?php
//check class dependencies exist or not
if(!class_exists("Eh_Dependencies"))
{
	require_once('class-eh-dependencies.php');
}

//check woocommerce is active function exist
if(!function_exists('eh_is_woocommerce_active'))
{
	function eh_is_woocommerce_active()
	{
		return Eh_Dependencies::woocommerce_active_check();
	}
}
