# Domain: Progress

## 1. Overview

| Attribute | Value |
|-----------|-------|
| **Domain Name** | Progress |
| **Bounded Context** | Student Mastery Tracking, Analytics, Reporting |
| **Core Responsibility** | Mastery calculation, weakness detection, progress visualization, predictive analytics |
| **Owner** | Progress Domain Lead |
| **Status** | Partly implemented (Sprint 3.3: `topic_masteries`, progress panel) |

> **Sprint 3.3 (PM decision, 16.11.2026):** topic mastery follows design § 06 — accuracy on the last 20 answers, at least 5 answers, levels at 50 / 65 / 80%. `dd-topic-masteries.md` v1.1 is authoritative. The weighted formula, level thresholds, "minimum attempts for level up" table, weak-area detection and scheduled jobs below describe the original design and are **not implemented**.

## 2. Purpose

The Progress domain transforms raw attempt data into actionable insights:
- **Mastery Tracking** - Per-topic, per-objective, per-program proficiency
- **Weakness Detection** - Automated identification of knowledge gaps
- **Learning Analytics** - Trends, velocity, retention, predictions
- **Reporting** - Student dashboards, teacher class reports, parent summaries
- **Intervention** - Actionable recommendations for remediation

## 3. Core Entities

### 3.1 TopicMastery (Primary)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | Student |
| `topic_id` | BIGINT UNSIGNED | NOT NULL, FK → topics.id, CASCADE | Topic |
| `mastery_percentage` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | 0.00 - 100.00 |
| `mastery_level` | TINYINT UNSIGNED | NOT NULL DEFAULT 0 | 0=InsufficientData, 1=Weak, 2=Developing, 3=Proficient, 4=Mastered (dictionary v1.1) |
| `total_attempts` | INT UNSIGNED | NOT NULL DEFAULT 0 | Lifetime |
| `correct_attempts` | INT UNSIGNED | NOT NULL DEFAULT 0 | Lifetime |
| `last_practiced_at` | TIMESTAMP | NULLABLE | |
| `next_review_at` | TIMESTAMP | NULLABLE | SRS scheduling |
| `ease_factor` | DECIMAL(4,2) | NOT NULL DEFAULT 2.50 | SM-2 EF (1.30-2.50) |
| `interval_days` | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | Current SRS interval |
| `repetitions` | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | Successful reviews |
| `is_due` | BOOLEAN | NOT NULL DEFAULT FALSE | Due for review |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(user_id, topic_id)`

### 3.2 LearningObjectiveMastery

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `learning_objective_id` | BIGINT UNSIGNED | NOT NULL, FK → learning_objectives.id, CASCADE | |
| `mastery_percentage` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | |
| `mastery_level` | TINYINT UNSIGNED | NOT NULL DEFAULT 0 | 0-3 |
| `questions_attempted` | INT UNSIGNED | NOT NULL DEFAULT 0 | |
| `questions_correct` | INT UNSIGNED | NOT NULL DEFAULT 0 | |
| `last_attempt_at` | TIMESTAMP | NULLABLE | |
| `is_due` | BOOLEAN | NOT NULL DEFAULT FALSE | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(user_id, learning_objective_id)`

