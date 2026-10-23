<?php
require_once __DIR__ . '/auth_lib.php';

do_logout();
flash_set('success', 'Anda telah berhasil Log Out.');
redirect('login.php');
