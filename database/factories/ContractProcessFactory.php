<?php

namespace Database\Factories;

use App\Enums\ProcessStatus;
use App\Enums\ProcessType;
use App\Models\ContractProcess;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractProcessFactory extends Factory
{
    protected $model = ContractProcess::class;

    public function definition(): array
    {
        return [
            'process_type' => ProcessType::CONTRATACION_DIRECTA_PERSONA_NATURAL,
            'status' => ProcessStatus::NEED_DEFINED,
            'current_step' => 0,
            'object' => $this->faker->sentence(10),
            'estimated_value' => $this->faker->randomFloat(2, 1000000, 50000000),
            'term_days' => $this->faker->numberBetween(30, 180),
            'expected_start_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'contractor_name' => $this->faker->name(),
            'contractor_document_type' => $this->faker->randomElement(['CC', 'CE']),
            'contractor_document_number' => $this->faker->numerify('##########'),
            'contractor_email' => $this->faker->safeEmail(),
            'contractor_phone' => $this->faker->phoneNumber(),
            'created_by' => User::factory(),
        ];
    }

    public function withRelations(): static
    {
        return $this->state(fn (array $attributes) => [
            'supervisor_id' => User::factory(),
            'ordering_officer_id' => User::factory(),
            'unit_head_id' => User::factory(),
            'unit_lawyer_id' => User::factory(),
            'link_lawyer_id' => User::factory(),
            'secretaria_id' => Secretaria::factory(),
            'unidad_id' => Unidad::factory(),
        ]);
    }

    public function inStep(int $step, ProcessStatus $status = null): static
    {
        $status = $status ?? match($step) {
            0 => ProcessStatus::NEED_DEFINED,
            1 => ProcessStatus::INITIAL_DOCS_PENDING,
            2 => ProcessStatus::CONTRACTOR_VALIDATION,
            3 => ProcessStatus::CONTRACT_DOCS_DRAFTED,
            4 => ProcessStatus::PRECONTRACT_FILE_READY,
            5 => ProcessStatus::LEGAL_REVIEW_PENDING,
            6 => ProcessStatus::SECOP_PUBLISHED_AND_SIGNED,
            7 => ProcessStatus::RPC_REQUESTED,
            8 => ProcessStatus::CONTRACT_NUMBER_ASSIGNED,
            9 => ProcessStatus::STARTED,
            default => ProcessStatus::NEED_DEFINED,
        };

        return $this->state(fn (array $attributes) => [
            'current_step' => $step,
            'status' => $status,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProcessStatus::STARTED,
            'current_step' => 9,
            'contract_number' => 'CONT-' . $this->faker->numerify('###-####'),
            'rpc_number' => 'RPC-' . $this->faker->numerify('###-####'),
            'secop_id' => $this->faker->numerify('SECOP-########'),
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProcessStatus::CANCELLED,
            'cancelled_at' => now(),
            'observations' => 'Proceso cancelado por ' . $this->faker->sentence(5),
        ]);
    }
}
