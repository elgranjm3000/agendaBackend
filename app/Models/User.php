<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Especifica la conexión a la base de datos secundaria
     */
    protected $connection = 'secondary';

    /**
     * Especifica el nombre de la tabla
     */
    protected $table = 'ta_users';

        protected $primaryKey = 'id_user';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol',
        'id_office',
        'last_login_at',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
   protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function getAuthIdentifier()
    {
        return $this->id_user;
    }

    public function getAuthIdentifierName()
    {
        return 'id_user';
    }

    // Accessor para que ->id funcione
    public function getIdAttribute()
    {
        return $this->id_user;
    }


 public function createToken(string $name, array $abilities = ['*'])
    {
        // Crear el token forzando la conexión principal
        $token = \App\Models\PersonalAccessToken::on('mysql')->create([
            'name' => $name,
            'tokenable_id' => $this->getAuthIdentifier(),
            'token' => hash('sha256', $plainTextToken = \Illuminate\Support\Str::random(40)),
            'tokenable_type' => get_class($this),
            'abilities' => $abilities,
        ]);

        return new \Laravel\Sanctum\NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    public function getRoleAttribute()
    {
        $roleMap = [
            1 => 'owner',
            2 => 'manager',
            3 => 'staff',
            4 => 'viewer',
        ];
        return $roleMap[$this->attributes['id_rol'] ?? 0] ?? 'viewer';
    }
}


/*class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['owner', 'manager']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'staff']);
    }
}*/