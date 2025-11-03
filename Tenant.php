<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_name',
        'phone_number',
        'country_code',
        'flat_id',
        'aadhar_image',
        'rent_start_date'
    ];

    /**
     * A tenant belongs to a flat.
     */
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function getAadharImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
  
        return 'storage/' . $value;
    }
}
