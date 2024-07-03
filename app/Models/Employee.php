<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     title="Employee",
 *     description="Employee model",
 *     @OA\Xml(
 *         name="Employee"
 *     )
 * )
 */
class Employee extends Model
{
    use HasFactory, Notifiable, HasApiTokens, Authenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @OA\Property(property="phone_no", type="string", example="1234567890"),
     * @OA\Property(property="username", type="string", example="john_doe"),
     * @OA\Property(property="password", type="string", format="password", example="password"),
     * @OA\Property(property="commission_sales", type="number", format="float", example="0.15"),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="role_id", type="integer", example="1"),
     * @OA\Property(property="registered_at", type="string", format="date-time", example="2023-07-03T04:41:42+00:00"),
     */
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Boot function to automatically handle events during model creation and updating.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $model->code = app(CodeGeneratorService::class)->generateCode("EMP", Employee::class);
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * Define the attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Define a relationship with sales created by this employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales()
    {
        return $this->hasMany(Sales::class);
    }

    /**
     * Define a relationship with the role of this employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Define a relationship with the sales commission logs of this employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales_point()
    {
        return $this->hasMany(EmployeeSalesCommissionLog::class);
    }

    /**
     * Define a relationship with the salaries of this employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salary()
    {
        return $this->hasMany(EmployeeSalary::class);
    }
}
