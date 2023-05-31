<?php

require_once 'vendor/autoload.php';

$t = new \Lum\Test();
$d = new \Lum\Dirstack();

$t->plan(18);

$initial = $d->pwd();
$tmp = sys_get_temp_dir();

$t->is(count($d), 1, 'initial count is 1');
$t->ok($d->go($tmp), "chdir $tmp");
$t->is($d->pwd(), $tmp, "current dir is $tmp");
$tdir1 = uniqid('dirstack_test');
mkdir($tdir1);
$tpath1 = "$tmp/$tdir1";
$t->ok($d->go($tdir1), "chdir $tdir1");
$t->is($d->pwd(), $tpath1, "current dir is $tpath1");
$tdir2 = uniqid('', true);
mkdir($tdir2);
$tpath2 = "$tpath1/$tdir2";
$t->ok($d->go($tdir2), "chdir $tdir2");
$t->is($d->pwd(), $tpath2, "current dir is $tpath2");
$t->is(count($d), 4, 'stack count is 4');
$t->ok($d->back(), 'went back one level');
$t->is($d->pwd(), $tpath1, "current dir is once again $tpath1");
rmdir($tdir2);
$t->ok($d->back(2), 'went back 2 levels');
$t->is($d->pwd(), $initial, "current dir is initial $initial");
rmdir($tpath1);
$t->ok(!$d->back(), 'no further paths to go back to');
$t->is(count($d), 1, 'stack count at 1');
$t->isJSON($d->getStack(), [$initial], 'correct minimal stack');

$d = new \Lum\Dirstack($tmp);
$t->is($d->pwd(), $tmp, 'changed directory when passed initial value');
$t->ok($d->go($initial), "chdir $initial");
$t->isJSON($d->getStack(), [$tmp, $initial], 'correct stack value');

echo $t->tap();
return $t;
