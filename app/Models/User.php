<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property UserBalance     $balance
 * @property UserTransaction $transaction
 */
class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    private function transactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class);
    }

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
    ];
}
