<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Apparently I need to check if WooCommerce is installed or not as latest version broke things

function vidyen_woocommerce_check()
{
  if ( class_exists( 'WooCommerce' ) )
  {
    return 1;
  }
  else
  {
    return 0;
  }
}
