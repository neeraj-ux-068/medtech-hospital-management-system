<?php
require_once __DIR__ . '/include/security.php';

function check_login(): void
{
    hms_require_role('patient');
}
