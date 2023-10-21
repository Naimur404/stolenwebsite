<?php

namespace Botble\Base\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface BaseModel
{
    public static function bootHasUuidsOrIntegerIds(): void;

    public function newUniqueId(): string;

    public function getKeyType(): string;

    public function getKey();

    public static function determineIfUsingUuidsForId(): bool;

    public static function getTypeOfId(): string;

    public function metadata(): MorphMany;

    public function getMetaData(string $key, bool $single = false): array|string|null;
}
