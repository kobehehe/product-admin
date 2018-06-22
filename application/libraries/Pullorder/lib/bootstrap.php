<?php

//惰性加载 wangchognwen
require_once __DIR__ . '/AutoLoader.php';

$autoloader = new AutoLoader(__NAMESPACE__, VENDOR_DIR);

$autoloader->register();
