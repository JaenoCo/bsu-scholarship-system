<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class MLAnalyticsService
{
    private $pythonScriptPath;
    private $modelsPath;

    public function __construct()
    {
        $this->pythonScriptPath = base_path('ml_analytics.py');
        $this->modelsPath = storage_path('app/models');
    }

    /**
     * Generate ML-powered insights for a report
     */
    public function generateMLInsights($campusId, $startDate, $endDate)
    {
        try {
            // Get data from database
            $data = $this->prepareDataForML($campusId, $startDate, $endDate);
            
            // Run Python ML analysis
            $mlResults = $this->runMLAnalysis($data);
            
            // Process and format results
            return $this->formatMLResults($mlResults);
            
        } catch (\Exception $e) {
            Log::error('ML Analytics Error: ' . $e->getMessage());
            return $this->getFallbackInsights();
        }
    }

    /**
     * Prepare data for ML analysis
     */
    private function prepareDataForML($campusId, $startDate, $endDate)
    {
        // Get applications data
        $applications = \App\Models\Application::with(['user', 'scholarship'])
            ->whereHas('user', function($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function($app) {
                return [
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'scholarship_id' => $app->scholarship_id,
                    'status' => $app->status,
                    'created_at' => $app->created_at->toISOString(),
                ];
            });

        // Get students data
        $students = \App\Models\User::where('campus_id', $campusId)
            ->where('role', 'student')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'campus_id' => $user->campus_id,
                    'campus_type' => $user->campus->type ?? 'unknown',
                ];
            });

        // Get scholarships data
        $scholarships = \App\Models\Scholarship::all()
            ->map(function($scholarship) {
                return [
                    'id' => $scholarship->id,
                    'scholarship_type' => $scholarship->scholarship_type,
                    'grant_type' => $scholarship->grant_type,
                    'grant_amount' => $scholarship->grant_amount,
                ];
            });

        return [
            'applications' => $applications->toArray(),
            'students' => $students->toArray(),
            'scholarships' => $scholarships->toArray(),
        ];
    }

    /**
     * Run Python ML analysis
     */
    private function runMLAnalysis($data)
    {
        // Create temporary data file
        $tempFile = tempnam(sys_get_temp_dir(), 'ml_data_');
        file_put_contents($tempFile, json_encode($data));

        try {
            // Run Python script
            $command = "python3 {$this->pythonScriptPath} --data-file {$tempFile}";
            $result = Process::run($command);

            if ($result->failed()) {
                throw new \Exception('Python ML analysis failed: ' . $result->errorOutput());
            }

            $mlResults = json_decode($result->output(), true);
            
            // Clean up
            unlink($tempFile);
            
            return $mlResults;
            
        } catch (\Exception $e) {
            // Clean up on error
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }

    /**
     * Format ML results for Laravel
     */
    private function formatMLResults($mlResults)
    {
        $insights = [
            'ml_models' => [],
            'predictions' => [],
            'recommendations' => [],
            'risk_factors' => [],
            'opportunities' => [],
            'trends' => [],
            'performance_metrics' => []
        ];

        // Process model results
        if (isset($mlResults['approval_prediction'])) {
            $insights['ml_models']['approval_prediction'] = [
                'model_type' => 'Logistic Regression',
                'accuracy' => $mlResults['approval_prediction']['accuracy'] ?? 0,
                'feature_importance' => $mlResults['approval_prediction']['feature_importance'] ?? []
            ];
        }

        if (isset($mlResults['success_prediction'])) {
            $insights['ml_models']['success_prediction'] = [
                'model_type' => 'Random Forest',
                'accuracy' => $mlResults['success_prediction']['accuracy'] ?? 0,
                'feature_importance' => $mlResults['success_prediction']['feature_importance'] ?? []
            ];
        }

        if (isset($mlResults['approval_rate_regression'])) {
            $insights['ml_models']['approval_rate_regression'] = [
                'model_type' => 'Linear Regression',
                'r2_score' => $mlResults['approval_rate_regression']['r2_score'] ?? 0,
                'coefficients' => $mlResults['approval_rate_regression']['coefficients'] ?? []
            ];
        }

        // Process insights
        if (isset($mlResults['insights'])) {
            $insights['predictions'] = $mlResults['insights']['predictions'] ?? [];
            $insights['recommendations'] = $mlResults['insights']['recommendations'] ?? [];
            $insights['risk_factors'] = $mlResults['insights']['risk_factors'] ?? [];
            $insights['opportunities'] = $mlResults['insights']['opportunities'] ?? [];
        }

        // Process trends
        if (isset($mlResults['trends'])) {
            $insights['trends'] = $mlResults['trends'];
        }

        return $insights;
    }

    /**
     * Get fallback insights when ML fails
     */
    private function getFallbackInsights()
    {
        return [
            'ml_models' => [],
            'predictions' => [],
            'recommendations' => [
                'ML Analysis: Unable to generate AI insights at this time. Using rule-based analysis instead.'
            ],
            'risk_factors' => [],
            'opportunities' => [],
            'trends' => [],
            'performance_metrics' => [],
            'fallback' => true
        ];
    }

    /**
     * Predict approval probability for a new application
     */
    public function predictApprovalProbability($applicationData)
    {
        try {
            // Prepare single application data
            $data = [
                'applications' => [$applicationData],
                'students' => [],
                'scholarships' => []
            ];

            $mlResults = $this->runMLAnalysis($data);
            
            if (isset($mlResults['approval_prediction'])) {
                return [
                    'probability' => $mlResults['approval_prediction']['probability'] ?? 0.5,
                    'confidence' => $mlResults['approval_prediction']['confidence'] ?? 0.5,
                    'factors' => $mlResults['approval_prediction']['factors'] ?? []
                ];
            }

            return ['probability' => 0.5, 'confidence' => 0.0, 'factors' => []];
            
        } catch (\Exception $e) {
            Log::error('ML Prediction Error: ' . $e->getMessage());
            return ['probability' => 0.5, 'confidence' => 0.0, 'factors' => []];
        }
    }

    /**
     * Get model performance metrics
     */
    public function getModelPerformance()
    {
        try {
            $modelsFile = $this->modelsPath . '/model_performance.json';
            
            if (file_exists($modelsFile)) {
                return json_decode(file_get_contents($modelsFile), true);
            }
            
            return [];
            
        } catch (\Exception $e) {
            Log::error('Model Performance Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrain models with new data
     */
    public function retrainModels()
    {
        try {
            // Get all historical data
            $data = $this->prepareDataForML(null, now()->subYear(), now());
            
            // Run retraining
            $mlResults = $this->runMLAnalysis($data);
            
            // Save performance metrics
            $performanceFile = $this->modelsPath . '/model_performance.json';
            file_put_contents($performanceFile, json_encode($mlResults));
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Model Retraining Error: ' . $e->getMessage());
            return false;
        }
    }
}
