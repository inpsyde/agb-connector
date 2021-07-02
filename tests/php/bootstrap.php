<?php # -*- coding: utf-8 -*-
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


function wp_remote_get($url)
{

    if ( ! file_exists($url)) {
        return false;
    }
    $content = file_get_contents($url, false);

    if ( ! $content) {
        return false;
    }

    return array(
        'body' => $content,
        'response' => array(
            'code' => 200,
        ),
    );
}

function is_wp_error($possible_error)
{
    if (false === $possible_error) {
        return true;
    }

    return false;
}

function wp_remote_retrieve_body($response)
{
    if ( ! isset($response['body'])) {
        return '';
    }

    return $response['body'];
}

function wp_remote_retrieve_response_code($response)
{
    return $response['response']['code'];
}
