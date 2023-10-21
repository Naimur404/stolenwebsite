<?php

namespace Botble\Table\Abstracts\Concerns;

trait HasConfirmation
{
    protected bool $isConfirmation = false;

    protected string $confirmationModalTitle;

    protected string $confirmationModalMessage;

    protected string $confirmationModalButton;

    protected string $confirmationModalCancelButton;

    public function confirmation($isConfirmation = true): static
    {
        $this->isConfirmation = $isConfirmation;

        return $this;
    }

    public function isConfirmation(): bool
    {
        return $this->isConfirmation;
    }

    public function confirmationModalTitle(string $title): static
    {
        $this->confirmationModalTitle = $title;

        return $this;
    }

    public function getConfirmationModalTitle(): string
    {
        return $this->confirmationModalTitle ?? trans('core/table::table.confirm_bulk_action');
    }

    public function confirmationModalMessage(string $message): static
    {
        $this->confirmationModalMessage = $message;

        return $this;
    }

    public function getConfirmationModalMessage(): string
    {
        return $this->confirmationModalMessage ?? trans('core/table::table.confirm_bulk_message');
    }

    public function confirmationModalButton(string $confirmButton): static
    {
        $this->confirmationModalButton = $confirmButton;

        return $this;
    }

    public function getConfirmationModalButton(): string
    {
        return $this->confirmationModalButton ?? trans('core/base::base.yes');
    }

    public function confirmationModalCancelButton(string $cancelButton): static
    {
        $this->confirmationModalCancelButton = $cancelButton;

        return $this;
    }

    public function getConfirmationModalCancelButton(): string
    {
        return $this->confirmationModalCancelButton ?? trans('core/table::table.cancel');
    }
}
