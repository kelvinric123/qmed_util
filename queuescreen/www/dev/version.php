<?php

shell_exec('cd ' . realpath(__DIR__ . '/../../../..'));

echo trim(shell_exec('git show --format="%h" --no-patch'));
