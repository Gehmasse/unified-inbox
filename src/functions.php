<?php

function dd(...$data): never
{
    header('Content-type: text/html; charset=utf-8');

    foreach ($data as $d) {
        echo '<pre>' . print_r($d, true) . '</pre>';
    }

    die;
}