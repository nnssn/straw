<?php

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function currentUri()
{
    return h($_SERVER["REQUEST_URI"]);
}

function queryString()
{
    $result = array();
    foreach ($_GET as $key => $value) {
        $result[$key] = h($value);
    }
    return $result;
}
