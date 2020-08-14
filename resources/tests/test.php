<?php

namespace Test;

interface ITest
{
    public static function Perform();
}

const tests = [
    'insert-in-db.php',
    // 'create-classes.php',
];

foreach (tests as $i => $test) {
    ?><div class="test-wrap" style="white-space: pre-wrap;"><?php
    include $test;
    ?></div><?php
}
