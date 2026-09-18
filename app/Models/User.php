<?php

namespace App\Models;

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
    'position',
    'status',
    'contact_no',
    'address',
    'purok',
    'profile_picture',
    'declared_type', 'birthdate', 'gender', 'civil_status', 'citizenship', 'religion', 'length_of_stay', 'is_voter', 'id_type', 'id_number', 'id_photo_front', 'emergency_contact_name', 'emergency_contact_phone', 'monthly_income', 'employment_status', 'is_senior', 'is_solo_parent', 'is_pwd', 'is_first_time_jobseeker',
    'resident_type',
    'verified_at',
    'verified_by',
    'rejection_reason',
    'email_otp_hash',
    'email_otp_expires_at',
    'email_otp_attempts',
    'google_id',
    'avatar',
    'registration_method',
])]

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
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

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: function () {
                $f = !empty($this->first_name) ? strtoupper(substr($this->first_name, 0, 1)) : '';
                $l = !empty($this->last_name) ? strtoupper(substr($this->last_name, 0, 1)) : '';
                return ($f . $l) ?: 'U';
            }
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $photo = $this->profile_picture ?: $this->avatar;
                
                if (!empty($photo)) {
                    if (filter_var($photo, FILTER_VALIDATE_URL)) {
                        return $photo;
                    }
                    return asset($photo);
                }

                return null;
            }
        );
    }
    public function getAgeAttribute(): ?int
    {
        return $this->birthdate ? \Carbon\Carbon::parse($this->birthdate)->age : null;
    }
}
