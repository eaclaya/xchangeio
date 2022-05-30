<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    public function steps(){
        return $this->hasMany('App\Models\SurveyStep')->orderBy('sort_number', 'asc');
    }

    public function items(){
        return $this->hasMany('App\Models\SurveyItem')->orderBy('sort_number', 'asc');
    }
}
