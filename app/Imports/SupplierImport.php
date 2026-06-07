<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class SupplierImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected int $imported = 0;
    protected int $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $existing = Supplier::where('email', $row['email'])
                ->orWhere('phone', $row['phone'])
                ->first();

            if ($existing) {
                $this->skipped++;
                continue;
            }

            Supplier::create([
                'name'        => $row['name'],
                'email'       => $row['email'] ?? null,
                'phone'       => $row['phone'] ?? null,
                'address'     => $row['address'] ?? null,
                'city'        => $row['city'] ?? null,
                'country'     => $row['country'] ?? null,
                'tax_number'  => $row['tax_number'] ?? null,
            ]);
            $this->imported++;
        }
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int { return $this->skipped; }
}
