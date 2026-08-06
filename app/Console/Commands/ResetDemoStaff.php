<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetDemoStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:reset-demo {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete every non-superadmin staff member and recreate exactly 2 known-good accounts per role (Admin, Chef, Waiter, Cashier).';

    protected array $roles = ['Manager', 'Chef', 'Waiter', 'Cashier'];

    public function handle(): int
    {
        $superadmins = User::where('is_superadmin', true)->get();

        if ($superadmins->isEmpty()) {
            $this->error('Hech qanday Bosh Administrator (is_superadmin=true) topilmadi. Xavfsizlik uchun to\'xtatildi - avval kamida bitta superadmin borligiga ishonch hosil qiling.');
            return self::FAILURE;
        }

        $toDelete = User::where('is_superadmin', false)->get();

        $this->info('Saqlanadigan Bosh Administrator(lar): ' . $superadmins->pluck('login')->implode(', '));
        $this->info("O'chiriladigan xodimlar soni: {$toDelete->count()}");

        if (!$this->option('force') && !$this->confirm('Davom etishni tasdiqlaysizmi? Bu qaytarib bo\'lmaydigan amal!')) {
            $this->warn('Bekor qilindi.');
            return self::SUCCESS;
        }

        $keepId = $superadmins->first()->id;

        DB::transaction(function () use ($toDelete, $keepId) {
            foreach ($toDelete as $user) {
                // Reassign any inventory transactions to the surviving superadmin so
                // the FK constraint (inventory_transactions.user_id -> users.id,
                // no cascade) doesn't block deletion, without losing audit history.
                DB::table('inventory_transactions')->where('user_id', $user->id)->update(['user_id' => $keepId]);

                // Clean up polymorphic pivot rows that don't have a DB-level FK
                // cascade (Spatie roles/permissions, Sanctum tokens) so they
                // don't linger as orphaned rows after the user is gone.
                DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', User::class)->delete();
                DB::table('model_has_permissions')->where('model_id', $user->id)->where('model_type', User::class)->delete();
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->where('tokenable_type', User::class)->delete();

                $user->delete();
            }
        });

        $this->info("O'chirildi: {$toDelete->count()} ta xodim.");

        $branch = \App\Models\Branch::first();
        $created = [];
        foreach ($this->roles as $role) {
            for ($i = 1; $i <= 2; $i++) {
                $login = strtolower($role) . $i;
                $password = strtolower($role) . '12345';
                $phone = '+998900000' . str_pad((array_search($role, $this->roles) * 2 + $i), 3, '0', STR_PAD_LEFT);

                $user = User::create([
                    'name' => $role . ' Test ' . $i,
                    'login' => $login,
                    'password' => Hash::make($password),
                    'phone' => $phone,
                    'status' => 'active',
                    'is_superadmin' => false,
                    'face_registered' => false,
                    'branch_id' => $branch->id,
                ]);
                $user->assignRole($role);

                $created[] = [$role, $login, $password, $phone];
            }
        }

        $this->newLine();
        $this->info('Yangi xodimlar yaratildi:');
        $this->table(['Rol', 'Login', 'Parol', 'Telefon'], $created);

        return self::SUCCESS;
    }
}
