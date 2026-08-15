<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Read-only mirror of cosecsa-api's TotYear (shared DB) — used here only to
// populate the Trainer edit form's "ToT Years Attended" checkboxes.
class TotYear extends Model
{
    protected $table = 'tot_years';
}
