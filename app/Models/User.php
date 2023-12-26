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
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles , SoftDeletes;

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
        'phone',
        'type',
        'co_auth',
        'trading_api_token',
        'trading_uuid',
        'client_trading_id'
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

    public function buy_golds()
    {
        return $this->hasMany(BuyGold::class);
    }

    public function sell_golds()
    {
        return $this->hasMany(SellGold::class);
    }

    public function address_books()
    {
        return $this->hasMany(AddressBook::class);
    }

    public function bank_details()
    {
        return $this->hasMany(BankDetails::class);
    }

    public function delete_requests()
    {
        return $this->hasOne(DeleteRequest::class);
    }

    public function kyc()
    {
        return $this->hasMany(KYC::class);
    }
}
