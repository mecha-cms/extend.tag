<?php

class Tag extends Page {

    public function __construct(string $path = null, array $lot = [], $prefix = []) {
        $p = $GLOBALS['URL']['path'];
        $n = $path ? Path::N($path) : null;
        if (Config::is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if (Config::is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        parent::__construct($path, array_replace_recursive([
            'url' => $n ? $GLOBALS['URL']['$'] . ($p ? '/' . $p : "") . '/' . Extend::state('tag', 'path') . '/' . $n : null
        ], $lot), $prefix);
    }

}