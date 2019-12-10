<?php

shell_exec('cd ~qmed-utils');

echo shell_exec('git show --format="%h" --no-patch');