### 3.3 WeakAreaReport

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `topic_id` | BIGINT UNSIGNED | NOT NULL, FK → topics.id, CASCADE | |
| `mastery_percentage` | DECIMAL(5,2) | NOT NULL | At detection time |
| `gap_score` | DECIMAL(5,2) | NOT NULL | 100 - mastery (higher = weaker) |
| `attempts_analyzed` | INT UNSIGNED | NOT NULL | Sample size |
| `error_patterns` | JSON | NULLABLE | Misconception tags |
| `recommended_actions` | JSON | NULLABLE | Practice, review, video |
| `status` | TINYINT UNSIGNED | NOT NULL DEFAULT 1 | 1=Active, 2=Improving, 3=Resolved, 4=Dismissed |
| `detected_at` | TIMESTAMP | NOT NULL | |
| `resolved_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

### 3.4 MasterySnapshot (Daily Rollup)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `topic_id` | BIGINT UNSIGNED | NOT NULL, FK → topics.id, CASCADE | |
| `snapshot_date` | DATE | NOT NULL | |
| `mastery_percentage` | DECIMAL(5,2) | NOT NULL | End-of-day |
| `total_attempts` | INT UNSIGNED | NOT NULL DEFAULT 0 | Day's attempts |
| `correct_attempts` | INT UNSIGNED | NOT NULL DEFAULT 0 | Day's correct |
| `study_time_seconds` | INT UNSIGNED | NOT NULL DEFAULT 0 | |
| `sessions_count` | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | |
| `created_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(user_id, topic_id, snapshot_date)`

### 3.5 StudentProgress (Aggregated View)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `program_id` | BIGINT UNSIGNED | NOT NULL, FK → programs.id, CASCADE | |
| `overall_mastery` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | Weighted average |
| `topics_completed` | INT UNSIGNED | NOT NULL DEFAULT 0 | Mastery ≥ 80% |
| `topics_in_progress` | INT UNSIGNED | NOT NULL DEFAULT 0 | Mastery 30-79% |
| `topics_not_started` | INT UNSIGNED | NOT NULL DEFAULT 0 | Mastery = 0 |
| `total_study_time_seconds` | BIGINT UNSIGNED | NOT NULL DEFAULT 0 | |
| `current_streak_days` | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | Consecutive days |
| `longest_streak_days` | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 | |
| `last_activity_at` | TIMESTAMP | NULLABLE | |
| `last_snapshot_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

### 3.6 ClassReport (Teacher View)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `class_id` | BIGINT UNSIGNED | NOT NULL, FK → classes.id, CASCADE | |
| `report_date` | DATE | NOT NULL | |
| `students_enrolled` | INT UNSIGNED | NOT NULL | |
| `students_active` | INT UNSIGNED | NOT NULL | |
| `avg_mastery` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | Class average |
| `topics_mastered` | INT UNSIGNED | NOT NULL DEFAULT 0 | |
| `topics_struggling` | INT UNSIGNED | NOT NULL DEFAULT 0 | |
| `avg_study_time_minutes` | DECIMAL(8,2) | NOT NULL DEFAULT 0.00 | |
| `completion_rate` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | % topics ≥ 80% |
| `at_risk_students` | JSON | NULLABLE | User IDs below threshold |
| `top_weak_topics` | JSON | NULLABLE | Topic IDs with lowest avg |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

---

## 4. Relationships

```mermaid
erDiagram
    TOPIC_MASTERY }|--|| USER : belongs_to
    TOPIC_MASTERY }|--|| TOPIC : belongs_to
    
    OBJECTIVE_MASTERY }|--|| USER : belongs_to
    OBJECTIVE_MASTERY }|--|| LEARNING_OBJECTIVE : belongs_to
    
    WEAK_AREA_REPORT }|--|| USER : belongs_to
    WEAK_AREA_REPORT }|--|| TOPIC : belongs_to
    
    MASTERY_SNAPSHOT }|--|| USER : belongs_to
    MASTERY_SNAPSHOT }|--|| TOPIC : belongs_to
    
    STUDENT_PROGRESS }|--|| USER : belongs_to
    STUDENT_PROGRESS }|--|| PROGRAM : belongs_to
    
    CLASS_REPORT }|--|| CLASS : belongs_to
```

---

## 4. Mastery Calculation

### Weighted Mastery Formula

