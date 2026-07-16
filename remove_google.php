<?php
$json = file_get_contents('vendor/composer/installed.json');
$data = json_decode($json, true);
$data['packages'] = array_filter($data['packages'], function($p) {
    return strpos($p['name'], 'google/') !== 0;
});
$data['packages'] = array_values($data['packages']);
file_put_contents('vendor/composer/installed.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
