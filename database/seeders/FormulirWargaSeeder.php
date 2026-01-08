<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Resident;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FormulirWargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = storage_path('app/public/Formulir Pendataan Warga Perumahan Sharia Islamic Soreang.xlsx');

        if (!file_exists($file)) {
            $this->command->error("File not found: $file");
            return;
        }

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header row
        array_shift($rows);

        foreach ($rows as $index => $row) {
            // Row index for error reporting
            $line = $index + 2;

            try {
                // Map Basic Data
                $nik = $row[1] ?? null;
                $fullName = $row[2] ?? null;
                
                if (!$fullName) continue; // Skip empty rows

                // --- HOUSE ---
                $block = $row[6] ?? null;
                $number = $row[7] ?? null;

                if (!$block || !$number) {
                    $this->command->warn("Skipping row $line: Missing Block or Number (Name: $fullName)");
                    continue;
                }

                $houseAddress = "Blok $block No. $number"; // Construct address if needed
                
                $houseStatusInput = $row[12] ?? '';
                $houseStatus = match(strtoupper($houseStatusInput)) {
                    'MILIK SENDIRI' => 'occupied', // Assuming mapping
                    'KONTRAK' => 'rented',
                    'KOSONG' => 'empty',
                    default => 'occupied'
                };

                $house = House::firstOrCreate(
                    [
                        'block' => $block,
                        'number' => $number,
                    ],
                    [
                        'address' => $houseAddress,
                        'status' => $houseStatus,
                    ]
                );

                // --- HEAD OF FAMILY ---
                // Date handling
                $dobRaw = $row[4] ?? null;
                $dob = null;
                if ($dobRaw) {
                    if (is_numeric($dobRaw)) {
                         $dob = Date::excelToDateTimeObject($dobRaw)->format('Y-m-d');
                    } else {
                        // Try parsing string date if not serial
                        try {
                            $dob = Carbon::parse($dobRaw)->format('Y-m-d');
                        } catch (\Exception $e) {}
                    }
                }

                $religion = $row[5] ?? null;
                
                // Map Marital Status
                $maritalRaw = strtoupper($row[9] ?? '');
                $maritalStatus = match(true) {
                    str_contains($maritalRaw, 'KAWIN') && !str_contains($maritalRaw, 'BELUM') => 'married',
                    str_contains($maritalRaw, 'BELUM') => 'single',
                    str_contains($maritalRaw, 'CERAI HIDUP') => 'divorced',
                    str_contains($maritalRaw, 'CERAI MATI') => 'widowed',
                    default => 'married'
                };

                $occupation = $row[10] ?? null;
                $phone = $row[11] ?? null;

                $head = Resident::updateOrCreate(
                    ['nik' => $nik], // Unique identifiable
                    [
                        'full_name' => $fullName,
                        'family_card_number' => $nik, // Use NIK as KK number since unavailable in Excel
                        'family_relation' => 'KEPALA KELUARGA',
                        'place_of_birth' => $row[3] ?? null,
                        'date_of_birth' => $dob,
                        'religion' => $religion,
                        'marital_status' => $maritalStatus,
                        'occupation' => $occupation,
                        'phone' => $phone,
                        'is_head_of_family' => true,
                        'status' => 'permanent', // Default to permanent
                    ]
                );
                
                // Attach house (Many-to-Many)
                $head->houses()->syncWithoutDetaching([$house->id]);

                // --- FAMILY MEMBERS ---
                // Mapping chunks: [Name, Relation, Gender, Age, Occupation, School]
                // Starts at col 15. Stride is 7 (cols 15-21, 22-28, etc)
                $familyStartCols = [15, 22, 29, 36, 43];
                
                foreach ($familyStartCols as $startCol) {
                    $famName = $row[$startCol] ?? null;
                    if (!$famName) continue;

                    $famRelation = $row[$startCol + 1] ?? null;
                    $famAge = $row[$startCol + 3] ?? null;
                    $famJob = $row[$startCol + 4] ?? null;

                    // Estimate DOB from Age
                    $famDob = null;
                    if ($famAge && is_numeric($famAge)) {
                        $famDob = Carbon::now()->subYears($famAge)->format('Y-m-d');
                    }

                    $familyMember = Resident::create([
                        'nik' => $nik . '-' . $startCol . '-' . mt_rand(100, 999), // Generate placeholder NIK
                        'full_name' => $famName,
                        'family_card_number' => $nik, // Link via Head's NIK as KK? Or just leave blank.
                        'family_relation' => $famRelation, // Using this based on previous view
                        'is_head_of_family' => false,
                        'date_of_birth' => $famDob,
                        'occupation' => $famJob,
                        'status' => 'permanent',
                    ]);
                    
                    // Attach house
                    $familyMember->houses()->syncWithoutDetaching([$house->id]);
                }

                // --- VEHICLES ---
                // Starts at col 49? No, 49 is "Do you have vehicle?".
                // First vehicle data at 50 (Type), 51 (Brand), 52 (Plate).
                // Next at 54 (Type), 55 (Brand), 56 (Plate).
                // Stride is 4 (Type, Brand, Plate, More?) -> 50, 54, 58, 62, 66, 70
                $vehicleStartCols = [50, 54, 58, 62, 66, 70];
                
                foreach ($vehicleStartCols as $vCol) {
                    $vType = $row[$vCol] ?? null;
                    $vBrand = $row[$vCol + 1] ?? null;
                    $vPlate = $row[$vCol + 2] ?? null;

                    if ($vPlate || ($vType && $vBrand)) {
                        // Create vehicle if plate or type/brand exists
                        Vehicle::create([
                            'resident_id' => $head->id,
                            'license_plate' => $vPlate ?? 'UNKNOWN',
                            'brand' => $vBrand,
                            'vehicle_type' => $vType, // car/motorcycle? Need to check DB.
                        ]);
                    }
                }

            } catch (\Exception $e) {
                $this->command->warn("Error processing row $line: " . $e->getMessage());
            }
        }
    }
}
