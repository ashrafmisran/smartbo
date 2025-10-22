<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class FamilyAnalysisService
{
    public function analyzeFamilyRelationships(array $pengundi): array
    {
        try {
            // Prepare data for OpenAI analysis
            $pengundiData = $this->preparePengundiData($pengundi);
            
            // Create prompt for Malaysian family analysis
            $prompt = $this->createAnalysisPrompt($pengundiData);
            
            // Send to OpenAI
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert in Malaysian naming conventions and family relationship analysis. You understand Malaysian Islamic naming patterns, Chinese naming conventions, and Indian naming systems. Analyze voter data to identify potential family relationships based on names, addresses, and demographic patterns.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ]);

            $analysis = $response->choices[0]->message->content;
            
            // Parse the response and structure it
            return $this->parseAnalysisResponse($analysis, $pengundi);
            
        } catch (\Exception $e) {
            Log::error('Family Analysis Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'summary' => 'Ralat berlaku semasa analisis keluarga',
                'relationships' => [],
                'confidence' => 0
            ];
        }
    }

    private function preparePengundiData(array $pengundi): array
    {
        return array_map(function ($person) {
            return [
                'name' => $person['Nama_Penuh'] ?? '',
                'ic' => $person['No_KP_Baru'] ?? '',
                'address' => trim(($person['Alamat_1'] ?? '') . ' ' . ($person['Alamat_2'] ?? '') . ' ' . ($person['Alamat_3'] ?? '')),
                'age' => $this->calculateAge($person['No_KP_Baru'] ?? ''),
                'gender' => $this->determineGender($person['No_KP_Baru'] ?? ''),
                'birth_place' => $this->extractBirthPlace($person['No_KP_Baru'] ?? ''),
            ];
        }, $pengundi);
    }

    private function createAnalysisPrompt(array $pengundiData): string
    {
        $prompt = "Analyze the following Malaysian voters data to identify potential family relationships:\n\n";
        
        foreach ($pengundiData as $index => $person) {
            $prompt .= sprintf(
                "Person %d:\n- Name: %s\n- IC: %s\n- Address: %s\n- Age: %d\n- Gender: %s\n- Birth Place: %s\n\n",
                $index + 1,
                $person['name'],
                $person['ic'],
                $person['address'],
                $person['age'],
                $person['gender'],
                $person['birth_place']
            );
        }

        $prompt .= "Based on Malaysian naming conventions, please:\n";
        $prompt .= "1. Identify potential family groups (parents, children, siblings, spouses)\n";
        $prompt .= "2. Consider Islamic naming patterns (bin/binti relationships)\n";
        $prompt .= "3. Consider Chinese family names and generational patterns\n";
        $prompt .= "4. Consider Indian naming conventions\n";
        $prompt .= "5. Look at address similarities and age differences\n";
        $prompt .= "6. Provide confidence levels (high/medium/low) for each relationship\n\n";
        $prompt .= "Format your response as a JSON structure with family groups and relationship explanations.";

        return $prompt;
    }

    private function calculateAge(string $ic): int
    {
        if (strlen($ic) < 6) return 0;
        
        $year = substr($ic, 0, 2);
        $month = substr($ic, 2, 2);
        $day = substr($ic, 4, 2);
        
        // Determine century (assumes 00-30 = 2000s, 31-99 = 1900s)
        $fullYear = ($year <= 30) ? 2000 + $year : 1900 + $year;
        
        $birthDate = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $fullYear, $month, $day));
        if (!$birthDate) return 0;
        
        return $birthDate->diff(new \DateTime())->y;
    }

    private function determineGender(string $ic): string
    {
        if (strlen($ic) < 12) return 'Unknown';
        
        $lastDigit = (int) substr($ic, -1);
        return ($lastDigit % 2 === 0) ? 'Female' : 'Male';
    }

    private function extractBirthPlace(string $ic): string
    {
        if (strlen($ic) < 8) return 'Unknown';
        
        $placeCode = substr($ic, 6, 2);
        
        // Map common birth place codes (simplified)
        $placeCodes = [
            '01' => 'Johor', '02' => 'Kedah', '03' => 'Kelantan', '04' => 'Melaka',
            '05' => 'Negeri Sembilan', '06' => 'Pahang', '07' => 'Penang', '08' => 'Perak',
            '09' => 'Perlis', '10' => 'Selangor', '11' => 'Terengganu', '12' => 'Sabah',
            '13' => 'Sarawak', '14' => 'Wilayah Persekutuan KL', '15' => 'Labuan', '16' => 'Putrajaya'
        ];
        
        return $placeCodes[$placeCode] ?? 'Other/Unknown';
    }

    private function parseAnalysisResponse(string $analysis, array $originalData): array
    {
        try {
            // Try to extract JSON from the response
            $jsonStart = strpos($analysis, '{');
            $jsonEnd = strrpos($analysis, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonStr = substr($analysis, $jsonStart, $jsonEnd - $jsonStart + 1);
                $parsed = json_decode($jsonStr, true);
                
                if ($parsed) {
                    return [
                        'success' => true,
                        'summary' => $this->generateSummary($parsed),
                        'relationships' => $parsed['family_groups'] ?? [],
                        'confidence' => $this->calculateOverallConfidence($parsed),
                        'raw_analysis' => $analysis,
                        'ai_insights' => $parsed['insights'] ?? []
                    ];
                }
            }
            
            // Fallback: return basic analysis
            return [
                'success' => true,
                'summary' => 'Analisis AI telah selesai. Lihat butiran untuk maklumat lanjut.',
                'relationships' => [],
                'confidence' => 50,
                'raw_analysis' => $analysis,
                'ai_insights' => []
            ];
            
        } catch (\Exception $e) {
            Log::error('Analysis parsing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Gagal memproses respons AI',
                'summary' => 'Analisis tidak dapat diselesaikan sepenuhnya',
                'relationships' => [],
                'confidence' => 0
            ];
        }
    }

    private function generateSummary(array $analysis): string
    {
        $familyCount = count($analysis['family_groups'] ?? []);
        
        if ($familyCount === 0) {
            return 'Tiada hubungan keluarga yang jelas dikesan dalam data ini.';
        }
        
        return sprintf(
            'Dikenal pasti %d kumpulan keluarga yang berpotensi berdasarkan analisis nama dan maklumat demografi.',
            $familyCount
        );
    }

    private function calculateOverallConfidence(array $analysis): int
    {
        $confidenceLevels = [];
        
        foreach ($analysis['family_groups'] ?? [] as $group) {
            foreach ($group['relationships'] ?? [] as $relationship) {
                $confidence = $relationship['confidence'] ?? 'medium';
                
                switch (strtolower($confidence)) {
                    case 'high':
                        $confidenceLevels[] = 80;
                        break;
                    case 'medium':
                        $confidenceLevels[] = 60;
                        break;
                    case 'low':
                        $confidenceLevels[] = 40;
                        break;
                    default:
                        $confidenceLevels[] = 50;
                }
            }
        }
        
        return empty($confidenceLevels) ? 50 : (int) array_sum($confidenceLevels) / count($confidenceLevels);
    }
}