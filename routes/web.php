<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Models\Setting;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PasswordManagementController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| ✅ ROUTES PUBLIQUES (Authentification) - CORRIGÉES
|--------------------------------------------------------------------------
*/

// ✅ CORRECTION #1 : Noms de routes différents pour éviter les conflits
Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Inscription (RÉSERVÉE AUX ADMINS UNIQUEMENT)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ROUTES MOT DE PASSE OUBLIÉ
|--------------------------------------------------------------------------
*/

// Formulaire "mot de passe oublié"
Route::get('/password/forgot', [PasswordManagementController::class, 'showForgotForm'])
    ->name('password.forgot');

// Traitement demande de récupération
Route::post('/password/email', [PasswordManagementController::class, 'sendResetEmail'])
    ->name('password.email');

// Affichage formulaire de réinitialisation avec token
Route::get('/password/reset/{token}/{user}', [PasswordManagementController::class, 'showResetForm'])
    ->name('password.reset');

// Traitement réinitialisation avec token
Route::post('/password/update', [PasswordManagementController::class, 'resetPassword'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| ROUTES CHANGEMENT MOT DE PASSE OBLIGATOIRE
|--------------------------------------------------------------------------
*/

// Affichage formulaire changement obligatoire (utilisateur temporairement connecté)
Route::get('/password/mandatory-change', [LoginController::class, 'showMandatoryPasswordChange'])
    ->name('password.mandatory-change')
    ->middleware('web');

// Traitement changement obligatoire
Route::post('/password/mandatory-update', [LoginController::class, 'updateMandatoryPassword'])
    ->name('password.mandatory-update')
    ->middleware('web');

/*
|--------------------------------------------------------------------------
| ✅ ROUTES PROTÉGÉES (Utilisateurs connectés) - CORRIGÉES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    
    // ✅ CORRECTION #2 : Dashboard principal avec appels directs au lieu de redirections
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | ROUTES COMMUNES (Admin + Users)
    |--------------------------------------------------------------------------
    */
    
    // Changement de mot de passe (utilisateur connecté)
    Route::post('/password/change', [PasswordManagementController::class, 'changePassword'])
        ->name('password.change');
    
    /*
    |--------------------------------------------------------------------------
    | 🆕 ROUTES UTILISATEURS AVEC DIFFÉRENCIATION AUTOMATIQUE - CORRIGÉES
    |--------------------------------------------------------------------------
    */
    
    // Dashboard utilisateurs - LOGIQUE AUTOMATIQUE :
    // → Poste Ecran : Interface plein écran sans sidebar + grille services
    // → Accueil/Conseiller : Interface avec sidebar + guide métier
    Route::get('/layouts/app-users', [DashboardController::class, 'userDashboard'])
        ->name('layouts.app-users');

    // 🆕 API pour rafraîchir les services (interface Poste Ecran)
    Route::get('/api/user/services/refresh', [DashboardController::class, 'refreshUserServices'])
        ->name('api.user.services.refresh');

    // 🆕 API pour obtenir les informations utilisateur (AJAX)
    Route::get('/api/user/info', [DashboardController::class, 'getUserInfo'])
        ->name('api.user.info');

    // 🆕 API pour les guides métier par type
    Route::get('/api/user/type-guide/{type?}', [DashboardController::class, 'getTypeGuide'])
        ->name('api.user.type-guide');

    /*
    |--------------------------------------------------------------------------
    | 🆕 INTERFACE CONSEILLER DÉDIÉE - NOUVELLES ROUTES CORRIGÉES
    |--------------------------------------------------------------------------
    */

    // Dashboard principal conseiller - Interface dédiée FIFO
    Route::get('/layouts/app-conseiller', [DashboardController::class, 'conseillerDashboard'])
        ->name('layouts.app-conseiller')
        ->middleware('conseiller');

    // 👨‍💼 ROUTES CONSEILLER UNIQUEMENT
    Route::middleware('conseiller')->group(function () {
        
        // 🎫 GESTION FILE D'ATTENTE FIFO
        Route::prefix('conseiller')->group(function () {
            
            // Récupérer les tickets en attente (FIFO chronologique)
            Route::get('/tickets', [DashboardController::class, 'getConseillerTickets'])
                ->name('conseiller.tickets');
            
            // Appeler le prochain ticket (FIFO)
            Route::post('/call-ticket', [DashboardController::class, 'callNextTicket'])
                ->name('conseiller.call-ticket');
            
            // Terminer le ticket en cours
            Route::post('/complete-ticket', [DashboardController::class, 'completeCurrentTicket'])
                ->name('conseiller.complete-ticket');
            
            // Mes statistiques personnelles
            Route::get('/my-stats', [DashboardController::class, 'getConseillerStats'])
                ->name('conseiller.my-stats');
            
            // Mon historique des tickets traités
            Route::get('/history', [DashboardController::class, 'getConseillerHistory'])
                ->name('conseiller.history');
            
            // Mettre en pause / reprendre
            Route::post('/toggle-pause', [DashboardController::class, 'toggleConseillerPause'])
                ->name('conseiller.toggle-pause');
            
            // Détails d'un ticket spécifique
            Route::get('/ticket/{id}/details', [DashboardController::class, 'getTicketDetails'])
                ->name('conseiller.ticket-details');
            
            // Transférer un ticket vers un autre conseiller (futur)
            Route::post('/transfer-ticket', [DashboardController::class, 'transferTicket'])
                ->name('conseiller.transfer-ticket');
            
            // Export des données conseiller
            Route::get('/export', [DashboardController::class, 'exportConseillerData'])
                ->name('conseiller.export');
        });
        
        // 🔄 API TEMPS RÉEL CONSEILLER
        Route::prefix('api/conseiller')->group(function () {
            
            // Rafraîchir la file en temps réel
            Route::get('/refresh-queue', [DashboardController::class, 'refreshConseillerQueue'])
                ->name('api.conseiller.refresh-queue');
            
            // Obtenir le prochain ticket sans le prendre
            Route::get('/next-ticket', [DashboardController::class, 'getNextTicketPreview'])
                ->name('api.conseiller.next-ticket');
            
            // Vérifier si j'ai un ticket en cours
            Route::get('/current-ticket', [DashboardController::class, 'getCurrentTicketStatus'])
                ->name('api.conseiller.current-ticket');
            
            // Notifications temps réel
            Route::get('/notifications', [DashboardController::class, 'getConseillerNotifications'])
                ->name('api.conseiller.notifications');
            
            // Statistiques temps réel
            Route::get('/live-stats', [DashboardController::class, 'getLiveConseillerStats'])
                ->name('api.conseiller.live-stats');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ✅ SECTION AMÉLIORÉE : GESTION DE FILE D'ATTENTE CHRONOLOGIQUE FIFO
    |--------------------------------------------------------------------------
    */

    // 🎫 GÉNÉRATION DE TICKET (Postes Ecran uniquement) - FILE CHRONOLOGIQUE FIFO
    Route::post('/ecran/generate-ticket', [DashboardController::class, 'generateTicket'])
        ->name('ecran.generate-ticket');

    // 🎫 API STATISTIQUES TICKETS EN TEMPS RÉEL (Postes Ecran) - AVEC LOGIQUE FIFO
    Route::get('/api/ecran/queue-stats/{serviceId?}', function($serviceId = null) {
        if (!auth()->user()->isEcranUser()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        try {
            $user = auth()->user();
            $creator = $user->getCreator();
            
            if (!$creator) {
                return response()->json(['success' => false, 'message' => 'Créateur introuvable'], 500);
            }

            if ($serviceId) {
                // Statistiques d'un service spécifique avec file chronologique
                $service = \App\Models\Service::where('id', $serviceId)
                                            ->where('created_by', $creator->id)
                                            ->first();
                
                if (!$service) {
                    return response()->json(['success' => false, 'message' => 'Service non autorisé'], 403);
                }

                $stats = \App\Models\Queue::getServiceStats($serviceId);
                
                return response()->json([
                    'success' => true,
                    'service' => $service->nom,
                    'stats' => $stats,
                    'queue_info' => [
                        'type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'next_position' => \App\Models\Queue::calculateQueuePosition(),
                        'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes()
                    ],
                    'timestamp' => now()->format('H:i:s')
                ]);
            } else {
                // Statistiques globales de tous les services avec file chronologique
                $services = $creator->createdServices()->get();
                $globalStats = [
                    'total_tickets_today' => 0,
                    'total_waiting' => 0,
                    'total_in_progress' => 0,
                    'total_completed' => 0,
                    'services_stats' => []
                ];  

                foreach ($services as $service) {
                    $serviceStats = \App\Models\Queue::getServiceStats($service->id);
                    $globalStats['total_tickets_today'] += $serviceStats['total_tickets'];
                    $globalStats['total_waiting'] += $serviceStats['en_attente'];
                    $globalStats['total_in_progress'] += $serviceStats['en_cours'];
                    $globalStats['total_completed'] += $serviceStats['termines'];
                    
                    $globalStats['services_stats'][] = [
                        'service_id' => $service->id,
                        'service_name' => $service->nom,
                        'letter' => $service->letter_of_service,
                        'stats' => $serviceStats
                    ];
                }

                return response()->json([
                    'success' => true,
                    'global_stats' => $globalStats,
                    'queue_info' => [
                        'type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'global_position' => \App\Models\Queue::calculateQueuePosition(),
                        'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                        'total_waiting_global' => \App\Models\Queue::where('date', today())->where('statut_global', 'en_attente')->count()
                    ],
                    'timestamp' => now()->format('H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur API queue stats - File chronologique FIFO', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    })->name('api.ecran.queue-stats');

    // 🎫 HISTORIQUE DES TICKETS (Postes Ecran - consultation uniquement) - ORDRE CHRONOLOGIQUE
    Route::get('/api/ecran/tickets-history', function(Request $request) {
        if (!auth()->user()->isEcranUser()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        try {
            $user = auth()->user();
            $creator = $user->getCreator();
            
            if (!$creator) {
                return response()->json(['success' => false, 'message' => 'Créateur introuvable'], 500);
            }

            $serviceIds = $creator->createdServices()->pluck('id');
            $date = $request->get('date', today());
            $limit = min($request->get('limit', 50), 100); // Max 100 tickets

            // 🆕 ORDRE CHRONOLOGIQUE : Tri par created_at (FIFO)
            $tickets = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                       ->whereDate('date', $date)
                                       ->with('service')
                                       ->orderBy('created_at', 'desc') // Plus récents en premier pour l'historique
                                       ->limit($limit)
                                       ->get()
                                       ->map(function($ticket) {
                                           return $ticket->toTicketArray();
                                       });

            return response()->json([
                'success' => true,
                'tickets' => $tickets,
                'date' => \Carbon\Carbon::parse($date)->format('d/m/Y'),
                'total_count' => $tickets->count(),
                'queue_info' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'order_note' => 'Historique trié par heure d\'arrivée (plus récents en premier)'
                ],
                'timestamp' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur API tickets history - File chronologique', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    })->name('api.ecran.tickets-history');

    // 🆕 API POUR L'ORDRE CHRONOLOGIQUE DE LA FILE (Postes Ecran)
    Route::get('/api/ecran/chronological-queue', function(Request $request) {
        if (!auth()->user()->isEcranUser()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        try {
            $user = auth()->user();
            $creator = $user->getCreator();
            
            if (!$creator) {
                return response()->json(['success' => false, 'message' => 'Créateur introuvable'], 500);
            }

            $serviceIds = $creator->createdServices()->pluck('id');
            $date = $request->get('date', today());
            
            // 🎯 ORDRE CHRONOLOGIQUE FIFO : Tous tickets en attente triés par heure d'arrivée
            $chronologicalQueue = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                  ->whereDate('date', $date)
                                                  ->where('statut_global', 'en_attente')
                                                  ->orderBy('created_at', 'asc') // FIFO : Premier arrivé, premier servi
                                                  ->with('service')
                                                  ->get()
                                                  ->map(function($ticket, $index) {
                                                      $ticketArray = $ticket->toTicketArray();
                                                      $ticketArray['rang_chronologique'] = $index + 1; // Position dans la file
                                                      $ticketArray['heure_arrivee'] = $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s');
                                                      return $ticketArray;
                                                  });

            return response()->json([
                'success' => true,
                'chronological_queue' => $chronologicalQueue,
                'queue_stats' => [
                    'total_waiting' => $chronologicalQueue->count(),
                    'next_to_serve' => $chronologicalQueue->first(),
                    'last_in_queue' => $chronologicalQueue->last(),
                    'estimated_wait_next' => \App\Models\Queue::estimateWaitingTime(1)
                ],
                'queue_info' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'order_explanation' => 'Les tickets sont traités dans l\'ordre chronologique d\'arrivée, peu importe le service'
                ],
                'date' => \Carbon\Carbon::parse($date)->format('d/m/Y'),
                'timestamp' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur API chronological queue', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    })->name('api.ecran.chronological-queue');
    
    /*
    |--------------------------------------------------------------------------
    | ROUTES ADMINISTRATEURS UNIQUEMENT
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('admin')->group(function () {
        
        // Dashboard admin principal (layouts/app)
        Route::get('/layouts/app', [DashboardController::class, 'adminDashboard'])
            ->name('layouts.app');

        /*
        |--------------------------------------------------------------------------
        | ROUTES PARAMÈTRES GÉNÉRAUX - VERSION COMPLÈTE
        |--------------------------------------------------------------------------
        */

        // Routes principales des paramètres
        Route::prefix('layouts/setting')->group(function () {
            
            // Page principale des paramètres généraux
            Route::get('/general', [SettingsController::class, 'index'])
                ->name('layouts.setting');
            
            // Mise à jour des paramètres
            Route::put('/general', [SettingsController::class, 'update'])
                ->name('layouts.setting.update');
            
            // Actions sur les paramètres
            Route::post('/reset', [SettingsController::class, 'reset'])
                ->name('layouts.setting.reset');
            
            Route::post('/clear-cache', [SettingsController::class, 'clearCache'])
                ->name('layouts.setting.clear-cache');
            
            // API pour les paramètres (AJAX)
            Route::get('/api/group/{group}', [SettingsController::class, 'getGroupSettings'])
                ->name('layouts.setting.api.group');
            
            Route::post('/api/update', [SettingsController::class, 'updateSetting'])
                ->name('layouts.setting.api.update');
            
            Route::get('/api/stats', [SettingsController::class, 'getStats'])
                ->name('layouts.setting.api.stats');
        });

        // Route alternative pour compatibilité
        Route::get('/settings', function() {
            return redirect()->route('layouts.setting');
        });  

        /*
        |--------------------------------------------------------------------------
        | GESTION DES AGENCES
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('admin')->group(function () {
            
            // Routes principales des agences
            Route::get('/agencies', [AgencyController::class, 'index'])->name('agency.agence');
            Route::get('/agencies/create', [AgencyController::class, 'create'])->name('agency.agence-create');
            Route::post('/agencies', [AgencyController::class, 'store'])->name('agencies.store');
            Route::get('/agencies/{agency}', [AgencyController::class, 'show'])->name('agencies.show');
            Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agencies.edit');
            Route::put('/agencies/{agency}', [AgencyController::class, 'update'])->name('agencies.update');
            Route::delete('/agencies/{agency}', [AgencyController::class, 'destroy'])->name('agencies.destroy');
            
            // Routes pour les actions spécifiques des agences
            Route::post('/agencies/{agency}/activate', [AgencyController::class, 'activate'])->name('agencies.activate');
            Route::post('/agencies/{agency}/deactivate', [AgencyController::class, 'deactivate'])->name('agencies.deactivate');
            Route::get('/agencies/{agency}/details', [AgencyController::class, 'details'])->name('agencies.details');
            
            // Routes pour les actions en masse
            Route::post('/agencies/bulk-activate', [AgencyController::class, 'bulkActivate'])->name('agencies.bulk-activate');
            Route::post('/agencies/bulk-delete', [AgencyController::class, 'bulkDelete'])->name('agencies.bulk-delete');
            Route::get('/agencies/export', [AgencyController::class, 'export'])->name('agencies.export');
        });
        Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agency.agence-edit');

        /*
        |--------------------------------------------------------------------------
        | GESTION DES SERVICES
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')->group(function () {
            
            // Routes principales des services
            Route::get('/services', [ServiceController::class, 'index'])->name('service.service-list');
            Route::get('/services/create', [ServiceController::class, 'create'])->name('service.service-create');
            Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
            
            // Routes pour les actions spécifiques des services
            Route::post('/services/{service}/activate', [ServiceController::class, 'activate'])->name('services.activate');
            Route::post('/services/{service}/deactivate', [ServiceController::class, 'deactivate'])->name('services.deactivate');
            Route::get('/services/{service}/details', [ServiceController::class, 'details'])->name('services.details');
            
            // Routes pour les actions en masse
            Route::post('/services/bulk-activate', [ServiceController::class, 'bulkActivate'])->name('services.bulk-activate');
            Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
            Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
            
            // API pour statistiques des services
            Route::get('/api/services/stats', [ServiceController::class, 'getStats'])->name('services.api.stats');
            
            // 🆕 API pour la recherche de services (AJAX)
            Route::get('/api/services/search', [ServiceController::class, 'searchServices'])->name('services.api.search');
        });
         
        // ✅ NOUVELLE ROUTE : Formulaire d'édition de service
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');

        // ✅ NOUVELLE ROUTE : Mise à jour de service (PUT)
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');

        // ✅ NOUVELLE ROUTE : Statistiques d'un service pour le modal
        Route::get('/services/{service}/stats', [ServiceController::class, 'getServiceStats'])->name('services.stats');

        // ✅ MODIFICATION : Route existante check-letter-availability pour supporter exclude_id
        Route::post('/services/check-letter-availability', [ServiceController::class, 'checkLetterAvailability'])
            ->name('services.check-letter-availability');

        /*
        |--------------------------------------------------------------------------
        | ✅ AMÉLIORÉ : GESTION DES FILES D'ATTENTE CHRONOLOGIQUE (ADMIN)
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin/queue')->group(function () {
            
            // 📊 Tableau de bord des files d'attente avec logique chronologique FIFO
            Route::get('/dashboard', function() {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $todayStats = [
                    'total_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->count(),
                    'waiting_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'en_attente')->count(),
                    'processing_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'en_cours')->count(),
                    'completed_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'termine')->count(),
                    'average_wait_time' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->avg('temps_attente_estime') ?? 0,
                    // 🆕 NOUVEAU : Statistiques de la file chronologique
                    'next_global_position' => \App\Models\Queue::calculateQueuePosition(),
                    'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                ];

                $services = \App\Models\Service::where('created_by', $admin->id)
                                             ->with(['queues' => function($q) {
                                                 $q->whereDate('date', today());
                                             }])
                                             ->get()
                                             ->map(function($service) {
                                                 $service->today_stats = \App\Models\Queue::getServiceStats($service->id);
                                                 return $service;
                                             });

                // 🆕 NOUVEAU : File d'attente chronologique globale
                $chronologicalQueue = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                      ->whereDate('date', today())
                                                      ->where('statut_global', 'en_attente')
                                                      ->orderBy('created_at', 'asc') // FIFO
                                                      ->with('service')
                                                      ->limit(20)
                                                      ->get();

                return view('admin.queue.dashboard', compact('todayStats', 'services', 'chronologicalQueue'));
            })->name('admin.queue.dashboard');

            // 📋 Liste des tickets avec ordre chronologique
            Route::get('/tickets', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $query = \App\Models\Queue::whereIn('service_id', $serviceIds)->with('service');
                
                // Filtres
                if ($request->filled('date')) {
                    $query->whereDate('date', $request->date);
                } else {
                    $query->whereDate('date', today());
                }
                
                if ($request->filled('service_id')) {
                    $query->where('service_id', $request->service_id);
                }
                
                if ($request->filled('statut')) {
                    $query->where('statut_global', $request->statut);
                }
                
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('numero_ticket', 'LIKE', "%{$search}%")
                          ->orWhere('prenom', 'LIKE', "%{$search}%")
                          ->orWhere('telephone', 'LIKE', "%{$search}%");
                    });
                }
                
                // 🆕 TRI CHRONOLOGIQUE : Par défaut, ordre d'arrivée (FIFO)
                $sortBy = $request->get('sort', 'created_at');
                $sortOrder = $request->get('order', 'asc'); // ASC pour FIFO
                $query->orderBy($sortBy, $sortOrder);
                
                $tickets = $query->paginate(20);
                $services = \App\Models\Service::where('created_by', $admin->id)->get();
                
                return view('admin.queue.tickets', compact('tickets', 'services'));
            })->name('admin.queue.tickets');

            // 📈 Statistiques avancées avec file chronologique
            Route::get('/stats', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $period = $request->get('period', 'today');
                $dateRange = match($period) {
                    'today' => [today(), today()],
                    'week' => [now()->startOfWeek(), now()->endOfWeek()],
                    'month' => [now()->startOfMonth(), now()->endOfMonth()],
                    default => [today(), today()]
                };
                
                $stats = [
                    'period_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                        ->whereBetween('date', $dateRange)
                                                        ->count(),
                    'period_completed' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                          ->whereBetween('date', $dateRange)
                                                          ->where('statut_global', 'termine')
                                                          ->count(),
                    'average_processing_time' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                                 ->whereBetween('date', $dateRange)
                                                                 ->whereNotNull('heure_de_fin')
                                                                 ->avg(\DB::raw('TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60')) ?? 0,
                    'busiest_service' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                         ->whereBetween('date', $dateRange)
                                                         ->select('service_id', \DB::raw('COUNT(*) as ticket_count'))
                                                         ->groupBy('service_id')
                                                         ->orderBy('ticket_count', 'desc')
                                                         ->with('service')
                                                         ->first(),
                    // 🆕 NOUVEAU : Statistiques spécifiques à la file chronologique
                    'chronological_stats' => [
                        'queue_type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                        'peak_hours' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                        ->whereBetween('date', $dateRange)
                                                        ->selectRaw('HOUR(heure_d_enregistrement) as hour, COUNT(*) as count')
                                                        ->groupBy('hour')
                                                        ->orderBy('count', 'desc')
                                                        ->limit(3)
                                                        ->get()
                                                        ->toArray()
                    ]
                ];
                
                return view('admin.queue.stats', compact('stats', 'period'));
            })->name('admin.queue.stats');

            // 🗂️ Export des données avec ordre chronologique
            Route::get('/export', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $date = $request->get('date', today());
                
                // 🆕 TRI CHRONOLOGIQUE pour l'export
                $tickets = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                          ->whereDate('date', $date)
                                          ->with('service')
                                          ->orderBy('created_at', 'asc') // FIFO dans l'export
                                          ->get();
                
                $filename = 'tickets_chronological_' . \Carbon\Carbon::parse($date)->format('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($tickets) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
                    
                    fputcsv($file, [
                        'Rang Chronologique',
                        'Numéro Ticket',
                        'Service',
                        'Client',
                        'Téléphone',
                        'Date',
                        'Heure Arrivée',
                        'Position Globale',
                        'Temps Attente Estimé',
                        'Statut',
                        'Conseiller',
                        'Commentaire'
                    ], ';');
                    
                    foreach ($tickets as $index => $ticket) {
                        fputcsv($file, [
                            $index + 1, // Rang chronologique
                            $ticket->numero_ticket,
                            $ticket->service ? $ticket->service->nom : 'N/A',
                            $ticket->prenom,
                            $ticket->telephone,
                            $ticket->date->format('d/m/Y'),
                            $ticket->heure_d_enregistrement,
                            $ticket->position_file,
                            $ticket->temps_attente_estime . ' min',
                            $ticket->getStatutLibelle(),
                            $ticket->conseillerClient ? $ticket->conseillerClient->username : 'N/A',
                            $ticket->commentaire ?: ''
                        ], ';');
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            })->name('admin.queue.export');

            // 🆕 NOUVEAU : API pour la file chronologique globale (Admin)
            Route::get('/api/chronological-global', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                $date = $request->get('date', today());
                
                $globalStats = \App\Models\Queue::getGlobalQueueStats($date);
                $chronologicalOrder = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                      ->whereDate('date', $date)
                                                      ->where('statut_global', 'en_attente')
                                                      ->orderBy('created_at', 'asc')
                                                      ->with('service')
                                                      ->get()
                                                      ->map(function($ticket, $index) {
                                                          return [
                                                              'rang' => $index + 1,
                                                              'numero_ticket' => $ticket->numero_ticket,
                                                              'service' => $ticket->service->nom,
                                                              'client' => $ticket->prenom,
                                                              'heure_arrivee' => $ticket->heure_d_enregistrement,
                                                              'temps_attente_estime' => $ticket->temps_attente_estime
                                                          ];
                                                      });

                return response()->json([
                    'success' => true,
                    'global_stats' => $globalStats,
                    'chronological_order' => $chronologicalOrder,
                    'queue_info' => [
                        'type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'note' => 'Ordre de traitement basé sur l\'heure d\'arrivée'
                    ],
                    'timestamp' => now()->format('H:i:s')
                ]);
            })->name('admin.queue.api.chronological-global');
        });
                                  
        /*
        |--------------------------------------------------------------------------
        | GESTION DES UTILISATEURS
        |--------------------------------------------------------------------------
        */
                                                
        // Liste des utilisateurs 
        Route::get('/user/users-list', [DashboardController::class, 'usersList'])
            ->name('user.users-list');
        
        // Création d'utilisateurs 
        Route::get('/admin/user/create', [UserManagementController::class, 'create'])
            ->name('User.user-create');
        Route::post('/admin/user/store', [UserManagementController::class, 'store'])
            ->name('User.user.store');
              
        // Mes utilisateurs créés
        Route::get('/admin/users/my-created', [UserManagementController::class, 'myCreatedUsers'])
            ->name('User.user.my-created');
            
        // Modification d'utilisateurs
        Route::get('/admin/user/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('User.user-edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
            ->name('User.user.update');
            
        // Renvoyer identifiants
        Route::post('/admin/users/{user}/resend-credentials', [UserManagementController::class, 'resendCredentials'])
            ->name('admin.users.resend-credentials');

        // 🆕 Changement de type d'utilisateur
        Route::post('/admin/users/{user}/change-type', [UserManagementController::class, 'changeUserType'])
            ->name('admin.users.change-type');
           
        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR LES UTILISATEURS (DashboardController)
        |--------------------------------------------------------------------------
        */
        
        // Activation/Suspension/Réactivation (PATCH)
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend');
        Route::patch('/admin/users/{user}/reactivate', [DashboardController::class, 'reactivateUser'])
            ->name('admin.users.reactivate');
              
        // Routes POST alternatives pour le JavaScript (compatibilité)
        Route::post('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate.post');
        Route::post('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend.post');
        Route::post('/admin/users/{user}/reactivate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.reactivate.post');
        
        // Suppression
        Route::delete('/admin/users/{user}', [DashboardController::class, 'deleteUser'])
            ->name('admin.users.delete');
        
        // Réinitialisation mot de passe depuis modal détails
        Route::post('/admin/users/{user}/reset-password', [DashboardController::class, 'resetUserPassword'])
            ->name('admin.users.reset-password');
        
        // Actions en masse
        Route::post('/admin/users/bulk-activate', [DashboardController::class, 'bulkActivate'])
            ->name('admin.users.bulk-activate');
        Route::post('/admin/users/bulk-delete', [DashboardController::class, 'bulkDeleteUsers'])
            ->name('admin.users.bulk-delete');
        
        // Export
        Route::get('/admin/users/export', [DashboardController::class, 'exportUsers'])
            ->name('admin.users.export');
        
        /*
        |--------------------------------------------------------------------------
        | API AJAX POUR ADMINS (avec statistiques file chronologique)
        |--------------------------------------------------------------------------
        */
        
        // Statistiques en temps réel (incluant file chronologique)
        Route::get('/admin/api/stats', [DashboardController::class, 'getStats'])
            ->name('admin.api.stats');
        Route::get('/admin/api/advanced-stats', [DashboardController::class, 'getAdvancedStats'])
            ->name('admin.api.advanced-stats');
        
        // Recherche d'utilisateurs
        Route::get('/admin/api/search-users', [DashboardController::class, 'searchUsers'])
            ->name('admin.api.search-users');
        
        // Détails utilisateur (AJAX)
        Route::get('/admin/api/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.api.user-details');
        Route::get('/admin/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.users.details');
         
        // Statistiques admin personnalisées (UserManagementController)
        Route::get('/admin/api/my-stats', [UserManagementController::class, 'getMyUserStats'])
            ->name('admin.api.my-stats');

        // 🆕 API pour obtenir les rôles disponibles
        Route::get('/admin/api/available-roles', [UserManagementController::class, 'getAvailableRolesApi'])
            ->name('admin.api.available-roles');
    });
});

/*
|--------------------------------------------------------------------------
| 🆕 API POUR LA VÉRIFICATION DES SESSIONS EN TEMPS RÉEL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Route pour vérifier la fermeture automatique des sessions (AJAX)
    Route::get('/api/session/check-closure', [LoginController::class, 'checkSessionClosure'])
        ->name('api.session.check-closure');

    // Route pour obtenir les informations de session (AJAX)
    Route::get('/api/session/info', function(Request $request) {
        if (!Auth::check()) {
            return response()->json(['authenticated' => false]);
        }
        
        $user = Auth::user();
        $settings = Setting::validateSessionSettings();
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'type' => $user->getTypeName(),
                'type_role' => $user->getUserRole(),
                'is_admin' => $user->isAdmin(),
                'is_ecran' => $user->isEcranUser(),
                'is_accueil' => $user->isAccueilUser(),
                'is_conseiller' => $user->isConseillerUser(),
            ],
            'session_settings' => $settings,
            'security_info' => $user->getSecurityInfo(),
            'required_actions' => $user->getRequiredActions(),
            'server_time' => now()->format('H:i:s')
        ]);
    })->name('api.session.info');
});

/*
|--------------------------------------------------------------------------
| 🆕 ROUTES API POUR LES PARAMÈTRES (AJAX)
|--------------------------------------------------------------------------
*/

// API publique pour les paramètres (sans authentification)
Route::prefix('api/settings')->group(function () {
    
    // Obtenir les paramètres publics (comme le nom de l'app)
    Route::get('/public', function() {
        return response()->json([
            'app_name' => Setting::get('app_name', 'Attendis'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'auto_session_closure' => Setting::isAutoSessionClosureEnabled(),
            'closure_time' => Setting::getSessionClosureTime(),
            // 🆕 NOUVEAU : Paramètres de la file d'attente
            'queue_type' => 'fifo_chronological',
            'queue_principle' => 'Premier arrivé, premier servi',
            'default_wait_time' => Setting::getDefaultWaitingTimeMinutes()
        ]);
    });
    
    // Vérifier si un paramètre spécifique est activé
    Route::get('/check/{key}', function($key) {
        $allowedKeys = [
            'auto_detect_available_advisors',
            'auto_assign_all_services_to_advisors', 
            'enable_auto_session_closure',
            'maintenance_mode',
            'default_waiting_time_minutes' // 🆕 NOUVEAU paramètre
        ];
        
        if (!in_array($key, $allowedKeys)) {
            return response()->json(['error' => 'Paramètre non autorisé'], 403);
        }
        
        return response()->json([
            'key' => $key,
            'value' => Setting::get($key),
            'active' => (bool) Setting::get($key)
        ]);
    });

    // 🆕 NOUVEAU : API pour les paramètres de file d'attente
    Route::get('/queue-settings', function() {
        return response()->json([
            'queue_type' => 'fifo_chronological',
            'principle' => 'Premier arrivé, premier servi',
            'configured_wait_time' => Setting::getDefaultWaitingTimeMinutes(),
            'admin_can_configure' => true,
            'description' => 'Les tickets sont traités dans l\'ordre chronologique d\'arrivée, peu importe le service'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| 🆕 ROUTES API UTILITAIRES POUR LES INTERFACES (avec file chronologique)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    
    // API pour rafraîchir les données selon le type d'utilisateur
    Route::get('/api/dashboard/refresh', function(Request $request) {
        $user = Auth::user();
        
        if ($user->isEcranUser()) {
            // Données pour interface Poste Ecran avec file chronologique
            $creator = $user->getCreator();
            $services = $creator ? $creator->createdServices()->get() : collect();
            
            return response()->json([
                'success' => true,
                'type' => 'ecran',
                'data' => [
                    'services_count' => $services->count(),
                    'active_services' => $services->where('statut', 'actif')->count(),
                    'inactive_services' => $services->where('statut', 'inactif')->count(),
                    'recent_services' => $services->where('created_at', '>=', now()->subDays(7))->count(),
                    'last_update' => now()->format('H:i:s'),
                    // 🆕 NOUVEAU : Informations sur la file chronologique
                    'queue_info' => [
                        'type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'next_position' => \App\Models\Queue::calculateQueuePosition(),
                        'configured_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes()
                    ]
                ]
            ]);
            
        } elseif ($user->isConseillerUser()) {
            // Données pour interface Conseiller
            return response()->json([
                'success' => true,
                'type' => 'conseiller',
                'data' => [
                    'user_type' => $user->getTypeName(),
                    'days_active' => $user->created_at->diffInDays(now()),
                    'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                    'last_update' => now()->format('H:i:s'),
                    'queue_info' => [
                        'type' => 'fifo_chronological',
                        'principle' => 'Premier arrivé, premier servi',
                        'role' => 'Traitement des tickets dans l\'ordre chronologique'
                    ]
                ]
            ]);
        } else {
            // Données pour interface Accueil
            return response()->json([
                'success' => true,
                'type' => $user->getUserRole(),
                'data' => [
                    'user_type' => $user->getTypeName(),
                    'days_active' => $user->created_at->diffInDays(now()),
                    'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                    'last_update' => now()->format('H:i:s'),
                ]
            ]);
        }
    })->name('api.dashboard.refresh');

    // API pour obtenir les conseils métier selon le type
    Route::get('/api/user/tips/{type?}', function($type = null) {
        $user = Auth::user();
        $userType = $type ?: $user->getUserRole();
        
        $tips = [
            'ecran' => [
                'Vérifiez régulièrement les nouveaux services',
                'Utilisez la recherche pour trouver rapidement un service',
                'L\'interface se met à jour automatiquement toutes les 5 minutes',
                '🆕 Les tickets sont traités par ordre d\'arrivée (FIFO)',
                '🆕 Le temps d\'attente est configuré par votre administrateur'
            ],
            'accueil' => [
                'Accueillez chaleureusement tous les visiteurs',
                'Orientez les visiteurs vers les bons services',
                'Tenez à jour les informations d\'accueil'
            ],
            'conseiller' => [
                '🎯 Traitez les tickets dans l\'ordre chronologique (FIFO)',
                '📞 Utilisez "Appeler suivant" pour le prochain ticket',
                '✅ Documentez la résolution de chaque ticket',
                '⏸️ Activez la pause si vous devez vous absenter',
                '📊 Consultez vos statistiques pour améliorer vos performances'
            ]
        ];
        
        return response()->json([
            'success' => true,
            'type' => $userType,
            'tips' => $tips[$userType] ?? []
        ]);
    })->name('api.user.tips');
});

/*
|--------------------------------------------------------------------------
| ✅ ROUTES DE COMPATIBILITÉ - CORRIGÉES POUR ÉVITER LES BOUCLES
|--------------------------------------------------------------------------
*/

// ✅ CORRECTION #3 : Routes de compatibilité sécurisées avec appels directs
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/app', function () {
        $user = auth()->user();
        
        try {
            if ($user->isAdmin()) {
                return app(DashboardController::class)->adminDashboard();
            } elseif ($user->isConseillerUser()) {
                return app(DashboardController::class)->conseillerDashboard();
            } else {
                return app(DashboardController::class)->userDashboard();
            }
        } catch (\Exception $e) {
            \Log::error('Erreur route compatibilité /app', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Erreur système. Veuillez vous reconnecter.');
        }
    });
    
    // 🆕 Redirection spécifique pour les anciens liens directs
    Route::get('/app-ecran', function () {
        try {
            return app(DashboardController::class)->userDashboard();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Erreur de redirection.');
        }
    });
    
    Route::get('/app-accueil', function () {
        try {
            return app(DashboardController::class)->userDashboard();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Erreur de redirection.');
        }
    });
    
    Route::get('/app-conseiller', function () {
        try {
            return app(DashboardController::class)->conseillerDashboard();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Erreur de redirection.');
        }
    });
});

/*
|--------------------------------------------------------------------------
| ✅ GESTION DES ERREURS - CORRIGÉE
|--------------------------------------------------------------------------
*/

// ✅ CORRECTION #4 : Route fallback sécurisée avec appels directs
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        try {
            // Appels directs au contrôleur au lieu de redirections
            if ($user->isAdmin()) {
                return app(DashboardController::class)->adminDashboard()
                    ->with('warning', 'Page non trouvée. Redirection vers le dashboard admin.');
            } elseif ($user->isConseillerUser()) {
                return app(DashboardController::class)->conseillerDashboard()
                    ->with('warning', 'Page non trouvée. Redirection vers votre interface conseiller.');
            } else {
                return app(DashboardController::class)->userDashboard()
                    ->with('warning', "Page non trouvée. Redirection vers votre espace {$user->getTypeName()}.");
            }
        } catch (\Exception $e) {
            \Log::error('Erreur route fallback', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            // En cas d'erreur, redirection sécurisée vers login
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Erreur système. Veuillez vous reconnecter.');
        }
    }
    
    return redirect()->route('login')
        ->with('error', 'Page non trouvée. Veuillez vous connecter.');
});

/*
|--------------------------------------------------------------------------
| ROUTES DE DÉVELOPPEMENT (À supprimer en production)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    
    // Test des relations
    Route::get('/dev/test-relations', function () {
        $users = \App\Models\User::with(['createdBy.administrator', 'createdUsers.user'])->get();
        return response()->json($users->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'type' => $user->getTypeName(),
                'type_role' => $user->getUserRole(),
                'created_by' => $user->getCreator() ? $user->getCreator()->username : null,
                'created_users_count' => $user->createdUsers()->count(),
            ];
        }));
    })->middleware('admin');
    
    // Test des statistiques
    Route::get('/dev/test-stats', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_relations' => \App\Models\AdministratorUser::count(),
            'admins' => \App\Models\User::where('user_type_id', 1)->count(),
            'ecran_users' => \App\Models\User::where('user_type_id', 2)->count(),
            'accueil_users' => \App\Models\User::where('user_type_id', 3)->count(),
            'conseiller_users' => \App\Models\User::where('user_type_id', 4)->count(),
        ]);
    })->middleware('admin');

    // 🆕 Test de la différenciation des interfaces
    Route::get('/dev/test-interfaces', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        
        $user = auth()->user();
        
        return response()->json([
            'user_id' => $user->id,
            'username' => $user->username,
            'type_id' => $user->user_type_id,
            'type_name' => $user->getTypeName(),
            'type_role' => $user->getUserRole(),
            'is_admin' => $user->isAdmin(),
            'is_ecran' => $user->isEcranUser(),
            'is_accueil' => $user->isAccueilUser(),
            'is_conseiller' => $user->isConseillerUser(),
            'interface_destination' => match(true) {
                $user->isAdmin() => 'layouts.app',
                $user->isConseillerUser() => 'layouts.app-conseiller',
                default => 'layouts.app-users'
            },
            'interface_type' => match(true) {
                $user->isAdmin() => 'app.blade.php',
                $user->isEcranUser() => 'app-ecran.blade.php',
                $user->isConseillerUser() => 'app-conseiller.blade.php',
                default => 'app-users.blade.php'
            },
            'creator' => $user->getCreator() ? $user->getCreator()->username : null,
            'services_count' => $user->getCreator() ? $user->getCreator()->createdServices()->count() : 0,
        ]);
    })->middleware('auth');

    // ✅ AMÉLIORÉ : Test de génération de tickets avec file chronologique FIFO
    Route::get('/dev/test-ticket-generation-fifo', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        
        $user = auth()->user();
        $creator = $user->getCreator();
        
        if (!$creator) {
            return response()->json(['error' => 'Pas de créateur trouvé']);
        }
    
        $services = $creator->createdServices()->get();
        
        if ($services->isEmpty()) {
            return response()->json(['error' => 'Aucun service trouvé']);
        }
        
        $service = $services->first();
        
        try {
            // Test de génération de ticket avec file chronologique
            $ticketData = [
                'service_id' => $service->id,
                'prenom' => 'Test Client FIFO',
                'telephone' => '0123456789',
                'commentaire' => 'Test de génération automatique - File chronologique FIFO'
            ];
            
            $ticket = \App\Models\Queue::createTicket($ticketData);
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket de test généré avec succès - File chronologique FIFO',
                'ticket' => $ticket->toTicketArray(),
                'service' => [
                    'id' => $service->id,
                    'nom' => $service->nom,
                    'letter_of_service' => $service->letter_of_service
                ],
                'queue_stats' => \App\Models\Queue::getServiceStats($service->id),
                'queue_info' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'next_position' => \App\Models\Queue::calculateQueuePosition(),
                    'configured_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes()
                ],
                'chronological_queue' => \App\Models\Queue::getChronologicalQueue(),
                'global_stats' => \App\Models\Queue::getGlobalQueueStats()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur génération ticket FIFO: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');

    // 🆕 NOUVEAU : Test de l'interface conseiller
    Route::get('/dev/test-conseiller-interface', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        
        $user = auth()->user();
        
        if (!$user->isConseillerUser()) {
            return response()->json(['error' => 'Utilisateur non conseiller']);
        }
        
        try {
            $creator = $user->getCreator();
            $myServiceIds = \App\Models\Service::where('created_by', $creator->id)->pluck('id');
            
            // Simuler les données de l'interface conseiller
            $interfaceData = [
                'file_stats' => [
                    'tickets_en_attente' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                            ->whereDate('date', today())
                                                            ->where('statut_global', 'en_attente')
                                                            ->count(),
                    'tickets_en_cours' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                          ->whereDate('date', today())
                                                          ->where('statut_global', 'en_cours')
                                                          ->count(),
                    'tickets_termines' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                          ->whereDate('date', today())
                                                          ->where('statut_global', 'termine')
                                                          ->count(),
                ],
                'conseiller_stats' => [
                    'tickets_traites_aujourd_hui' => \App\Models\Queue::where('conseiller_client_id', $user->id)
                                                                      ->whereDate('date', today())
                                                                      ->where('statut_global', 'termine')
                                                                      ->count(),
                    'ticket_en_cours' => \App\Models\Queue::where('conseiller_client_id', $user->id)
                                                          ->whereDate('date', today())
                                                          ->where('statut_global', 'en_cours')
                                                          ->first(),
                ],
                'next_ticket_preview' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                          ->whereDate('date', today())
                                                          ->where('statut_global', 'en_attente')
                                                          ->orderBy('created_at', 'asc')
                                                          ->first(),
                'queue_info' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'interface_status' => 'ready'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Interface conseiller testée avec succès',
                'conseiller_info' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'creator' => $creator->username
                ],
                'interface_data' => $interfaceData,
                'routes_available' => [
                    'conseiller.tickets' => route('conseiller.tickets'),
                    'conseiller.call-ticket' => route('conseiller.call-ticket'),
                    'conseiller.complete-ticket' => route('conseiller.complete-ticket'),
                    'conseiller.my-stats' => route('conseiller.my-stats'),
                    'conseiller.history' => route('conseiller.history')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur test interface conseiller: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');

    // 🆕 NOUVEAU : Test de la file chronologique
    Route::get('/dev/test-chronological-queue', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        
        try {
            $user = auth()->user();
            $creator = $user->getCreator();
            
            if (!$creator) {
                return response()->json(['error' => 'Pas de créateur trouvé']);
            }
            
            $serviceIds = $creator->createdServices()->pluck('id');
            
            return response()->json([
                'success' => true,
                'queue_type' => 'fifo_chronological',
                'principle' => 'Premier arrivé, premier servi',
                'chronological_queue' => \App\Models\Queue::getChronologicalQueue(),
                'next_ticket_global' => \App\Models\Queue::getNextTicketGlobal(),
                'global_queue_stats' => \App\Models\Queue::getGlobalQueueStats(),
                'services_queue_stats' => $serviceIds->map(function($serviceId) {
                    return [
                        'service_id' => $serviceId,
                        'stats' => \App\Models\Queue::getServiceStats($serviceId),
                        'chronological_queue' => \App\Models\Queue::getServiceQueueChronological($serviceId)
                    ];
                })->toArray(),
                'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'next_global_position' => \App\Models\Queue::calculateQueuePosition()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur test file chronologique: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | ROUTES DE DÉVELOPPEMENT POUR LES PARAMÈTRES (avec file chronologique)
    |--------------------------------------------------------------------------
    */

    Route::prefix('dev/settings')->middleware(['auth', 'admin'])->group(function () {
        
        // Tester tous les paramètres (incluant file d'attente)
        Route::get('/test-all', function() {
            return response()->json([
                'user_management' => Setting::getUserManagementSettings(),
                'security' => Setting::getSecuritySettings(),
                'all_settings' => Setting::getAllSettings(),
                'stats' => Setting::getStats(),
                'consistency_check' => Setting::checkConsistency(),
                // 🆕 NOUVEAU : Paramètres de file d'attente
                'queue_settings' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'default_wait_time' => Setting::getDefaultWaitingTimeMinutes(),
                    'admin_configurable' => true
                ]
            ]);
        });
        
        // Forcer une valeur pour test
        Route::post('/force/{key}', function(Request $request, $key) {
            $value = $request->input('value');
            $type = $request->input('type', 'string');
            
            $success = Setting::set($key, $value, $type);
            
            return response()->json([
                'success' => $success,
                'key' => $key,
                'new_value' => Setting::get($key),
                'message' => $success ? 'Paramètre forcé avec succès' : 'Erreur lors du forçage'
            ]);
        });
        
        // Simuler la fermeture automatique
        Route::post('/simulate-closure', function() {
            // Forcer la fermeture pour test
            Setting::set('enable_auto_session_closure', true, 'boolean');
            Setting::set('auto_session_closure_time', now()->format('H:i'), 'time');
            
            return response()->json([
                'message' => 'Fermeture automatique simulée',
                'closure_time' => Setting::getSessionClosureTime(),
                'should_close_now' => Setting::shouldCloseSessionsNow()
            ]);
        });

        // 🆕 NOUVEAU : Tester les paramètres de temps d'attente
        Route::post('/test-wait-time/{minutes}', function($minutes) {
            Setting::set('default_waiting_time_minutes', (int)$minutes, 'integer');
            
            // Tester le calcul avec le nouveau temps
            $position = 5; // Exemple : 5ème en file
            $estimatedTime = \App\Models\Queue::estimateWaitingTime($position);
            
            return response()->json([
                'message' => 'Temps d\'attente modifié pour test',
                'configured_time' => (int)$minutes,
                'position_test' => $position,
                'estimated_time_calculated' => $estimatedTime,
                'calculation_formula' => "({$position} - 1) × {$minutes} = {$estimatedTime} minutes"
            ]);
        });
    });     
}