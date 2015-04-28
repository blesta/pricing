<?php

include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AutoloadPricing.php';

spl_autoload_register(array('AutoloadPricing', 'load'));
