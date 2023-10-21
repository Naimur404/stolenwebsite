<?php

namespace Botble\Faq\Contracts;

use Botble\Faq\FaqCollection;

interface Faq
{
    public function registerSchema(FaqCollection $faqs): void;
}
