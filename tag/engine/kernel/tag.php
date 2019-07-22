<?php

class Tag extends Page {

    // Set pre-defined tag property
    public static $data = [];

    public function __construct(string $path = null, array $lot = [], $prefix = []) {
        global $url;
        $p = trim($url->path, '/');
        if (Config::is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if (Config::is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        $p = strtr($p, DS, '/');
        $n = $path ? Path::N($path) : null;
        parent::__construct($path, array_replace_recursive([
            'url' => $n ? $url . ($p ? '/' . $p : "") . '/' . state('tag')['path'] . '/' . $n : null
        ], $lot), $prefix);
    }

}