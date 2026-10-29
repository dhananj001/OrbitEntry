<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Model representing a Koha borrower/patron in the external Koha ILS database.
 *
 * Read-Only Protection:
 * This model accesses the Koha 'borrowers' table via the secondary 'koha'
 * database connection. To ensure strict data integrity and protect the Koha ILS
 * from accidental mutations from OrbitEntry, write operations are guarded.
 *
 * @property int $borrowernumber
 * @property string|null $cardnumber
 * @property string|null $surname
 * @property string|null $firstname
 * @property string|null $preferred_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $mobile
 * @property string $branchcode
 * @property string $categorycode
 * @property-read string $full_name
 * @property-read string $display_identifier
 * @property-read string $category_label
 */
class KohaPatron extends Model
{
    use HasFactory;

    /**
     * The database connection name for this model.
     *
     * @var string
     */
    protected $connection = 'koha';

    /**
     * The table associated with the model in Koha.
     *
     * @var string
     */
    protected $table = 'borrowers';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'borrowernumber';

    /**
     * The attributes that aren't mass assignable.
     * All attributes guarded by default in write attempts, unshielded for factory/test seeding.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     * Koha manages its own timestamp columns (updated_on).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dateofbirth' => 'date',
            'dateenrolled' => 'date',
            'dateexpiry' => 'date',
            'updated_on' => 'datetime',
            'lastseen' => 'datetime',
            'flags' => 'integer',
            'lost' => 'boolean',
            'debarred' => 'date',
        ];
    }

    /**
     * Get the patron's readable full name.
     */
    public function getFullNameAttribute(): string
    {
        $name = trim(($this->firstname ?? '').' '.($this->surname ?? ''));

        if (! empty($this->preferred_name) && $this->preferred_name !== $name) {
            return "{$name} ({$this->preferred_name})";
        }

        return $name ?: ($this->cardnumber ?? 'Unknown Patron');
    }

    /**
     * Get a human-friendly category label for Koha codes.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match (strtoupper($this->categorycode ?? '')) {
            'STD' => 'Student',
            'STF' => 'Staff / Faculty',
            'RES' => 'Researcher',
            'GST' => 'Guest',
            default => $this->categorycode ?: 'Patron',
        };
    }

    /**
     * Human readable display identifier (e.g. "Ambika Patil (Student - GECW)").
     */
    public function getDisplayIdentifierAttribute(): string
    {
        $category = $this->category_label;
        $branch = $this->branchcode ? " - {$this->branchcode}" : '';

        return "{$this->full_name} ({$category}{$branch})";
    }

    /**
     * Safely search or find a patron by card number.
     */
    public static function findByCardNumber(?string $cardNumber): ?self
    {
        if (empty($cardNumber)) {
            return null;
        }

        return static::where('cardnumber', strtoupper(trim($cardNumber)))->first();
    }

    /**
     * Guard: Prevent writing to the Koha database from OrbitEntry.
     *
     * @param  array<mixed>  $options
     */
    public function save(array $options = []): bool
    {
        if (app()->environment('testing')) {
            return parent::save($options);
        }

        throw new RuntimeException('Direct writes to the Koha database from OrbitEntry are restricted for safety.');
    }

    /**
     * Guard: Prevent deleting records in the Koha database from OrbitEntry.
     */
    public function delete(): ?bool
    {
        if (app()->environment('testing')) {
            return parent::delete();
        }

        throw new RuntimeException('Direct deletions in the Koha database from OrbitEntry are restricted for safety.');
    }
}
