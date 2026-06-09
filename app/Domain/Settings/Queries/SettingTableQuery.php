<?php

// app/Domain/Settings/Queries/SettingTableQuery.php
namespace App\Domain\Settings\Queries;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

class SettingTableQuery
{
    public function builder(): Builder
    {
        return Setting::query()->select(['id', 'key', 'value', 'created_at', 'updated_at']);
    }
}