```php
function calculateMastery(Collection $attempts): float
{
    if ($attempts->isEmpty()) return 0.0;
    
    // Recency weight: exponential decay (half-life = 30 days)
    $now = now();
    $weightedCorrect = 0.0;
    $totalWeight = 0.0;
    
    foreach ($attempts as $attempt) {
        $daysAgo = $attempt->answered_at->diffInDays($now);
        $recencyWeight = exp(-$daysAgo / 30); // e^(-t/30)
        
        // Difficulty weight: harder questions count more
        $difficultyWeight = $attempt->question->difficulty / 3.0; // 0.33 to 1.67
        
        // Quality weight from SRS (0-5)
        $qualityWeight = $attempt->quality ?? 3;
        
        $weight = $recencyWeight * $difficultyWeight * ($qualityWeight / 5.0);
        
        $totalWeight += $weight;
        if ($attempt->is_correct) {
            $weightedCorrect += $weight;
        }
    }
    
    if ($totalWeight === 0) return 0.0;
    
    return round(($weightedCorrect / $totalWeight) * 100, 1);
}
```

### Mastery Level Thresholds

| Level | Percentage | Label | Color | Action |
|-------|------------|-------|-------|--------|
| 0 | 0-29% | Novice | Red | Intensive practice |
| 1 | 30-59% | Learning | Orange | Guided practice |
| 2 | 60-79% | Proficient | Yellow | Mixed review |
| 3 | 80-100% | Mastered | Green | Spaced review only |

### Minimum Attempts for Level Up

| Transition | Min Attempts | Min Accuracy | Consistency |
|------------|--------------|--------------|-------------|
| 0 → 1 | 10 | 40% | 3 consecutive correct |
| 1 → 2 | 25 | 65% | 5 of last 7 correct |
| 2 → 3 | 50 | 85% | 10 of last 12 correct |

---

## 5. Weak Area Detection

### Detection Algorithm

```php
function detectWeakAreas(int $userId): array
{
    $candidates = TopicMastery::query()
        ->where('user_id', $userId)
        ->where('mastery_percentage', '<', 60)
        ->where('total_attempts', '>=', 10) // Minimum sample
        ->where('is_due', true)
        ->get();
    
    $reports = [];
    
    foreach ($candidates as $mastery) {
        // Analyze error patterns
        $errors = QuestionAttempt::query()
            ->where('user_id', $mastery->user_id)
            ->whereHas('question', fn($q) => $q->where('topic_id', $mastery->topic_id))
            ->where('is_correct', false)
            ->with('question')
            ->limit(50)
            ->get();
        
        $patterns = analyzeErrorPatterns($errors);
        
        $report = WeakAreaReport::updateOrCreate(
            ['user_id' => $mastery->user_id, 'topic_id' => $mastery->topic_id, 'status' => 1],
            [
                'mastery_percentage' => $mastery->mastery_percentage,
                'gap_score' => 100 - $mastery->mastery_percentage,
                'attempts_analyzed' => $mastery->total_attempts,
                'error_patterns' => $patterns,
                'recommended_actions' => generateRecommendations($mastery, $patterns),
            ]
        );
        
        $reports[] = $report;
    }
    
    return $reports;
}

function analyzeErrorPatterns(Collection $errors): array
{
    $patterns = [];
    
    // Misconception tags from questions
    $tags = $errors->flatMap(fn($e) => $e->question->tags ?? []);
    $tagCounts = $tags->countBy();
    $patterns['misconceptions'] = $tagCounts->sortDesc()->take(5);
    
    // Bloom level weakness
    $bloomCounts = $errors->groupBy('question.bloom_level')->map->count();
    $patterns['bloom_weakness'] = $bloomCounts->sortDesc();
    
    // Difficulty distribution
    $diffCounts = $errors->groupBy('question.difficulty')->map->count();
    $patterns['difficulty_profile'] = $diffCounts->sortDesc();
    
    // Common wrong options (for MCQ)
    $wrongOptions = $errors
        ->whereNotNull('selected_option_id')
        ->pluck('selected_option_id')
        ->countBy();
    $patterns['common_wrong_answers'] = $wrongOptions->sortDesc()->take(3);
    
    return $patterns;
}
```

### Recommended Actions Generator

