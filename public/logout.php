<?php

session_regenerate_id(true);
session_destroy();
header('Location: login.php');
