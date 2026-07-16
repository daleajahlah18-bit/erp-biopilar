<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\EnterpriseAuditTrail;

use Kyslik\ColumnSortable\Sortable;
class User extends Authenticatable
{
    use Sortable;
    public $sortable = ['id', 'name',
        'email',
        'password',
        'employee_id',
        'photo',
        'department',
        'position',
        'phone_number',
        'status',
        'last_login_at',
        'last_login_ip', 'created_at', 'updated_at'];

    use HasApiTokens, HasFactory, Notifiable, HasRoles, EnterpriseAuditTrail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'photo',
        'department',
        'position',
        'phone_number',
        'status',
        'last_login_at',
        'last_login_ip'
    ];

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
