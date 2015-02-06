<?php
namespace Slider\Test;

define('APPLICATION_ROOT', '../../../');
require_once APPLICATION_ROOT . 'init_tests_autoloader.php';

use UnitTestBootstrap;

class SliderBootstrap extends UnitTestBootstrap\UnitTestBootstrap
{}

SliderBootstrap::init();