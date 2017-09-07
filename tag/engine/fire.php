<?php

// Require the plug manually…
r(__DIR__ . DS . 'plug', [
    'from.php',
    'get.php',
    'to.php'
], null, Lot::get(null, []));

function fn_tag_url($s) {
    global $site, $url;
    $path = $url->path;
    if ($site->tag) { // → `blog/tag/tag-slug`
        $path = URL::I($path); // remove page offset from URL path
        $path = Path::D($path, 2);
    } else if ($site->is === 'page') { // → `blog/page-slug`
        $path = Path::D($path);
    }
    return $url . ($path ? '/' . $path : "") . '/' . Extend::state(Path::D(__DIR__), 'path') . '/' . Path::N($s);
}

Hook::set('tag.url', 'fn_tag_url');

function fn_page_query_set($content, $lot) {
    $query = [];
    $f = Path::F($lot['path']);
    if (!array_key_exists('kind', $lot)) {
        $lot['kind'] = e(File::open($f . DS . 'kind.data')->read([]));
    }
    foreach ($lot['kind'] as $v) {
        if ($slug = To::tag($v)) {
            $query[] = str_replace('-', ' ', $slug);
        }
    }
    return $query;
}

function fn_page_query($content, $lot) {
    if (!$content || !array_key_exists('query', $lot) || !file_exists(Path::F($lot['path']) . DS . 'query.data')) {
        return fn_page_query_set($content, $lot);
    }
    return $content;
}

function fn_page_tags($content, $lot) {
    global $url;
    $tags = [];
    foreach (fn_page_query_set($content, $lot) as $v) {
        $v = str_replace(' ', '-', $v);
        $tags[$v] = new Page(TAG . DS . $v . '.page', [], ['*', 'tag']);
    }
    return $tags;
}

Hook::set('*.query', 'fn_page_query');
Hook::set('*.tags', 'fn_page_tags');

function fn_route_tag($path = "", $step = 1) {
    global $language, $site, $url;
    $step = $step - 1;
    $chops = explode('/', $path);
    $state = Extend::state(Path::D(__DIR__));
    $sort = $state['sort'];
    $chunk = $state['chunk'];
    $ss = array_pop($chops); // the tag slug
    $sss = array_pop($chops); // the tag path
    // Based on `lot\extend\page\index.php`
    $elevator = [
        'direction' => [
           '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
           '-2' => [
                2 => ['rel' => null]
            ],
           '-1' => [
                1 => Elevator::WEST,
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => Elevator::EAST,
                2 => ['rel' => 'next']
            ]
        ]
    ];
    // Get tag ID from tag slug…
    if (($id = From::tag($ss)) !== false) {
        // Placeholder…
        Lot::set([
            'pager' => new Elevator([], 1, 0, true, $elevator, $site->is),
            'page' => new Page
        ]);
        // --ditto
        Config::set('page.title', new Anemon([$language->tag, $site->title], ' &#x00B7; '));
        if ($sss === $state['path']) {
            $pages = [];
            $path = implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($files = Get::pages($r, 'page', $sort, 'slug')) {
                $files = array_filter($files, function($v) use($id, $r) {
                    if (!$k = File::exist($r . DS . $v . DS . 'kind.data')) {
                        return false;
                    }
                    $k = ',' . str_replace(' ', "", t(file_get_contents($k), '[', ']')) . ',';
                    if (strpos($k, ',' . $id . ',') !== false) {
                        return true;
                    }
                    return false;
                });
                foreach (Anemon::eat($files)->chunk($chunk, $step) as $v) {
                    $pages[] = new Page($r . DS . $v . '.page');
                }
            }
            if (empty($pages)) {
                $site->is = '404';
                Shield::abort();
            }
            $tag = new Tag(TAG . DS . $ss . '.page');
            $site->is = 'pages';
            $site->tag = $tag;
            if ($tag->description) {
                $site->description = $tag->description;
            }
            Config::set('page.title', new Anemon([$tag->title, $language->tag, $site->title], ' &#x00B7; '));
            Lot::set([
                'pages' => $pages,
                'page' => $tag,
                'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path . '/' . $state['path'] . '/' . $ss, $elevator, $site->is)
            ]);
            Shield::attach('pages/' . $path);
        }
    }
}

Route::lot(['%*%/%i%', '%*%'], 'fn_route_tag');