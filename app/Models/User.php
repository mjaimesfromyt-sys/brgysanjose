<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable([
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'role',
        'status',
        'contact_no',
        'address',
        'purok',
        'declared_type',
        'resident_type',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isResident(): bool
    {
        return $this->resident_type === 'resident';
    }

    public function isNonResident(): bool
    {
        return $this->resident_type === 'non_resident';
    }

    public function isResidentRole(): bool
    {
        return $this->role === 'resident';
    }

    public function isOfficial(): bool
    {
        return $this->role === 'official';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function equipmentRentals(): HasMany
    {
        return $this->hasMany(EquipmentRental::class);
    }
    
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parts = array_filter([
                    $this->first_name,
                    $this->middle_name,
                    $this->last_name,
                    $this->suffix,
                ]);
                return implode(' ', $parts);
            },
        );
    }
}