```php
function generateRecommendations(TopicMastery $mastery, array $patterns): array
{
    $actions = [];
    
    // Primary action based on mastery level
    match (true) {
        $mastery->mastery_percentage < 30 => $actions[] = [
            'type' => 'intensive_practice',
            'priority' => 'critical',
            'title' => 'Temel Kavramları Tekrar Edin',
            'description' => 'Bu konunun temel kavramlarını yeniden çalışın.',
            'estimated_time_minutes' => 30,
            'action_url' => route('practice.topic', ['topic' => $mastery->topic_id, 'mode' => 'intensive']),
        ],
        
        $mastery->mastery_percentage < 60 => $actions[] = [
            'type' => 'guided_review',
            'priority' => 'high',
            'title' => 'Kılavuzlu Tekrar',
            'description' => 'Zayıf alanlarınıza odaklanan kılavuzlu tekrar yapın.',
            'estimated_time_minutes' => 20,
            'action_url' => route('practice.topic', ['topic' => $mastery->topic_id, 'mode' => 'guided']),
        ],
        
        default => $actions[] = [
            'type' => 'spaced_review',
            'priority' => 'medium',
            'title' => 'Aralıklı Tekrar',
            'description' => 'Bu konuyu aralıklı tekrar sisteminize ekleyin.',
            'estimated_time_minutes' => 10,
            'action_url' => route('practice.due-queue'),
        ],
    };
    
    // Pattern-specific actions
    if (isset($patterns['bloom_weakness']['Apply']) && $patterns['bloom_weakness']['Apply'] > 3) {
        $actions[] = [
            'type' => 'application_practice',
            'priority' => 'high',
            'title' => 'Uygulama Becerilerini Geliştirin',
            'description' => 'Bilgiyi yeni durumlara uygulama pratik yapın.',
            'action_url' => route('practice.mode', ['mode' => 'application']),
        ];
    }
    
    if (isset($patterns['difficulty_profile'][4]) && $patterns['difficulty_profile'][4] > 2) {
        $actions[] = [
            'type' => 'difficulty_breakdown',
            'priority' => 'medium',
            'title' => 'Zor Soruları Parçalayın',
            'description' => 'Zor soruları adım adım çözme stratejisi öğrenin.',
            'action_url' => route('content.strategy', ['type' => 'difficulty-breakdown']),
        ];
    }
    
    return $actions;
}
```

---

## 6. Analytics & Reporting

### Student Dashboard Queries

```php
// Overall Progress
StudentProgress::where('user_id', $userId)->first();

// Topic Mastery Breakdown
TopicMastery::where('user_id', $userId)
    ->with('topic.unit.grade')
    ->orderByDesc('mastery_percentage')
    ->get()
    ->groupBy('grade_id')
    ->map(fn($group) => $group->groupBy('unit_id'));

// Weak Areas
WeakAreaReport::where('user_id', $userId)
    ->where('status', 1) // Active
    ->with('topic.unit.grade')
    ->orderByDesc('gap_score')
    ->get();

// Study Streak
$streak = StudentProgress::where('user_id', $userId)
    ->value('current_streak_days');

// Weekly Trend
MasterySnapshot::where('user_id', $userId)
    ->where('snapshot_date', '>=', now()->subWeeks(12))
    ->orderBy('snapshot_date')
    ->get()
    ->groupBy('topic_id')
    ->map(fn($snapshots) => $snapshots->pluck('mastery_percentage', 'snapshot_date'));
```

### Teacher Class Analytics

