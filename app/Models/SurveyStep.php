<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyStep extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function survey(){
        return $this->belongsTo('App\Models\Survey');
    }

    public function items(){
        return $this->hasMany('App\Models\SurveyItem');
    }
}
