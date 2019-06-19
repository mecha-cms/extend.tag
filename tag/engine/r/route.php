<?php namespace _\route;

function tag($form) {
    global $config, $language, $url;
    $path = $this[0];
    $current = $url->i;
    // Load default page state(s)…
    $state = \extend('tag');
    $i = $current - 1; // 0-based index…
    $chops = \explode('/', $path);
    $sort = $config->tag('sort') ?? $config->page('sort') ?? [1, 'path'];
    $chunk = $config->tag('chunk') ?? $config->page('chunk') ?? 5;
    $t = \array_pop($chops); // The tag slug
    $p = \array_pop($chops); // The tag path
    $has_tags = false;
    // Get tag ID from tag slug…
    if (null !== ($id = \From::tag($t))) {
        $GLOBALS['t'][] = $language->tag;
        if ($p === $state['path']) {
            \Config::set('sort', $sort);
            \Config::set('chunk', $chunk);
            $path = \implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($file = \File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new \Page($file);
            }
            $pages = \Get::pages($r, 'page', $sort, 'path');
            if ($pages->count() > 0) {
                $pages = $pages->is(function($v) use(&$has_tags, $id) {
                    if (\is_file($k = \Path::F($v) . DS . 'kind.data')) {
                        $k = \e(\file_get_contents($k));
                    } else if (!$k = \Page::apart(\file_get_contents($v), 'kind', true)) {
                        return false;
                    }
                    $k = ',' . \implode(',', (array) $k) . ',';
                    $has_tags = true;
                    return \strpos($k, ',' . $id . ',') !== false;
                });
            }
            if ($f = \File::exist([
                TAG . DS . $t . '.page',
                TAG . DS . $t . '.archive'
            ])) {
                $tag = new \Tag($f);
            }
            \Config::set([
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => true,
                    'tags' => true
                ],
                'has' => [
                    'page' => true,
                    'pages' => $pages->count() > 0,
                    'parent' => true
                ]
            ]);
            $path = '/' . $path . '/' . $p . '/' . $t;
            $GLOBALS['t'][] = $tag->title;
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], $url . $path);
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            $GLOBALS['page'] = $tag;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            $GLOBALS['parent'] = $page;
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                \Config::set('is.error', 404);
                \Config::set('has', [
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ]);
                $GLOBALS['t'][] = $language->isError;
                $this->view('404' . $path . '/' . $current);
            }
            \Config::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            $this->status(200);
            $this->view('pages' . $path . '/' . $current);
        }
    }
}

\Route::over('*', __NAMESPACE__ . "\\tag", 20);