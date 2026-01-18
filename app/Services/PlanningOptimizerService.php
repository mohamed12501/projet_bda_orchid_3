<?php

namespace App\Services;

use App\Models\PlanningRun;
use App\Models\PlanningItem;
use App\Models\Creneau;
use App\Models\Module;
use App\Models\Salle;
use App\Models\Inscription;
use App\Models\Professeur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlanningOptimizerService
{
    protected $moduleStudentsCache = [];
    protected $moduleGroupStudentsCache = []; // Cache students by module and group
    protected $creneauUsageCache = [];
    protected $salleUsageCache = [];
    protected $professorSurveillanceCache = [];
    protected $creneauxCache = []; // Cache creneaux to avoid repeated finds
    protected $groupDailyExamsCache = []; // Track exams per group per day
    
    /**
     * Generate a feasible schedule for a planning run
     */
    public function optimize(PlanningRun $run, int $periodeId): array
    {
        // Set time limit
        set_time_limit(300); // 5 minutes
        
        $startTime = microtime(true);
        $run->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            // Preload all data to avoid N+1 queries
            $this->preloadData($run);
            
            // Get modules to schedule based on scope
            $modules = $this->getModulesForRun($run);
            
            // Get available rooms
            $salles = $this->getAvailableSalles($run);
            
            // Get period dates
            $periode = \App\Models\PeriodeExamen::findOrFail($periodeId);
            
            // Validate period has dates
            if (!$periode->date_debut || !$periode->date_fin) {
                throw new \Exception(
                    sprintf(
                        'La période sélectionnée "%s" n\'a pas de dates définies. Veuillez configurer les dates de début et de fin.',
                        $periode->nom ?? 'Sans nom'
                    )
                );
            }
            
            Log::info('Period details', [
                'periode_id' => $periodeId,
                'periode_nom' => $periode->nom,
                'date_debut' => $periode->date_debut,
                'date_fin' => $periode->date_fin
            ]);
            
            $dates = $this->generateDates($periode->date_debut, $periode->date_fin);
            
            Log::info('Generated dates', [
                'count' => count($dates),
                'first_date' => !empty($dates) ? $dates[0]->format('Y-m-d') : 'none',
                'last_date' => !empty($dates) ? end($dates)->format('Y-m-d') : 'none'
            ]);
            
            // Check if we have dates
            if (empty($dates)) {
                throw new \Exception(
                    sprintf(
                        'Aucune date de travail générée pour la période "%s" (du %s au %s). Vérifiez que la période contient des jours ouvrables (lundi-vendredi).',
                        $periode->nom ?? 'Sans nom',
                        Carbon::parse($periode->date_debut)->format('d/m/Y'),
                        Carbon::parse($periode->date_fin)->format('d/m/Y')
                    )
                );
            }
            
            // Generate time slots (creneaux) - batch creation
            $creneaux = $this->generateCreneaux($dates);
            
            Log::info('Generated creneaux', ['count' => count($creneaux)]);
            
            // Cache creneaux by ID for quick lookup
            foreach ($creneaux as $creneau) {
                $this->creneauxCache[$creneau->id_creneau] = $creneau;
            }
            
            // Clear existing items for this run
            PlanningItem::where('run_id', $run->id)->delete();
            
            // Reset caches (but not moduleGroupStudentsCache - we need it)
            $this->creneauUsageCache = [];
            $this->salleUsageCache = [];
            $this->professorSurveillanceCache = [];
            $this->groupDailyExamsCache = [];
            
            // Schedule modules by groups
            $scheduled = [];
            $conflicts = [];
            
            // Check if we have modules
            if ($modules->isEmpty()) {
                throw new \Exception('Aucun module trouvé pour cette portée. Vérifiez la configuration du run (département ou formation).');
            }
            
            // Check if we have salles
            if ($salles->isEmpty()) {
                throw new \Exception('Aucune salle disponible. Veuillez créer des salles dans le système.');
            }
            
            // Check if we have creneaux (should not happen after date validation, but double-check)
            if (empty($creneaux)) {
                throw new \Exception(
                    sprintf(
                        'Aucun créneau généré pour la période sélectionnée "%s". Cela peut être dû à un problème de création dans la base de données.',
                        $periode->nom ?? 'Sans nom'
                    )
                );
            }
            
            // Group modules by formation for better scheduling (similar formations on same days)
            $modulesByFormation = $modules->groupBy('id_formation');
            
            Log::info('Grouped modules by formation', [
                'formations_count' => $modulesByFormation->count(),
                'total_modules' => $modules->count()
            ]);
            
            // Schedule each formation's modules together
            foreach ($modulesByFormation as $formationId => $formationModules) {
                Log::info('Scheduling formation', [
                    'formation_id' => $formationId,
                    'modules_count' => $formationModules->count()
                ]);
                
                foreach ($formationModules as $module) {
                    // Get all groups for this module
                    $groups = $this->getGroupsForModule($module);
                    
                    if ($groups->isEmpty()) {
                        $conflicts[] = "Module {$module->nom} ({$module->id_module}) - Aucun groupe trouvé";
                        continue;
                    }
                    
                    foreach ($groups as $group) {
                        $items = $this->scheduleModuleForGroup($run, $module, $group, $salles, $creneaux);
                        
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $scheduled[] = $item;
                                // Update caches
                                $this->updateCaches($item);
                            }
                        } else {
                            $conflicts[] = "Module {$module->nom} ({$module->id_module}) - Groupe {$group->nom} ({$group->id_groupe})";
                        }
                    }
                }
            }
            
            // Calculate metrics
            $metrics = $this->calculateMetrics($run, $scheduled, $conflicts, $startTime);
            
            $run->update([
                'status' => 'done',
                'ended_at' => now(),
                'metrics' => $metrics,
            ]);
            
            return [
                'success' => true,
                'scheduled' => count($scheduled),
                'conflicts' => count($conflicts),
                'metrics' => $metrics,
            ];
            
        } catch (\Exception $e) {
            Log::error('Optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $run->update([
                'status' => 'failed',
                'ended_at' => now(),
                'metrics' => ['error' => $e->getMessage()],
            ]);
            
            throw $e;
        }
    }

    /**
     * Preload all necessary data to avoid N+1 queries
     */
    protected function preloadData(PlanningRun $run): void
    {
        // Preload all inscriptions grouped by module
        $inscriptions = DB::table('inscription')
            ->select('id_module', 'id_etudiant')
            ->get()
            ->groupBy('id_module');
        
        foreach ($inscriptions as $moduleId => $moduleInscriptions) {
            $this->moduleStudentsCache[$moduleId] = $moduleInscriptions->pluck('id_etudiant')->toArray();
        }
        
        // Preload students grouped by module and group
        $students = DB::table('etudiant')
            ->select('id_etudiant', 'group_id')
            ->whereNotNull('group_id')
            ->get()
            ->keyBy('id_etudiant');
        
        foreach ($inscriptions as $moduleId => $moduleInscriptions) {
            foreach ($moduleInscriptions as $inscription) {
                $student = $students->get($inscription->id_etudiant);
                if ($student && $student->group_id) {
                    $key = "{$moduleId}_{$student->group_id}";
                    if (!isset($this->moduleGroupStudentsCache[$key])) {
                        $this->moduleGroupStudentsCache[$key] = [];
                    }
                    $this->moduleGroupStudentsCache[$key][] = $student->id_etudiant;
                }
            }
        }
    }

    /**
     * Reset internal caches
     */
    protected function resetCaches(): void
    {
        $this->creneauUsageCache = [];
        $this->salleUsageCache = [];
        $this->professorSurveillanceCache = [];
        // Don't reset moduleGroupStudentsCache - we need it for scheduling
    }

    /**
     * Update caches after scheduling an item
     */
    protected function updateCaches(PlanningItem $item): void
    {
        // Track creneau usage - include group_id if present
        if ($item->group_id) {
            $key = "{$item->creneau_id}_{$item->module_id}_{$item->group_id}";
        } else {
            $key = "{$item->creneau_id}_{$item->module_id}";
        }
        $this->creneauUsageCache[$key] = true;
        
        // Track salle usage
        $salleKey = "{$item->creneau_id}_{$item->salle_id}";
        $this->salleUsageCache[$salleKey] = true;
        
        // Track professor surveillance - use cached creneau
        if (is_array($item->surveillants)) {
            foreach ($item->surveillants as $surveillant) {
                $profId = is_array($surveillant) ? ($surveillant['id_prof'] ?? null) : $surveillant;
                if ($profId) {
                    // Use cached creneau instead of querying database
                    $creneau = $this->creneauxCache[$item->creneau_id] ?? null;
                    if ($creneau && $creneau->date) {
                        // Normalize date to string format for cache key
                        $dateStr = $creneau->date instanceof \Carbon\Carbon 
                            ? $creneau->date->format('Y-m-d') 
                            : $creneau->date;
                        $profKey = "{$profId}_{$dateStr}";
                        $this->professorSurveillanceCache[$profKey] = 
                            ($this->professorSurveillanceCache[$profKey] ?? 0) + 1;
                    }
                }
            }
        }
        
        // Track group daily exams
        if ($item->group_id) {
            $creneau = $this->creneauxCache[$item->creneau_id] ?? null;
            if ($creneau && $creneau->date) {
                $dateStr = $creneau->date instanceof \Carbon\Carbon 
                    ? $creneau->date->format('Y-m-d') 
                    : $creneau->date;
                $groupDayKey = "{$item->group_id}_{$dateStr}";
                $this->groupDailyExamsCache[$groupDayKey] = true;
            }
        }
    }

    /**
     * Get modules to schedule based on run scope
     */
    protected function getModulesForRun(PlanningRun $run): \Illuminate\Database\Eloquent\Collection
    {
        $query = Module::with('formation.departement');
        
        if ($run->scope === 'departement' && $run->dept_id) {
            $query->whereHas('formation', function ($q) use ($run) {
                $q->where('id_dept', $run->dept_id);
            });
        } elseif ($run->scope === 'formation' && $run->formation_id) {
            $query->where('id_formation', $run->formation_id);
        }
        
        return $query->get();
    }

    /**
     * Get available rooms with preloaded equipements
     */
    protected function getAvailableSalles(PlanningRun $run): \Illuminate\Database\Eloquent\Collection
    {
        $salles = Salle::query();
        
        // Only eager load if relationship exists
        if (method_exists(Salle::class, 'equipements')) {
            $salles->with('equipements');
        }
        
        return $salles->get();
    }

    /**
     * Generate dates for the period (excluding weekends)
     */
    protected function generateDates($start, $end): array
    {
        $dates = [];
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        while ($current->lte($endDate)) {
            // Skip weekends
            if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                $dates[] = $current->copy();
            }
            $current->addDay();
        }
        
        return $dates;
    }

    /**
     * Generate time slots (creneaux) for dates - optimized batch creation
     */
    protected function generateCreneaux(array $dates): array
    {
        if (empty($dates)) {
            Log::warning('generateCreneaux: No dates provided');
            return [];
        }
        
        $timeSlots = [
            ['08:00', '09:30'],
            ['09:30', '11:00'],
            ['11:00', '12:30'],
            ['12:30', '14:00'],
            ['14:00', '15:30']
        ];
        
        // Batch check existing creneaux
        $dateRange = array_map(fn($d) => $d->format('Y-m-d'), $dates);
        
        Log::info('generateCreneaux: Checking date range', [
            'date_range' => $dateRange
        ]);
        
        // Get all existing creneaux for the date range  
        $rawCreneaux = DB::table('creneau')->whereIn('date', $dateRange)->get();
        Log::info('generateCreneaux: Raw DB creneaux', [
            'count' => $rawCreneaux->count(),
            'sample' => $rawCreneaux->first() ? [
                'date' => $rawCreneaux->first()->date,
                'heure_debut' => $rawCreneaux->first()->heure_debut,
                'heure_fin' => $rawCreneaux->first()->heure_fin,
            ] : 'none'
        ]);
        
        $allCreneaux = Creneau::whereIn('date', $dateRange)->get();
        
        Log::info('generateCreneaux: Found existing creneaux', [
            'count' => $allCreneaux->count(),
            'sample' => $allCreneaux->first() ? [
                'date' => $allCreneaux->first()->date,
                'date_class' => get_class($allCreneaux->first()->date),
                'heure_debut' => $allCreneaux->first()->heure_debut,
                'heure_fin' => $allCreneaux->first()->heure_fin,
            ] : 'none'
        ]);
        
        $existingCreneaux = $allCreneaux->keyBy(function ($c) {
            // Normalize date format
            $dateStr = $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date;
            // Normalize time formats (remove seconds if present)
            $heureDebut = substr($c->heure_debut, 0, 5);
            $heureFin = substr($c->heure_fin, 0, 5);
            return $dateStr . '_' . $heureDebut . '_' . $heureFin;
        });
        
        $resultCreneaux = [];
        $toInsert = [];
        
        // Check which creneaux need to be created and collect existing ones
        foreach ($dates as $date) {
            foreach ($timeSlots as $slot) {
                $key = $date->format('Y-m-d') . '_' . $slot[0] . '_' . $slot[1];
                
                if ($existingCreneaux->has($key)) {
                    // Already exists, add to results
                    $resultCreneaux[] = $existingCreneaux->get($key);
                } else {
                    // Doesn't exist, prepare for insertion
                    $toInsert[] = [
                        'date' => $date->format('Y-m-d'),
                        'heure_debut' => $slot[0],
                        'heure_fin' => $slot[1],
                        'created_at' => now(),
                    ];
                }
            }
        }
        
        Log::info('generateCreneaux: Before insert', [
            'existing_count' => count($resultCreneaux),
            'to_insert_count' => count($toInsert)
        ]);
        
        // Batch insert missing creneaux if needed
        if (!empty($toInsert)) {
            try {
                DB::table('creneau')->insert($toInsert);
                
                Log::info('generateCreneaux: Successfully inserted new creneaux', [
                    'inserted_count' => count($toInsert)
                ]);
            } catch (\Exception $e) {
                Log::error('generateCreneaux: Failed to insert creneaux', [
                    'error' => $e->getMessage(),
                    'sample_data' => array_slice($toInsert, 0, 2)
                ]);
                throw new \Exception('Erreur lors de la création des créneaux: ' . $e->getMessage());
            }
            
            // Reload all creneaux to get the newly created ones with their IDs
            $allCreneaux = Creneau::whereIn('date', $dateRange)->get();
            
            Log::info('generateCreneaux: Reloaded creneaux after insert', [
                'total_count' => $allCreneaux->count(),
                'sample_creneau' => $allCreneaux->first() ? [
                    'id' => $allCreneaux->first()->id_creneau,
                    'date' => $allCreneaux->first()->date,
                    'date_type' => gettype($allCreneaux->first()->date),
                    'heure_debut' => $allCreneaux->first()->heure_debut,
                    'heure_debut_type' => gettype($allCreneaux->first()->heure_debut),
                    'heure_fin' => $allCreneaux->first()->heure_fin,
                ] : 'no creneaux'
            ]);
            
            // Rebuild resultCreneaux with all creneaux in order
            $resultCreneaux = [];
            $matchCount = 0;
            $noMatchCount = 0;
            
            foreach ($dates as $date) {
                foreach ($timeSlots as $slot) {
                    $creneau = $allCreneaux->first(function($c) use ($date, $slot, &$matchCount, &$noMatchCount) {
                        $dateStr = $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date;
                        $heureDebut = is_string($c->heure_debut) ? substr($c->heure_debut, 0, 5) : $c->heure_debut;
                        $heureFin = is_string($c->heure_fin) ? substr($c->heure_fin, 0, 5) : $c->heure_fin;
                        
                        $match = $dateStr === $date->format('Y-m-d') 
                            && $heureDebut === $slot[0] 
                            && $heureFin === $slot[1];
                        
                        if ($match) {
                            $matchCount++;
                        } else {
                            $noMatchCount++;
                        }
                        
                        return $match;
                    });
                    
                    if ($creneau) {
                        $resultCreneaux[] = $creneau;
                    }
                }
            }
            
            Log::info('generateCreneaux: Rebuild complete', [
                'result_count' => count($resultCreneaux),
                'match_count' => $matchCount,
                'no_match_count' => $noMatchCount
            ]);
        }
        
        Log::info('generateCreneaux: Returning', [
            'count' => count($resultCreneaux),
            'sample' => !empty($resultCreneaux) ? [
                'first_id' => $resultCreneaux[0]->id_creneau ?? 'no id',
                'first_date' => $resultCreneaux[0]->date ?? 'no date',
            ] : 'empty'
        ]);
        
        if (empty($resultCreneaux)) {
            Log::error('generateCreneaux: CRITICAL - No creneaux to return!', [
                'dates_provided' => count($dates),
                'existing_found' => $allCreneaux->count(),
                'attempted_insert' => count($toInsert)
            ]);
        }
        
        // Return as array of Eloquent objects
        return $resultCreneaux;
    }

    /**
     * Get groups for a module (students enrolled in the module)
     */
    protected function getGroupsForModule(Module $module): \Illuminate\Database\Eloquent\Collection
    {
        $studentIds = $this->moduleStudentsCache[$module->id_module] ?? [];
        
        if (empty($studentIds)) {
            return collect();
        }
        
        // Get unique group IDs from students enrolled in this module
        $groupIds = DB::table('etudiant')
            ->whereIn('id_etudiant', $studentIds)
            ->whereNotNull('group_id')
            ->distinct()
            ->pluck('group_id')
            ->toArray();
        
        if (empty($groupIds)) {
            return collect();
        }
        
        // Get groups
        $groups = \App\Models\Groupe::whereIn('id_groupe', $groupIds)->get();
        
        return $groups;
    }

    /**
     * Schedule a module for a specific group
     */
    protected function scheduleModuleForGroup(
        PlanningRun $run,
        Module $module,
        \App\Models\Groupe $group,
        $salles,
        array $creneaux
    ): array {
        $items = [];
        
        // Get expected students count for this group in this module
        $key = "{$module->id_module}_{$group->id_groupe}";
        $groupStudents = $this->moduleGroupStudentsCache[$key] ?? [];
        $expectedStudents = count($groupStudents);
        
        if ($expectedStudents === 0) {
            return [];
        }
        
        // Filter suitable rooms - capacity should be at least 2 * group size (half capacity rule)
        $suitableSalles = $this->filterSuitableSallesForGroup($salles, $module, $expectedStudents);
        
        if ($suitableSalles->isEmpty()) {
            return [];
        }
        
        // Try to find a suitable creneau and salle
        foreach ($creneaux as $creneau) {
            // Ensure creneau is an object with proper properties
            if (!is_object($creneau) || !isset($creneau->id_creneau)) {
                continue;
            }
            
            // Check if this group already has an exam on this day (ONE EXAM PER GROUP PER DAY)
            $dateStr = $creneau->date instanceof \Carbon\Carbon 
                ? $creneau->date->format('Y-m-d') 
                : $creneau->date;
            $groupDayKey = "{$group->id_groupe}_{$dateStr}";
            
            if (isset($this->groupDailyExamsCache[$groupDayKey])) {
                continue; // Skip this day, group already has an exam
            }
            
            foreach ($suitableSalles as $salle) {
                // Check if room is already booked in this creneau (from cache)
                $salleKey = "{$creneau->id_creneau}_{$salle->id_salle}";
                if (isset($this->salleUsageCache[$salleKey])) {
                    continue;
                }
                
                // Check student overlap for this group (from cache)
                if ($this->hasStudentOverlapForGroup($module, $group, $creneau)) {
                    continue;
                }
                
                // Assign surveillants (minimum 2 required)
                $surveillants = $this->assignSurveillants($run, $module, $creneau);
                
                // Skip if we couldn't assign at least 2 surveillants
                if (count($surveillants) < 2) {
                    Log::warning('Could not assign 2 surveillants, skipping', [
                        'module_id' => $module->id_module,
                        'group_id' => $group->id_groupe,
                        'creneau_id' => $creneau->id_creneau,
                        'assigned_count' => count($surveillants)
                    ]);
                    continue;
                }
                
                // Create planning item for this group
                $item = PlanningItem::create([
                    'run_id' => $run->id,
                    'module_id' => $module->id_module,
                    'group_id' => $group->id_groupe,
                    'salle_id' => $salle->id_salle,
                    'creneau_id' => $creneau->id_creneau,
                    'expected_students' => $expectedStudents,
                    'surveillants' => $surveillants,
                ]);
                
                $items[] = $item;
                
                // Mark salle as used for this creneau
                $this->salleUsageCache[$salleKey] = true;
                
                // Mark this group as having an exam on this day
                $this->groupDailyExamsCache[$groupDayKey] = true;
                
                Log::info('Scheduled exam for group', [
                    'module' => $module->nom,
                    'group_id' => $group->id_groupe,
                    'date' => $dateStr,
                    'surveillants_count' => count($surveillants)
                ]);
                
                // Only schedule one creneau per group per module
                break 2;
            }
        }
        
        return $items;
    }

    /**
     * Filter suitable salles for a group - uses half capacity rule
     */
    protected function filterSuitableSallesForGroup($salles, Module $module, int $groupSize)
    {
        return $salles->filter(function ($salle) use ($module, $groupSize) {
            // Check capacity: salle must have at least 2 * groupSize (half capacity rule)
            $capacity = $salle->capacite_examen ?? $salle->capacite ?? 0;
            $usableCapacity = floor($capacity / 2); // Half capacity for spacing
            
            if ($usableCapacity < $groupSize) {
                return false;
            }
            
            // Check equipment requirement
            if ($module->necessite_equipement) {
                if (method_exists($salle, 'equipements')) {
                    if ($salle->equipements->isEmpty()) {
                        return false;
                    }
                } else {
                    $hasEquipment = DB::table('salle_equipement')
                        ->where('id_salle', $salle->id_salle)
                        ->exists();
                    
                    if (!$hasEquipment) {
                        return false;
                    }
                }
            }
            
            return true;
        });
    }

    /**
     * Check if scheduling this module for this group in this creneau would cause student overlap
     */
    protected function hasStudentOverlapForGroup(Module $module, \App\Models\Groupe $group, Creneau $creneau): bool
    {
        // Get students in this group for this module from cache
        $key = "{$module->id_module}_{$group->id_groupe}";
        $groupStudents = $this->moduleGroupStudentsCache[$key] ?? [];
        
        if (empty($groupStudents)) {
            return false;
        }
        
        // Check if any scheduled item in the same creneau shares students with this group
        $creneauKey = "{$creneau->id_creneau}";
        foreach ($this->creneauUsageCache as $cacheKey => $value) {
            if (strpos($cacheKey, $creneauKey) === 0) {
                $parts = explode('_', $cacheKey);
                if (count($parts) >= 2) {
                    $otherModuleId = $parts[1] ?? null;
                    if ($otherModuleId && $otherModuleId != $module->id_module) {
                        $otherModuleStudents = $this->moduleStudentsCache[$otherModuleId] ?? [];
                        if (!empty(array_intersect($groupStudents, $otherModuleStudents))) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Assign surveillants to an exam
     */
    protected function assignSurveillants(PlanningRun $run, Module $module, Creneau $creneau): array
    {
        $surveillants = [];
        
        // Normalize date for cache key
        $dateStr = $creneau->date instanceof \Carbon\Carbon 
            ? $creneau->date->format('Y-m-d') 
            : $creneau->date;
        
        // Get department - try multiple sources
        $deptId = $module->formation->id_dept ?? null;
        
        // If no department found, try to get all professors
        static $professorsByDept = [];
        static $allProfessors = null;
        
        if ($deptId && !isset($professorsByDept[$deptId])) {
            $professorsByDept[$deptId] = Professeur::where('id_dept', $deptId)
                ->select('id_prof', 'nom', 'prenom')
                ->get();
        }
        
        // Fallback to all professors if no department or no professors in department
        if (!$deptId || empty($professorsByDept[$deptId])) {
            if ($allProfessors === null) {
                $allProfessors = Professeur::select('id_prof', 'nom', 'prenom')->get();
            }
            $professeurs = $allProfessors;
        } else {
            $professeurs = $professorsByDept[$deptId];
        }
        
        // If still no professors, log warning and return empty
        if ($professeurs->isEmpty()) {
            Log::warning('No professors available for surveillance', [
                'module_id' => $module->id_module,
                'dept_id' => $deptId
            ]);
            return $surveillants;
        }
        
        // Calculate surveillance counts from cache
        $surveillanceCounts = [];
        foreach ($professeurs as $prof) {
            $profKey = "{$prof->id_prof}_{$dateStr}";
            $surveillanceCounts[$prof->id_prof] = $this->professorSurveillanceCache[$profKey] ?? 0;
        }
        
        // Sort by surveillance count (ascending)
        asort($surveillanceCounts);
        
        // Assign 2 surveillants minimum
        $assigned = 0;
        foreach ($surveillanceCounts as $profId => $count) {
            if ($assigned >= 2) {
                break;
            }
            
            // Check max 3 surveillances per day (but allow if we need at least 2)
            if ($count >= 3 && $assigned >= 2) {
                continue;
            }
            
            $surveillants[] = [
                'id_prof' => $profId,
                'role' => $assigned === 0 ? 'responsable' : 'surveillant',
            ];
            $assigned++;
        }
        
        // If we couldn't assign 2, try again without the limit
        if ($assigned < 2 && $professeurs->count() >= 2) {
            $surveillants = [];
            $assigned = 0;
            foreach ($surveillanceCounts as $profId => $count) {
                if ($assigned >= 2) {
                    break;
                }
                
                $surveillants[] = [
                    'id_prof' => $profId,
                    'role' => $assigned === 0 ? 'responsable' : 'surveillant',
                ];
                $assigned++;
            }
        }
        
        Log::info('Assigned surveillants', [
            'module_id' => $module->id_module,
            'date' => $dateStr,
            'assigned_count' => count($surveillants),
            'total_profs' => $professeurs->count()
        ]);
        
        return $surveillants;
    }

    /**
     * Calculate metrics for the run
     */
    protected function calculateMetrics(PlanningRun $run, array $scheduled, array $conflicts, float $startTime): array
    {
        $execTime = (microtime(true) - $startTime) * 1000; // ms
        
        $totalRooms = Salle::count();
        $usedRooms = count(array_unique(array_column($scheduled, 'salle_id')));
        
        $roomsUsedPct = $totalRooms > 0 ? ($usedRooms / $totalRooms) * 100 : 0;
        
        // Average exams per day per student
        $studentExamsPerDay = $this->calculateStudentExamsPerDay($scheduled);
        
        return [
            'conflicts_found' => count($conflicts),
            'rooms_used_pct' => round($roomsUsedPct, 2),
            'avg_student_exams_per_day' => round($studentExamsPerDay, 2),
            'exec_time_ms' => round($execTime, 2),
            'total_scheduled' => count($scheduled),
            'total_conflicts' => count($conflicts),
        ];
    }

    /**
     * Calculate average exams per day per student
     */
    protected function calculateStudentExamsPerDay(array $scheduled): float
    {
        if (empty($scheduled)) {
            return 0;
        }
        
        $studentDays = [];
        
        foreach ($scheduled as $item) {
            $students = $this->moduleStudentsCache[$item->module_id] ?? [];
            
            // Use cached creneau
            $creneau = $this->creneauxCache[$item->creneau_id] ?? null;
            
            if (!$creneau) {
                continue;
            }
            
            $date = $creneau->date;
            
            foreach ($students as $studentId) {
                $key = "{$studentId}_{$date}";
                $studentDays[$key] = ($studentDays[$key] ?? 0) + 1;
            }
        }
        
        if (empty($studentDays)) {
            return 0;
        }
        
        return array_sum($studentDays) / count($studentDays);
    }
}