<?php

shell_exec('cd ~qmed-utils');

echo md5(shell_exec('git show --format="%h" --no-patch'));
