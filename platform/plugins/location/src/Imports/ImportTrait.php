<?php

namespace Botble\Location\Imports;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;

trait ImportTrait
{
    protected int $totalImported = 0;

    protected array $successes = [];

    public function getTotalImported(): int
    {
        return $this->totalImported;
    }

    public function setTotalImported(): self
    {
        ++$this->totalImported;

        return $this;
    }

    public function onSuccess($item): void
    {
        $this->successes[] = $item;
    }

    public function successes(): Collection
    {
        return collect($this->successes);
    }

    public function transformDate($value, string|null $format = ''): string
    {
        $format = $format ?: config('core.base.general.date_format.date_time');

        try {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format($format);
        } catch (Exception) {
            return Carbon::createFromFormat($format, $value);
        }
    }

    public function getDate($value, $format = 'Y-m-d H:i:s', $default = null): string
    {
        try {
            $date = DateTime::createFromFormat('!' . $format, $value);

            return $date ? $date->format(config('core.base.general.date_format.date_time')) : $value;
        } catch (Exception) {
            return $default;
        }
    }

    public function setValues(array &$row, array $attributes = []): self
    {
        foreach ($attributes as $attribute) {
            $this->setValue(
                $row,
                Arr::get($attribute, 'key'),
                Arr::get($attribute, 'type', 'array'),
                Arr::get($attribute, 'default'),
                Arr::get($attribute, 'from')
            );
        }

        return $this;
    }

    public function setValue(
        array &$row,
        string $key,
        string $type = 'array',
        string|null $default = null,
        string|null $from = null
    ): self {
        $value = Arr::get($row, $from ?: $key, $default);

        switch ($type) {
            case 'array':
                $value = $value ? explode(',', $value) : [];

                break;
            case 'bool':
                if (Str::lower($value) == 'false' || $value == '0' || Str::lower($value) == 'no') {
                    $value = false;
                }
                $value = (bool)$value;

                break;
            case 'datetime':
                if ($value) {
                    if (in_array(gettype($value), ['integer', 'double'])) {
                        $value = $this->transformDate($value);
                    } else {
                        $value = $this->getDate($value);
                    }
                }

                break;
            case 'integer':
                $value = (int)$value;

                break;
        }

        Arr::set($row, $key, $value);

        return $this;
    }
}
