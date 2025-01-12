<?php

namespace Terra;

use Timber\Timber;

class PageBuilder {

    protected $context;

    public function __construct() {
        $this->context = Timber::context();
        $this->context["props"] = $this->props;
    }

    public function render() {
        return Timber::render(
            "pages/{$this->page_id}/{$this->page_id}.template.twig",
            $this->context
        );
    }
}