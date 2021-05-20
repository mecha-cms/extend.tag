<?php

class Tags extends Pages {

    public $page = null; // The parent page

    public function page(string $path) {
        $tag = new Tag($path);
        $tag->page = $this->page;
        return $tag;
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . DS . 'tag';
        return parent::from(...$lot);
    }

}
