<?php

namespace App\Services\Simulation;

use Illuminate\Support\Collection;

class WealthCompoundingModel
{
    /**
     * Calculate financial net worth compounding curve (age 0 to 120) and retirement metrics.
     *
     * @param float $currentAge
     * @param float $currentSavings
     * @param float $monthlyIncome
     * @param float $monthlyInvestment
     * @param int $targetRetirementAge
     * @param Collection $activities
     * @return array
     */
    public function calculate(
        float $currentAge,
        float $currentSavings,
        float $monthlyIncome,
        float $monthlyInvestment,
        int $targetRetirementAge,
        Collection $activities
    ): array {
        // Adjust parameters based on logged financial activities
        $annualROI = 0.075; // 7.5% real annual compound return (S&P 500 inflation-adjusted benchmark)
        $effectiveMonthlyInvestment = $monthlyInvestment;
        $effectiveMonthlyIncome = $monthlyIncome;

        foreach ($activities as $act) {
            $type = $act->activity_type ?? '';
            $isActive = $act->is_active ?? true;

            if ($type === 'investment' || $type === 'index_investing') {
                if ($act->intensity_or_amount > 0) {
                    $effectiveMonthlyInvestment = $act->intensity_or_amount;
                }
            } elseif ($type === 'smoking') {
                // Smoking financial drain (e.g. $10/day = $300/mo uninvested)
                if ($isActive) {
                    $cigs = $act->intensity_or_amount > 0 ? $act->intensity_or_amount : 15;
                    $monthlyCost = ($cigs / 20) * 250;
                    $effectiveMonthlyInvestment = max(0, $effectiveMonthlyInvestment - ($monthlyCost * 0.5));
                }
            }
        }

        $curve = [];
        $netWorth = 0.0;
        $financialFreedomAge = null;
        $netWorthAtRetirement = 0.0;
        $annualInvestment = $effectiveMonthlyInvestment * 12;
        $annualExpenses = max(18000, ($effectiveMonthlyIncome - $effectiveMonthlyInvestment) * 12);
        $fiThreshold = $annualExpenses * 25; // 4% Safe Withdrawal Rule

        for ($age = 0; $age <= 120; $age++) {
            if ($age < 21) {
                // Dependent years
                $curve[$age] = 0.0;
                continue;
            }

            if ($age <= $currentAge) {
                // Historical interpolation up to current savings
                $workingYears = max(1, $currentAge - 21);
                $progress = ($age - 21) / $workingYears;
                $nw = $currentSavings * pow($progress, 1.4);
                $curve[$age] = round($nw);
                $netWorth = $nw;
                continue;
            }

            // Future projection
            if ($age <= $targetRetirementAge) {
                // Accumulation phase with compounding + salary growth (2.5% real salary growth)
                $yearsInCareer = $age - $currentAge;
                $careerSalaryMultiplier = 1.0 + min(0.8, $yearsInCareer * 0.025);
                $adjustedAnnualInvestment = $annualInvestment * $careerSalaryMultiplier;

                $netWorth = ($netWorth * (1.0 + $annualROI)) + $adjustedAnnualInvestment;

                if ($financialFreedomAge === null && $netWorth >= $fiThreshold) {
                    $financialFreedomAge = $age;
                }

                if ($age === $targetRetirementAge) {
                    $netWorthAtRetirement = $netWorth;
                }
            } else {
                // Distribution / Retirement phase (4% real safe withdrawal drawdown)
                $annualWithdrawal = $annualExpenses * 0.9;
                $netWorth = ($netWorth * (1.0 + $annualROI * 0.8)) - $annualWithdrawal;
                if ($netWorth < 0) $netWorth = 0.0;
            }

            $curve[$age] = round($netWorth);
        }

        return [
            'current_net_worth' => round($currentSavings),
            'monthly_investment' => round($effectiveMonthlyInvestment, 2),
            'projected_retirement_net_worth' => round($netWorthAtRetirement),
            'projected_financial_freedom_age' => $financialFreedomAge ?? $targetRetirementAge,
            'fi_threshold' => round($fiThreshold),
            'curve' => $curve,
        ];
    }
}
