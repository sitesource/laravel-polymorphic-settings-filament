<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class TestTeam extends Model
{
    protected $table = 'test_teams';

    protected $guarded = [];
}
