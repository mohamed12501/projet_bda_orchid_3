<?php

namespace App\Services;

use App\Models\PlanningRun;
use App\Models\PlanningItem;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;

class ConflictService
{
    /**
     * Detect room conflicts (same run + same salle + same creneau count > 1)
     */
    public function detectRoomConflicts(?PlanningRun $run = null): array
    {
        $query = PlanningItem::select('salle_id', 'creneau_id', 'run_id', DB::raw('COUNT(*) as count'))
            ->groupBy('salle_id', 'creneau_id', 'run_id')
            ->having('count', '>', 1);
        
        if ($run) {
            $query->where('run_id', $run->id);
        }
        
        $conflicts = $query->get();
        
        $results = [];
        foreach ($conflicts as $conflict) {
            $items = PlanningItem::where('run_id', $conflict->run_id)
                ->where('salle_id', $conflict->salle_id)
                ->where('creneau_id', $conflict->creneau_id)
                ->with(['salle', 'creneau', 'module', 'run'])
                ->get();
            
            $results[] = [
                'type' => 'room_conflict',
                'run_id' => $conflict->run_id,
                'salle_id' => $conflict->salle_id,
                'creneau_id' => $conflict->creneau_id,
                'count' => $conflict->count,
                'items' => $items,
            ];
        }
        
        return $results;
    }

    /**
     * Detect student overlap conflicts (same run + same creneau where modules share students)
     */
    public function detectStudentOverlaps(?PlanningRun $run = null): array
    {
        $query = PlanningItem::query();
        
        if ($run) {
            $query->where('run_id', $run->id);
        }
        
        $items = $query->with(['module', 'creneau'])->get();
        
        $conflicts = [];
        $checked = [];
        
        foreach ($items as $item1) {
            foreach ($items as $item2) {
                if ($item1->id === $item2->id) {
                    continue;
                }
                
                // Same creneau
                if ($item1->creneau_id !== $item2->creneau_id) {
                    continue;
                }
                
                // Same run
                if ($item1->run_id !== $item2->run_id) {
                    continue;
                }
                
                $key = min($item1->id, $item2->id) . '_' . max($item1->id, $item2->id);
                if (in_array($key, $checked)) {
                    continue;
                }
                $checked[] = $key;
                
                // Check if modules share students
                $students1 = Inscription::where('id_module', $item1->module_id)
                    ->pluck('id_etudiant')
                    ->toArray();
                
                $students2 = Inscription::where('id_module', $item2->module_id)
                    ->pluck('id_etudiant')
                    ->toArray();
                
                $sharedStudents = array_intersect($students1, $students2);
                
                if (!empty($sharedStudents)) {
                    $conflicts[] = [
                        'type' => 'student_overlap',
                        'run_id' => $item1->run_id,
                        'creneau_id' => $item1->creneau_id,
                        'item1' => $item1,
                        'item2' => $item2,
                        'shared_students' => array_values($sharedStudents),
                        'shared_count' => count($sharedStudents),
                    ];
                }
            }
        }
        
        return $conflicts;
    }

    /**
     * Detect students with more than 1 exam per day (soft constraint violation)
     */
    public function detectStudentMaxExamsPerDay(?PlanningRun $run = null): array
    {
        $query = PlanningItem::query();
        
        if ($run) {
            $query->where('run_id', $run->id);
        }
        
        $items = $query->with(['module.inscriptions', 'creneau'])->get();
        
        $studentDays = [];
        
        foreach ($items as $item) {
            $students = Inscription::where('id_module', $item->module_id)
                ->pluck('id_etudiant')
                ->toArray();
            
            $date = $item->creneau->date;
            
            foreach ($students as $studentId) {
                $key = "{$studentId}_{$date}";
                if (!isset($studentDays[$key])) {
                    $studentDays[$key] = [
                        'student_id' => $studentId,
                        'date' => $date,
                        'count' => 0,
                        'items' => [],
                    ];
                }
                $studentDays[$key]['count']++;
                $studentDays[$key]['items'][] = $item;
            }
        }
        
        $violations = [];
        foreach ($studentDays as $key => $data) {
            if ($data['count'] > 1) {
                $violations[] = [
                    'type' => 'student_max_exams_per_day',
                    'student_id' => $data['student_id'],
                    'date' => $data['date'],
                    'count' => $data['count'],
                    'items' => $data['items'],
                ];
            }
        }
        
        return $violations;
    }

    /**
     * Detect professors with more than 3 surveillances per day (soft constraint violation)
     */
    public function detectProfessorMaxSurveillancesPerDay(?PlanningRun $run = null): array
    {
        $query = PlanningItem::query();
        
        if ($run) {
            $query->where('run_id', $run->id);
        }
        
        $items = $query->with('creneau')->get();
        
        $profDays = [];
        
        foreach ($items as $item) {
            $surveillants = $item->surveillants ?? [];
            $date = $item->creneau->date;
            
            foreach ($surveillants as $surv) {
                $profId = $surv['id_prof'] ?? null;
                if (!$profId) {
                    continue;
                }
                
                $key = "{$profId}_{$date}";
                if (!isset($profDays[$key])) {
                    $profDays[$key] = [
                        'prof_id' => $profId,
                        'date' => $date,
                        'count' => 0,
                        'items' => [],
                    ];
                }
                $profDays[$key]['count']++;
                $profDays[$key]['items'][] = $item;
            }
        }
        
        $violations = [];
        foreach ($profDays as $key => $data) {
            if ($data['count'] > 3) {
                $violations[] = [
                    'type' => 'professor_max_surveillances_per_day',
                    'prof_id' => $data['prof_id'],
                    'date' => $data['date'],
                    'count' => $data['count'],
                    'items' => $data['items'],
                ];
            }
        }
        
        return $violations;
    }

    /**
     * Get all conflicts for a run or globally
     */
    public function getAllConflicts(?PlanningRun $run = null): array
    {
        return [
            'room_conflicts' => $this->detectRoomConflicts($run),
            'student_overlaps' => $this->detectStudentOverlaps($run),
            'student_max_exams_per_day' => $this->detectStudentMaxExamsPerDay($run),
            'professor_max_surveillances_per_day' => $this->detectProfessorMaxSurveillancesPerDay($run),
        ];
    }

    /**
     * Get conflict summary/KPIs
     */
    public function getConflictSummary(?PlanningRun $run = null): array
    {
        $allConflicts = $this->getAllConflicts($run);
        
        return [
            'room_conflicts_count' => count($allConflicts['room_conflicts']),
            'student_overlaps_count' => count($allConflicts['student_overlaps']),
            'student_max_exams_violations' => count($allConflicts['student_max_exams_per_day']),
            'professor_max_surveillances_violations' => count($allConflicts['professor_max_surveillances_per_day']),
            'total_conflicts' => array_sum([
                count($allConflicts['room_conflicts']),
                count($allConflicts['student_overlaps']),
                count($allConflicts['student_max_exams_per_day']),
                count($allConflicts['professor_max_surveillances_per_day']),
            ]),
        ];
    }
}
