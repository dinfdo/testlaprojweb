<?php
require_once __DIR__ . '/../helpers.php';

only_method('POST');
start_session();
session_destroy();

json_response(['success' => true]);
