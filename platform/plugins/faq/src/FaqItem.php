<?php

namespace Botble\Faq;

class FaqItem
{
    public function __construct(protected string $question, protected string $answer)
    {
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
