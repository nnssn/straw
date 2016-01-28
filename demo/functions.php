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
    return array_map(function ($value) {
        return (! is_array($value)) ? h($value) : array_map('h', $value);
    }, $_GET);
}
