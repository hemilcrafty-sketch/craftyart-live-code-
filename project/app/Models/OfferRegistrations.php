<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferRegistrations extends Model
{
    protected $table = 'offer_registrations';
    protected $connection = 'mysql';
    use HasFactory;
}
