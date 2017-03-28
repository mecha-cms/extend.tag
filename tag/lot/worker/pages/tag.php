<aside class="s">
  <section class="s-search">
    <h3><?php echo $language->search; ?></h3>
    <form id="form.s.search" class="search" action="<?php echo $url->current; ?>" method="get">
      <p><?php echo Form::text('q', Request::get('q', ""), null, ['classes' => ['input']]) . ' ' . Form::submit(null, null, $language->search, ['classes' => ['button']]); ?></p>
    </form>
  </section>
  <?php if ($__pager[0]): ?>
  <section class="s-nav">
    <h3><?php echo $language->navigation; ?></h3>
    <p><?php echo $__pager[0]; ?></p>
  </section>
  <?php endif; ?>
</aside>
<main class="m">
  <section class="m-buttons">
    <p>
      <?php $__links = [HTML::a('&#x2795; ' . $language->tag, $__state->path . '/::s::/' . $__path, false, ['classes' => ['button', 'set']])]; ?>
      <?php echo implode(' ', Hook::fire('panel.a.tags', [$__links])); ?>
    </p>
  </section>
  <?php echo $__message; ?>
  <section class="m-pages">
    <?php if ($__pages[0]): ?>
    <?php foreach ($__pages[1] as $k => $v): ?>
    <?php $s = $__pages[0][$k]->url; ?>
    <article class="page <?php echo 'on-' . $v->state; ?><?php if ($config->shield === $v->id): ?> is-current<?php endif; ?>" id="page-<?php echo $v->id; ?>">
      <header>
        <h3><?php echo $v->title; ?></h3>
      </header>
      <section><p><?php echo To::snippet($v->description, true, $__state->snippet); ?></p></section>
      <footer>
        <p>
        <?php

        $__links = [
            HTML::a($language->edit, $s),
            HTML::a($language->delete, str_replace('::g::', '::r::', $s) . HTTP::query(['token' => $__token]))
        ];

        echo implode(' &#x00B7; ', Hook::fire('panel.a.tag', [$__links]));

        ?>
        </p>
      </footer>
    </article>
    <?php endforeach; ?>
    <?php else: ?>
    <p><?php echo $language->message_info_void($language->tags); ?></p>
    <?php endif; ?>
  </section>
</main>