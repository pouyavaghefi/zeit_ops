<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use RuntimeException;

class MyMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'vagefipouya@yahoo.com';

        // ==========================================
        // 1. FIND USER BY EMAIL
        // ==========================================

        $user = DB::table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            throw new RuntimeException(
                "User with email {$email} was not found."
            );
        }

        DB::transaction(function () use ($user) {

            // ==========================================
            // 2. CREATE OR UPDATE MEMBERSHIP PLAN
            // ==========================================

            DB::table('membership_plans')->updateOrInsert(
                ['slug' => 'developer-internal'],
                [
                    'name' => 'Developer Internal',
                    'description' =>
                        'Internal ZeitOps development and testing membership.',

                    'price' => 0.00,
                    'currency' => 'EUR',
                    'billing_period' => 'yearly',

                    'max_servers' => 10,
                    'max_websites' => 50,
                    'max_deployments_per_month' => 1000,

                    'monitoring_enabled' => true,
                    'automated_backups_enabled' => true,
                    'custom_domains_enabled' => true,

                    'is_active' => true,

                    'updated_at' => now(),
                ]
            );

            $plan = DB::table('membership_plans')
                ->where('slug', 'developer-internal')
                ->first();

            // ==========================================
            // 3. REGISTER USER MEMBERSHIP
            // ==========================================

            $existingMembership = DB::table(
                'membership_plan_registered_users'
            )
                ->where('user_id', $user->id)
                ->where('membership_plan_id', $plan->id)
                ->first();

            $membershipData = [
                'status' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'auto_renew' => false,
                'updated_at' => now(),
            ];

            if ($existingMembership) {

                DB::table(
                    'membership_plan_registered_users'
                )
                    ->where('id', $existingMembership->id)
                    ->update($membershipData);

            } else {

                DB::table(
                    'membership_plan_registered_users'
                )->insert(array_merge($membershipData, [
                    'user_id' => $user->id,
                    'membership_plan_id' => $plan->id,
                    'payment_provider' => null,
                    'provider_subscription_id' => null,
                    'created_at' => now(),
                ]));
            }

        });

        $this->command?->info(
            "Active ZeitOps membership assigned to {$email}."
        );
    }
}