```php
// Class Overview
ClassReport::where('class_id', $classId)
    ->latest('report_date')
    ->first();

// Student Comparison
User::whereHas('classStudents', fn($q) => $q->where('class_id', $classId))
    ->with(['topicMasteries' => fn($q) => $q->where('mastery_level', '>=', 2)])
    ->get()
    ->map(fn($student) => [
        'name' => $student->name,
        'mastery_avg' => $student->topicMasteries->avg('mastery_percentage'),
        'topics_mastered' => $student->topicMasteries->where('mastery_level', 3)->count(),
        'weak_count' => $student->weakAreas()->where('status', 1)->count(),
        'study_time' => $student->studentProgress->total_study_time_seconds ?? 0,
    ])
    ->sortByDesc('mastery_avg');

// Topic Heatmap (Class)
QuestionAttempt::whereHas('user.classStudents', fn($q) => $q->where('class_id', $classId))
    ->where('answered_at', '>=', now()->subDays(30))
    ->with('question.topic')
    ->get()
    ->groupBy('question.topic_id')
    ->map(fn($attempts) => [
        'topic' => $attempts->first()->question->topic,
        'total_attempts' => $attempts->count(),
        'accuracy' => round($attempts->where('is_correct', true)->count() / $attempts->count() * 100, 1),
        'avg_time' => $attempts->avg('time_spent_seconds'),
    ])
    ->sortByDesc('total_attempts');
```

---

## 6. Scheduled Jobs

| Job | Schedule | Purpose |
|-----|----------|---------|
| `UpdateMasteryJob` | After every attempt | Incremental mastery update |
| `RecalculateMasteryJob` | Daily 02:00 | Full recalculation with decay |
| `DetectWeakAreasJob` | Daily 03:00 | Scan for new weak areas |
| `GenerateMasterySnapshotsJob` | Daily 01:00 | Daily rollup |
| `GenerateClassReportsJob` | Daily 04:00 | Teacher reports |
| `CleanOldSnapshotsJob` | Weekly Sunday | Retain 2 years |
| `UpdateStreaksJob` | Daily 00:30 | Streak calculation |

### Mastery Recalculation (Daily)

```php
class RecalculateMasteryJob implements ShouldQueue
{
    public function handle()
    {
        User::chunkById(100, function ($users) {
            foreach ($users as $user) {
                // Recalculate with full decay
                $topics = TopicMastery::where('user_id', $user->id)->get();
                
                foreach ($topics as $mastery) {
                    $attempts = QuestionAttempt::where('user_id', $user->id)
                        ->whereHas('question', fn($q) => $q->where('topic_id', $mastery->topic_id))
                        ->get();
                    
                    $newPercentage = calculateMastery($attempts);
                    $mastery->update([
                        'mastery_percentage' => $newPercentage,
                        'mastery_level' => calculateLevel($newPercentage),
                        'is_due' => $newPercentage < 60,
                    ]);
                }
                
                // Update StudentProgress aggregate
                $this->updateStudentProgress($user);
            }
        });
    }
}
```

---

## 7. API Contracts

```
GET    /api/v1/progress/mastery                     # All topic masteries
GET    /api/v1/progress/mastery/{topic}             # Topic detail
GET    /api/v1/progress/weak-areas                  # Active weak areas
GET    /api/v1/progress/weak-areas/{id}/resolve     # Mark resolving
GET    /api/v1/progress/snapshots                   # History (filter: topic, range)
GET    /api/v1/progress/stats                       # Overview stats
GET    /api/v1/progress/streak                      # Current streak
GET    /api/v1/progress/dashboard                   # Full dashboard data

# Teacher
GET    /api/v1/teacher/classes/{class}/progress     # Class report
GET    /api/v1/teacher/classes/{class}/students     # Student list with mastery
GET    /api/v1/teacher/classes/{class}/heatmap      # Topic heatmap
GET    /api/v1/teacher/classes/{class}/reports      # Generated reports
GET    /api/v1/teacher/reports/{id}                 # Report detail
```

---

## 7. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| **Mastery Inflation** | High | Decay weighting; minimum attempts for level up; periodic recalibration |
| **Snapshot Storage** | Medium | Partition by month; retain 2 years hot, 5 years cold |
| **Weak Area Noise** | Medium | Minimum 10 attempts threshold; confidence intervals |
| **Streak Gaming** | Low | Require meaningful activity (≥5 min, ≥3 questions) |
| **Report Generation Load** | Medium | Async generation; cache 1 hour; incremental updates |
| **Data Consistency** | High | Event sourcing for mastery changes; audit trail |

---

*End of Progress Domain Design*