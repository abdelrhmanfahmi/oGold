<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Filters\Filters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'dateOfBirth',
        'country',
        'state',
        'city',
        'address',
        'phone',
        'bankName',
        'bankAddress',
        'bankSwiftCode',
        'bankAccount',
        'accountName',
        'type',
        'co_auth',
        'trading_api_token',
        'trading_uuid'
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
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeFilter($query, Filters $filter)
    {
        return $filter->apply($query);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function address_books()
    {
        return $this->hasMany(AddressBook::class);
    }
}
