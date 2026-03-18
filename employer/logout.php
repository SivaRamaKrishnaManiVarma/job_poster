<?php
require_once '../includes/config.php';
session_destroy();
redirect(url('employer/login.php'));
