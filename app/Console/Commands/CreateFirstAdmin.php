<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateFirstAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the first administrator account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Création du premier administrateur ===');
        $this->newLine();
        
        // Vérifier s'il existe déjà des administrateurs
        $adminCount = User::where('user_type_id', 1)->count();
        if ($adminCount > 0) {
            $this->warn('Il existe déjà ' . $adminCount . ' administrateur(s) dans le système.');
            if (!$this->confirm('Voulez-vous continuer et créer un nouvel administrateur ?')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }
        
        // Collecter les informations
        $email = $this->ask('Adresse email');
        while (User::where('email', $email)->exists()) {
            $this->error('Cet email est déjà utilisé.');
            $email = $this->ask('Adresse email');
        }
        
        $username = $this->ask('Nom d\'utilisateur');
        while (User::where('username', $username)->exists()) {
            $this->error('Ce nom d\'utilisateur est déjà pris.');
            $username = $this->ask('Nom d\'utilisateur');
        }
        
        $mobile = $this->ask('Numéro de téléphone', '+225 07 00 00 00 00');
        
        $password = $this->secret('Mot de passe (minimum 8 caractères)');
        while (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères.');
            $password = $this->secret('Mot de passe (minimum 8 caractères)');
        }
        
        $passwordConfirm = $this->secret('Confirmez le mot de passe');
        while ($password !== $passwordConfirm) {
            $this->error('Les mots de passe ne correspondent pas.');
            $passwordConfirm = $this->secret('Confirmez le mot de passe');
        }
        
        $this->newLine();
        $this->info('Récapitulatif :');
        $this->table(
            ['Propriété', 'Valeur'],
            [
                ['Email', $email],
                ['Nom d\'utilisateur', $username],
                ['Téléphone', $mobile],
                ['Type', 'Administrateur'],
                ['Statut', 'Actif'],
            ]
        );
        
        if (!$this->confirm('Confirmer la création de cet administrateur ?')) {
            $this->info('Opération annulée.');
            return 0;
        }
        
        try {
            $user = User::create([
                'email' => $email,
                'username' => $username,
                'mobile_number' => $mobile,
                'password' => Hash::make($password),
                'user_type_id' => 1, // Administrateur
                'status_id' => 2, // Actif
            ]);
            
            $this->newLine();
            $this->info('✅ Administrateur créé avec succès !');
            $this->info('ID: #' . $user->id);
            $this->info('Email: ' . $email);
            $this->info('Vous pouvez maintenant vous connecter avec ces identifiants.');
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Erreur lors de la création : ' . $e->getMessage());
            return 1;
        }
    }
}