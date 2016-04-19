--TEST--
Basic features using FileSystem cache engine
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(['cache_engine' => 'FileSystem']);
require __DIR__.'/_test.php';
?>
--EXPECT_EXTERNAL--
_test.expect
--CLEAN--
<?php
include __DIR__.'/../bootstrap.php';
exec('rm -r '.CACHE.'/*');
?>
