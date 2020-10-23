<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name', 'contact_number', 'email', 'image', 'hobbies',
    ];
    // add as parent category in category_parent
    public function parents(){
        return $this->belongsToMany(Employee::class,'employee_parent','employee_id','parent_id');
    }

    public function children(){
    	return $this->belongsToMany(Employee::class, 'employee_parent', 'employee_id', 'parent_id');
    }
}
