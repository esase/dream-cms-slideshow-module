<?php
namespace Slideshow\Test;

define('APPLICATION_ROOT', '../../../');
require_once APPLICATION_ROOT . 'init_tests_autoloader.php';

use UnitTestBootstrap;

class SlideshowBootstrap extends UnitTestBootstrap\UnitTestBootstrap
{}

SlideshowBootstrap::init();