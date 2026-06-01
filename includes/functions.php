<?php

function clean_input($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8");
}

function redirect_to($path)
{
    header("Location: " . $path);
    exit;
}

function is_post_request()
{
    return $_SERVER["REQUEST_METHOD"] === "POST";
}

