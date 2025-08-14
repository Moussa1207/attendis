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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Queue;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (Authentification)
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ROUTES MOT DE PASSE OUBLIÉ
|--------------------------------------------------------------------------
*/

Route::get('/password/forgot', [PasswordManagementController::class, 'showForgotForm'])
    ->name('password.forgot');
Route::post('/password/email', [PasswordManagementController::class, 'sendResetEmail'])
    ->name('password.email');
Route::get('/password/reset/{token}/{user}', [PasswordManagementController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/password/update', [PasswordManagementController::class, 'resetPassword'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| ROUTES CHANGEMENT MOT DE PASSE OBLIGATOIRE
|--------------------------------------------------------------------------
*/

Route::get('/password/mandatory-change', [LoginController::class, 'showMandatoryPasswordChange'])
    ->name('password.mandatory-change')
    ->middleware('web');
Route::post('/password/mandatory-update', [LoginController::class, 'updateMandatoryPassword'])
    ->name('password.mandatory-update')
    ->middleware('web');

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (Utilisateurs connectés)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/password/change', [PasswordManagementController::class, 'changePassword'])
        ->name('password.change');
    
    /*
    |--------------------------------------------------------------------------
    | ROUTES UTILISATEURS
    |--------------------------------------------------------------------------
    */
    Route::get('/layouts/app-users', [DashboardController::class, 'userDashboard'])
        ->name('layouts.app-users');
    Route::get('/api/conseiller/available-advisors', [DashboardController::class, 'getAvailableAdvisors']);
    Route::get('/api/conseiller/{id}/workload', [DashboardController::class, 'getAdvisorWorkload']);
    Route::get('/api/user/services/refresh', [DashboardController::class, 'refreshUserServices'])
        ->name('api.user.services.refresh');
    Route::get('/api/user/info', [DashboardController::class, 'getUserInfo'])
        ->name('api.user.info');
    Route::get('/api/user/type-guide/{type?}', [DashboardController::class, 'getTypeGuide'])
        ->name('api.user.type-guide');

    /*
    |--------------------------------------------------------------------------
    | INTERFACE CONSEILLER
    |--------------------------------------------------------------------------
    */
    Route::get('/layouts/app-conseiller', [DashboardController::class, 'conseillerDashboard'])
        ->name('layouts.app-conseiller')
        ->middleware('conseiller');

    Route::middleware('conseiller')->group(function () {
        Route::prefix('conseiller')->group(function () {
            Route::get('/tickets', [DashboardController::class, 'getConseillerTickets'])
                ->name('conseiller.tickets');
            Route::post('/call-ticket', [DashboardController::class, 'callNextTicket'])
                ->name('conseiller.call-ticket');
            Route::post('/complete-ticket', [DashboardController::class, 'completeCurrentTicket'])
                ->name('conseiller.complete-ticket');
            Route::get('/my-stats', [DashboardController::class, 'getConseillerStats'])
                ->name('conseiller.my-stats');
            Route::get('/ticket/{id}/resolution-details', [DashboardController::class, 'getTicketResolutionDetails'])
                ->name('conseiller.ticket-resolution-details');
            Route::get('/history', [DashboardController::class, 'getConseillerHistory'])
                ->name('conseiller.history');
            Route::post('/toggle-pause', [DashboardController::class, 'toggleConseillerPause'])
                ->name('conseiller.toggle-pause');
            Route::get('/ticket/{id}/details', [DashboardController::class, 'getTicketDetails'])
                ->name('conseiller.ticket-details');
            Route::post('/transfer-ticket', [DashboardController::class, 'transferTicket'])
                ->name('conseiller.transfer-ticket');
            Route::get('/export', [DashboardController::class, 'exportConseillerData'])
                ->name('conseiller.export');

            Route::post('/validate-resolution-comment', function(Request $request) {
                $validator = Validator::make($request->all(), [
                    'action' => 'required|in:traiter,refuser',
                    'commentaire' => 'nullable|string|max:500'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $action = $request->input('action');
                $commentaire = $request->input('commentaire', '');

                if ($action === 'refuser' && empty(trim($commentaire))) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['commentaire' => ['Le commentaire est obligatoire pour refuser un ticket']]
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Validation réussie',
                    'data' => [
                        'action' => $action,
                        'commentaire_length' => strlen(trim($commentaire)),
                        'is_comment_required' => $action === 'refuser',
                        'is_comment_provided' => !empty(trim($commentaire))
                    ]
                ]);
            })->name('conseiller.validate-resolution-comment'); });
            
 Route::get('/resolution-stats', function(Request $request) {
    try {
        $user = Auth::user();
        if (!$user->isConseillerUser()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $date   = $request->get('date', today());
        $period = $request->get('period', 'today');

        $dateRange = match($period) {
            'today' => [$date, $date],
            'week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [$date, $date],
        };

        $base = Queue::where('conseiller_client_id', $user->id)
                     ->where('statut_global', 'termine');

        if ($period === 'today') {
            $base = $base->whereDate('date', $date);
        } else {
            $base = $base->whereBetween('date', $dateRange);
        }

        $totalTraites  = (clone $base)->count();
        $resolus       = (clone $base)->where('resolu', 1)->count();
        $nonResolus    = (clone $base)->where('resolu', 0)->count();

        // ✅ tickets reçus (par transfert) que CE conseiller a terminés
        $recusTraites  = (clone $base)->where('transferer', 'new')->count();

        // ✅ tickets traités “normaux”
        $normauxTraites = max(0, $totalTraites - $recusTraites);

        // ✅ temps moyen de traitement (prise en charge -> fin), en minutes
        $avgDurationMin = (clone $base)
            ->whereNotNull('heure_prise_en_charge')
            ->whereNotNull('heure_de_fin')
            ->avg(\DB::raw('TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60'));
        $avgDurationMin = round($avgDurationMin ?? 0, 1);

        // taux utiles
        $tauxResolution  = $totalTraites > 0 ? round(($resolus / $totalTraites) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'period'  => $period,
            'date_range' => $dateRange,
            'resolution_stats' => [
                'total_traites'             => $totalTraites,
                'tickets_resolus'           => $resolus,
                'tickets_non_resolus'       => $nonResolus,
                'tickets_recus_traites'     => $recusTraites,    // 👈 “reçu par transfert” ET terminé
                'tickets_normaux_traites'   => $normauxTraites,  // 👈 traités sans transfert
                'taux_resolution'           => $tauxResolution,
                'avg_processing_time_min'   => $avgDurationMin,  // 👈 temps moyen propre
            ],
            'conseiller_info' => [
                'username' => $user->username,
                'email'    => $user->email,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Erreur stats historique'], 500);
    }
})->name('conseiller.resolution-stats');

            
           Route::get('/resolution-history/{action?}', function(Request $request, $action = null) {
    try {
        $user = Auth::user();
        if (!$user->isConseillerUser()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $date   = $request->get('date', today());
        $limit  = min($request->get('limit', 50), 100);
        $origin = $request->get('origin', 'all'); // received|normal|all

        $q = Queue::where('conseiller_client_id', $user->id)
                  ->where('statut_global', 'termine')
                  ->whereDate('date', $date)
                  ->with(['service:id,nom,letter_of_service']);

        if ($action === 'traiter') {
            $q->where('resolu', 1);
        } elseif ($action === 'refuser') {
            $q->where('resolu', 0);
        }

        if ($origin === 'received') {
            $q->where('transferer', 'new');
        } elseif ($origin === 'normal') {
            $q->where(function($x) {
                $x->whereNull('transferer')
                  ->orWhereIn('transferer', ['No','no','']);
            });
        }

        $tickets = $q->orderBy('heure_de_fin', 'desc')
                     ->limit($limit)
                     ->get()
                     ->map(function($t) {
                        $dureeMin = null;
                        if ($t->heure_prise_en_charge && $t->heure_de_fin) {
                            try {
                                $start = \Carbon\Carbon::createFromFormat('H:i:s', $t->heure_prise_en_charge);
                                $end   = \Carbon\Carbon::createFromFormat('H:i:s', $t->heure_de_fin);
                                $dureeMin = $start->diffInMinutes($end);
                            } catch (\Exception $e) {
                                $dureeMin = null;
                            }
                        }

                        return [
                            'id'                    => $t->id,
                            'numero_ticket'         => $t->numero_ticket,
                            'client_name'           => $t->prenom,
                            'service'               => $t->service->nom ?? 'N/A',
                            'service_name'          => $t->service->nom ?? 'N/A',
                            'telephone'             => $t->telephone,
                            'statut_traiter'        => $t->resolu === 1 ? 'traité' : 'refusé',
                            'origin'                => $t->transferer === 'new' ? 'reçu' : 'normal',
                            'date_traitement'       => $t->updated_at ? $t->updated_at->format('d/m/Y H:i') : 'N/A',
                            'duree_traitement'      => is_null($dureeMin) ? 'N/A' : ($dureeMin.'min'),
                            'duree_minutes'         => $dureeMin,
                            'commentaire_resolution'=> $t->commentaire_resolution,

                            'heure_prise_en_charge' => $t->heure_prise_en_charge,
                            'heure_de_fin'          => $t->heure_de_fin,
                            'resolu'                => $t->resolu,
                            'resolu_libelle'        => $t->resolu === 1 ? 'Résolu' : 'Non résolu',
                            'has_comment'           => !empty($t->commentaire_resolution),
                            'action_performed'      => $t->resolu === 1 ? 'traiter' : 'refuser',
                        ];
                     });

        return response()->json([
            'success'       => true,
            'action_filter' => $action,
            'origin_filter' => $origin,
            'tickets'       => $tickets,
            'count'         => $tickets->count(),
            'date'          => \Carbon\Carbon::parse($date)->format('d/m/Y'),
        ]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération de l\'historique'], 500);
    }
})->name('conseiller.resolution-history');

    
        Route::prefix('api/conseiller')->group(function () {
            Route::get('/refresh-queue', [DashboardController::class, 'refreshConseillerQueue'])
                ->name('api.conseiller.refresh-queue');
            Route::get('/next-ticket', [DashboardController::class, 'getNextTicketPreview'])
                ->name('api.conseiller.next-ticket');
            Route::get('/current-ticket', [DashboardController::class, 'getCurrentTicketStatus'])
                ->name('conseiller.current-ticket');
            Route::get('/notifications', [DashboardController::class, 'getConseillerNotifications'])
                ->name('api.conseiller.notifications');
            Route::get('/live-stats', [DashboardController::class, 'getLiveConseillerStats'])
                ->name('api.conseiller.live-stats');
            Route::get('/available-services', [DashboardController::class, 'getTransferServices'])
                ->name('api.conseiller.available-services');
            Route::get('/available-advisors', [DashboardController::class, 'getAvailableAdvisors'])
                ->name('api.conseiller.available-advisors');
            Route::get('/advisor-workload/{advisorId}', [DashboardController::class, 'getAdvisorWorkload'])
                ->name('api.conseiller.advisor-workload');
            
            Route::get('/live-resolution-stats', function(Request $request) {
                try {
                    $user = Auth::user();
                    
                    if (!$user->isConseillerUser()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Accès non autorisé'
                        ], 403);
                    }

                    $date = today();
                    
                    $todayStats = [
                        'total_traites' => Queue::where('conseiller_client_id', $user->id)
                                               ->whereDate('date', $date)
                                               ->where('statut_global', 'termine')
                                               ->count(),
                        'resolus_aujourdhui' => Queue::where('conseiller_client_id', $user->id)
                                                    ->whereDate('date', $date)
                                                    ->where('statut_global', 'termine')
                                                    ->where('resolu', 1)
                                                    ->count(),
                        'refuses_aujourdhui' => Queue::where('conseiller_client_id', $user->id)
                                                    ->whereDate('date', $date)
                                                    ->where('statut_global', 'termine')
                                                    ->where('resolu', 0)
                                                    ->count(),
                        'avec_commentaire_aujourdhui' => Queue::where('conseiller_client_id', $user->id)
                                                             ->whereDate('date', $date)
                                                             ->where('statut_global', 'termine')
                                                             ->whereNotNull('commentaire_resolution')
                                                             ->where('commentaire_resolution', '!=', '')
                                                             ->count(),
                        'ticket_en_cours' => Queue::where('conseiller_client_id', $user->id)
                                                 ->whereDate('date', $date)
                                                 ->where('statut_global', 'en_cours')
                                                 ->exists()
                    ];

                    $todayStats['taux_resolution_aujourd_hui'] = $todayStats['total_traites'] > 0 
                        ? round(($todayStats['resolus_aujourdhui'] / $todayStats['total_traites']) * 100, 2)
                        : 0;

                    $todayStats['taux_commentaire_aujourdhui'] = $todayStats['total_traites'] > 0 
                        ? round(($todayStats['avec_commentaire_aujourdhui'] / $todayStats['total_traites']) * 100, 2)
                        : 0;

                    return response()->json([
                        'success' => true,
                        'live_resolution_stats' => $todayStats,
                        'timestamp' => now()->format('H:i:s'),
                        'conseiller_info' => [
                            'username' => $user->username,
                            'status' => $todayStats['ticket_en_cours'] ? 'En cours' : 'Disponible'
                        ]
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de la récupération des statistiques temps réel'
                    ], 500);
                }
            })->name('api.conseiller.live-resolution-stats');
        });
    });

  Route::get('/api/accueil/called-clients', function (Illuminate\Http\Request $request) {
    $user = Auth::user();

    // Accueil ou Ecran peuvent voir le mur d’appels
    if (!$user->isAccueilUser() && !$user->isEcranUser()) {
        return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
    }

    $creator = $user->getCreator();
    if (!$creator) {
        return response()->json(['success' => true, 'data' => []]);
    }

    $serviceIds = \App\Models\Service::where('created_by', $creator->id)->pluck('id');

    // 1) Tous les tickets APPELÉS aujourd’hui (=> rang global fiable)
    $calledAll = \App\Models\Queue::whereIn('service_id', $serviceIds)
        ->whereDate('date', today())
        ->where(function($q){
            $q->whereNotNull('heure_prise_en_charge')
              // fallback : si certains passent en "en_cours" sans heure, on ne les perd pas
              ->orWhere('statut_global', 'en_cours');
        })
        ->orderBy('heure_prise_en_charge', 'asc')
        ->orderBy('id', 'asc')
        ->get(['id','heure_prise_en_charge','updated_at']);

    // Map id => rang (1..N) pour toute la journée
    $rankMap = $calledAll->values()->pluck('id')->flip()->map(fn($i) => $i + 1);

    // 2) Les derniers appels pour l’affichage (on en donne 12 pour la robustesse)
    $lastCalls = \App\Models\Queue::whereIn('service_id', $serviceIds)
        ->whereDate('date', today())
        ->where(function($q){
            $q->whereNotNull('heure_prise_en_charge')
              ->orWhere('statut_global', 'en_cours');
        })
        ->with(['service:id,nom,letter_of_service', 'conseillerClient:id,username'])
        ->orderBy('heure_prise_en_charge', 'desc')
        ->orderBy('id', 'desc')
        ->limit(12)
        ->get();

    $data = $lastCalls->map(function($t) use ($rankMap) {
        // heure affichée : priorité à l’heure de prise en charge, sinon fallback updated_at
        $calledAt = $t->heure_prise_en_charge
            ? \Carbon\Carbon::createFromFormat('H:i:s', $t->heure_prise_en_charge)->format('H:i')
            : ($t->updated_at ? $t->updated_at->format('H:i') : '--:--');

        return [
            'id'            => $t->id,
            'ticket_number' => $t->numero_ticket,
            'telephone'     => $t->telephone ?? '—',              // ✅ numéro renvoyé
            'called_at'     => $calledAt,
            'advisor_name'  => optional($t->conseillerClient)->username ?? '—',
            'service_name'  => optional($t->service)->nom ?? 'N/A',
            'statut_global' => $t->statut_global,
            'rang'          => $rankMap[$t->id] ?? null,          // ✅ rang du jour
        ];
    });

    return response()->json([
        'success' => true,
        'data'    => $data,
        'count'   => $data->count(),
        'date'    => now()->format('Y-m-d'),
    ]);
})->name('api.accueil.called-clients');

    /*
    |--------------------------------------------------------------------------
    | GESTION DE FILE D'ATTENTE CHRONOLOGIQUE FIFO
    |--------------------------------------------------------------------------
    */
    Route::post('/ecran/generate-ticket', [DashboardController::class, 'generateTicket'])
        ->name('ecran.generate-ticket');

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
            $limit = min($request->get('limit', 50), 100);

            $tickets = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                       ->whereDate('date', $date)
                                       ->with('service')
                                       ->orderBy('created_at', 'desc')
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
            
            $chronologicalQueue = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                  ->whereDate('date', $date)
                                                  ->where('statut_global', 'en_attente')
                                                  ->orderBy('created_at', 'asc')
                                                  ->with('service')
                                                  ->get()
                                                  ->map(function($ticket, $index) {
                                                      $ticketArray = $ticket->toTicketArray();
                                                      $ticketArray['rang_chronologique'] = $index + 1;
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
        Route::get('/layouts/app', [DashboardController::class, 'adminDashboard'])
            ->name('layouts.app');

        /*
        |--------------------------------------------------------------------------
        | ROUTES PARAMÈTRES GÉNÉRAUX
        |--------------------------------------------------------------------------
        */
        Route::prefix('layouts/setting')->group(function () {
            Route::get('/general', [SettingsController::class, 'index'])
                ->name('layouts.setting');
            Route::put('/general', [SettingsController::class, 'update'])
                ->name('layouts.setting.update');
            Route::post('/reset', [SettingsController::class, 'reset'])
                ->name('layouts.setting.reset');
            Route::post('/clear-cache', [SettingsController::class, 'clearCache'])
                ->name('layouts.setting.clear-cache');
            Route::get('/api/group/{group}', [SettingsController::class, 'getGroupSettings'])
                ->name('layouts.setting.api.group');
            Route::post('/api/update', [SettingsController::class, 'updateSetting'])
                ->name('layouts.setting.api.update');
            Route::get('/api/stats', [SettingsController::class, 'getStats'])
                ->name('layouts.setting.api.stats');
        });

        Route::get('/settings', function() {
            return redirect()->route('layouts.setting');
        });  

        /*
        |--------------------------------------------------------------------------
        | GESTION DES AGENCES
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin')->group(function () {
            Route::get('/agencies', [AgencyController::class, 'index'])->name('agency.agence');
            Route::get('/agencies/create', [AgencyController::class, 'create'])->name('agency.agence-create');
            Route::post('/agencies', [AgencyController::class, 'store'])->name('agencies.store');
            Route::get('/agencies/{agency}', [AgencyController::class, 'show'])->name('agencies.show');
            Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agencies.edit');
            Route::put('/agencies/{agency}', [AgencyController::class, 'update'])->name('agencies.update');
            Route::delete('/agencies/{agency}', [AgencyController::class, 'destroy'])->name('agencies.destroy');
            Route::post('/agencies/{agency}/activate', [AgencyController::class, 'activate'])->name('agencies.activate');
            Route::post('/agencies/{agency}/deactivate', [AgencyController::class, 'deactivate'])->name('agencies.deactivate');
            Route::get('/agencies/{agency}/details', [AgencyController::class, 'details'])->name('agencies.details');
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
            Route::get('/services', [ServiceController::class, 'index'])->name('service.service-list');
            Route::get('/services/create', [ServiceController::class, 'create'])->name('service.service-create');
            Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
            Route::post('/services/{service}/activate', [ServiceController::class, 'activate'])->name('services.activate');
            Route::post('/services/{service}/deactivate', [ServiceController::class, 'deactivate'])->name('services.deactivate');
            Route::get('/services/{service}/details', [ServiceController::class, 'details'])->name('services.details');
            Route::post('/services/bulk-activate', [ServiceController::class, 'bulkActivate'])->name('services.bulk-activate');
            Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
            Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
            Route::get('/api/services/stats', [ServiceController::class, 'getStats'])->name('services.api.stats');
            Route::get('/api/services/search', [ServiceController::class, 'searchServices'])->name('services.api.search');
        });
         
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::get('/services/{service}/stats', [ServiceController::class, 'getServiceStats'])->name('services.stats');
        Route::post('/services/check-letter-availability', [ServiceController::class, 'checkLetterAvailability'])
            ->name('services.check-letter-availability');

        /*
        |--------------------------------------------------------------------------
        | GESTION DES FILES D'ATTENTE CHRONOLOGIQUE (ADMIN)
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin/queue')->group(function () {
            Route::get('/dashboard', function() {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $todayStats = [
                    'total_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->count(),
                    'waiting_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'en_attente')->count(),
                    'processing_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'en_cours')->count(),
                    'completed_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('statut_global', 'termine')->count(),
                    'average_wait_time' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->avg('temps_attente_estime') ?? 0,
                    'resolved_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('resolu', 1)->count(),
                    'unresolved_tickets' => \App\Models\Queue::whereIn('service_id', $serviceIds)->whereDate('date', today())->where('resolu', 0)->count(),
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

                $chronologicalQueue = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                      ->whereDate('date', today())
                                                      ->where('statut_global', 'en_attente')
                                                      ->orderBy('created_at', 'asc')
                                                      ->with('service')
                                                      ->limit(20)
                                                      ->get();

                return view('admin.queue.dashboard', compact('todayStats', 'services', 'chronologicalQueue'));
            })->name('admin.queue.dashboard');

            Route::get('/tickets', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $query = \App\Models\Queue::whereIn('service_id', $serviceIds)->with('service');
                
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
                
                if ($request->filled('resolu')) {
                    if ($request->resolu === 'resolved') {
                        $query->where('resolu', 1);
                    } elseif ($request->resolu === 'unresolved') {
                        $query->where('resolu', 0);
                    }
                }
                
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('numero_ticket', 'LIKE', "%{$search}%")
                          ->orWhere('prenom', 'LIKE', "%{$search}%")
                          ->orWhere('telephone', 'LIKE', "%{$search}%");
                    });
                }
                
                $sortBy = $request->get('sort', 'created_at');
                $sortOrder = $request->get('order', 'asc');
                $query->orderBy($sortBy, $sortOrder);
                
                $tickets = $query->paginate(20);
                $services = \App\Models\Service::where('created_by', $admin->id)->get();
                
                return view('admin.queue.tickets', compact('tickets', 'services'));
            })->name('admin.queue.tickets');

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
                    'period_resolved' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                         ->whereBetween('date', $dateRange)
                                                         ->where('resolu', 1)
                                                         ->count(),
                    'period_unresolved' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                           ->whereBetween('date', $dateRange)
                                                           ->where('resolu', 0)
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
                    'chronological_resolution_stats' => [
                        'queue_type' => 'fifo_chronological_with_resolution',
                        'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                        'configured_wait_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                        'resolution_rate' => function() use ($serviceIds, $dateRange) {
                            $total = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                     ->whereBetween('date', $dateRange)
                                                     ->where('statut_global', 'termine')
                                                     ->count();
                            if ($total === 0) return 0;
                            
                            $resolved = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                        ->whereBetween('date', $dateRange)
                                                        ->where('statut_global', 'termine')
                                                        ->where('resolu', 1)
                                                        ->count();
                            return round(($resolved / $total) * 100, 2);
                        },
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
                
                $stats['chronological_resolution_stats']['resolution_rate'] = $stats['chronological_resolution_stats']['resolution_rate']();
                
                return view('admin.queue.stats', compact('stats', 'period'));
            })->name('admin.queue.stats');

            Route::get('/export', function(Request $request) {
                $admin = auth()->user();
                $serviceIds = \App\Models\Service::where('created_by', $admin->id)->pluck('id');
                
                $date = $request->get('date', today());
                
                $tickets = \App\Models\Queue::whereIn('service_id', $serviceIds)
                                          ->whereDate('date', $date)
                                          ->with('service')
                                          ->orderBy('created_at', 'asc')
                                          ->get();
                
                $filename = 'tickets_chronological_resolution_' . \Carbon\Carbon::parse($date)->format('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($tickets) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
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
                        'Résolution',
                        'Commentaire Résolution',
                        'Conseiller',
                        'Commentaire Initial'
                    ], ';');
                    
                    foreach ($tickets as $index => $ticket) {
                        fputcsv($file, [
                            $index + 1,
                            $ticket->numero_ticket,
                            $ticket->service ? $ticket->service->nom : 'N/A',
                            $ticket->prenom,
                            $ticket->telephone,
                            $ticket->date->format('d/m/Y'),
                            $ticket->heure_d_enregistrement,
                            $ticket->position_file,
                            $ticket->temps_attente_estime . ' min',
                            $ticket->getStatutLibelle(),
                            $ticket->getResoluLibelle(),
                            $ticket->commentaire_resolution ?: '',
                            $ticket->conseillerClient ? $ticket->conseillerClient->username : 'N/A',
                            $ticket->commentaire ?: ''
                        ], ';');
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            })->name('admin.queue.export');

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
                                                              'temps_attente_estime' => $ticket->temps_attente_estime,
                                                              'resolu' => $ticket->resolu,
                                                              'resolu_libelle' => $ticket->getResoluLibelle()
                                                          ];
                                                      });

                return response()->json([
                    'success' => true,
                    'global_stats' => $globalStats,
                    'chronological_order' => $chronologicalOrder,
                    'queue_info' => [
                        'type' => 'fifo_chronological_with_resolution',
                        'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                        'note' => 'Ordre de traitement basé sur l\'heure d\'arrivée avec gestion de résolution'
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
        Route::get('/user/users-list', [DashboardController::class, 'usersList'])
            ->name('user.users-list');
        Route::get('/admin/user/create', [UserManagementController::class, 'create'])
            ->name('User.user-create');
        Route::post('/admin/user/store', [UserManagementController::class, 'store'])
            ->name('User.user.store');
        Route::get('/admin/users/my-created', [UserManagementController::class, 'myCreatedUsers'])
            ->name('User.user.my-created');
        Route::get('/admin/user/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('User.user-edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
            ->name('User.user.update');
        Route::post('/admin/users/{user}/resend-credentials', [UserManagementController::class, 'resendCredentials'])
            ->name('admin.users.resend-credentials');
        Route::post('/admin/users/{user}/change-type', [UserManagementController::class, 'changeUserType'])
            ->name('admin.users.change-type');
           
        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR LES UTILISATEURS
        |--------------------------------------------------------------------------
        */
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend');
        Route::patch('/admin/users/{user}/reactivate', [DashboardController::class, 'reactivateUser'])
            ->name('admin.users.reactivate');
        Route::post('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate.post');
        Route::post('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend.post');
        Route::post('/admin/users/{user}/reactivate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.reactivate.post');
        Route::delete('/admin/users/{user}', [DashboardController::class, 'deleteUser'])
            ->name('admin.users.delete');
        Route::post('/admin/users/{user}/reset-password', [DashboardController::class, 'resetUserPassword'])
            ->name('admin.users.reset-password');
        Route::post('/admin/users/bulk-activate', [DashboardController::class, 'bulkActivate'])
            ->name('admin.users.bulk-activate');
        Route::post('/admin/users/bulk-delete', [DashboardController::class, 'bulkDeleteUsers'])
            ->name('admin.users.bulk-delete');
        Route::get('/admin/users/export', [DashboardController::class, 'exportUsers'])
            ->name('admin.users.export');
        
        /*
        |--------------------------------------------------------------------------
        | API AJAX POUR ADMINS
        |--------------------------------------------------------------------------
        */
        Route::get('/admin/api/stats', [DashboardController::class, 'getStats'])
            ->name('admin.api.stats');
        Route::get('/admin/api/advanced-stats', [DashboardController::class, 'getAdvancedStats'])
            ->name('admin.api.advanced-stats');
        Route::get('/admin/api/search-users', [DashboardController::class, 'searchUsers'])
            ->name('admin.api.search-users');
        Route::get('/admin/api/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.api.user-details');
        Route::get('/admin/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.users.details');
        Route::get('/admin/api/my-stats', [UserManagementController::class, 'getMyUserStats'])
            ->name('admin.api.my-stats');
        Route::get('/admin/api/available-roles', [UserManagementController::class, 'getAvailableRolesApi'])
            ->name('admin.api.available-roles');
    });
});

/*
|--------------------------------------------------------------------------
| API POUR LA VÉRIFICATION DES SESSIONS EN TEMPS RÉEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/api/session/check-closure', [LoginController::class, 'checkSessionClosure'])
        ->name('api.session.check-closure');

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
| ROUTES API POUR LES PARAMÈTRES (AJAX)
|--------------------------------------------------------------------------
*/
Route::prefix('api/settings')->group(function () {
    Route::get('/public', function() {
        return response()->json([
            'app_name' => Setting::get('app_name', 'Attendis'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'auto_session_closure' => Setting::isAutoSessionClosureEnabled(),
            'closure_time' => Setting::getSessionClosureTime(),
            'queue_type' => 'fifo_chronological_with_resolution',
            'queue_principle' => 'Premier arrivé, premier servi avec résolution binaire',
            'default_wait_time' => Setting::getDefaultWaitingTimeMinutes()
        ]);
    });
    
    Route::get('/check/{key}', function($key) {
        $allowedKeys = [
            'auto_detect_available_advisors',
            'auto_assign_all_services_to_advisors', 
            'enable_auto_session_closure',
            'maintenance_mode',
            'default_waiting_time_minutes'
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

    Route::get('/queue-settings', function() {
        return response()->json([
            'queue_type' => 'fifo_chronological_with_resolution',
            'principle' => 'Premier arrivé, premier servi avec résolution binaire',
            'configured_wait_time' => Setting::getDefaultWaitingTimeMinutes(),
            'admin_can_configure' => true,
            'resolution_format' => 'tinyint (0=non résolu, 1=résolu)',
            'comment_required_for_refusal' => true,
            'description' => 'Les tickets sont traités dans l\'ordre chronologique d\'arrivée avec gestion de résolution et commentaire obligatoire pour les refus'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| ROUTES API UTILITAIRES POUR LES INTERFACES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/api/dashboard/refresh', function(Request $request) {
        $user = Auth::user();
        
        if ($user->isEcranUser()) {
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
                    'queue_info' => [
                        'type' => 'fifo_chronological_with_resolution',
                        'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                        'next_position' => \App\Models\Queue::calculateQueuePosition(),
                        'configured_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes()
                    ]
                ]
            ]);
            
        } elseif ($user->isConseillerUser()) {
            return response()->json([
                'success' => true,
                'type' => 'conseiller',
                'data' => [
                    'user_type' => $user->getTypeName(),
                    'days_active' => $user->created_at->diffInDays(now()),
                    'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                    'last_update' => now()->format('H:i:s'),
                    'queue_info' => [
                        'type' => 'fifo_chronological_with_resolution',
                        'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                        'role' => 'Traitement des tickets dans l\'ordre chronologique avec gestion de résolution'
                    ]
                ]
            ]);
        } else {
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

    Route::get('/api/user/tips/{type?}', function($type = null) {
        $user = Auth::user();
        $userType = $type ?: $user->getUserRole();
        
        $tips = [
            'ecran' => [
                'Vérifiez régulièrement les nouveaux services',
                'Utilisez la recherche pour trouver rapidement un service',
                'L\'interface se met à jour automatiquement toutes les 5 minutes',
                'Les tickets sont traités par ordre d\'arrivée (FIFO)',
                'Le temps d\'attente est configuré par votre administrateur'
            ],
            'accueil' => [
                'Accueillez chaleureusement tous les visiteurs',
                'Orientez les visiteurs vers les bons services',
                'Tenez à jour les informations d\'accueil'
            ],
            'conseiller' => [
                'Traitez les tickets dans l\'ordre chronologique (FIFO)',
                'Utilisez "Appeler suivant" pour le prochain ticket',
                'Choisissez "Traiter" ou "Refuser" avec commentaire si nécessaire',
                'Le commentaire est obligatoire pour les refus',
                'Activez la pause si vous devez vous absenter',
                'Consultez vos statistiques de résolution pour améliorer vos performances'
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
| ROUTES DE COMPATIBILITÉ
|--------------------------------------------------------------------------
*/
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
| GESTION DES ERREURS
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        try {
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

    Route::get('/dev/test-ticket-generation-fifo-resolution', function () {
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
            $ticketData = [
                'service_id' => $service->id,
                'prenom' => 'Test Client FIFO Resolution',
                'telephone' => '0123456789',
                'commentaire' => 'Test de génération automatique - File chronologique FIFO avec résolution binaire'
            ];
            
            $ticket = \App\Models\Queue::createTicket($ticketData);
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket de test généré avec succès - File chronologique FIFO avec résolution',
                'ticket' => $ticket->toTicketArray(),
                'service' => [
                    'id' => $service->id,
                    'nom' => $service->nom,
                    'letter_of_service' => $service->letter_of_service
                ],
                'queue_stats' => \App\Models\Queue::getServiceStats($service->id),
                'queue_info' => [
                    'type' => 'fifo_chronological_with_resolution',
                    'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                    'next_position' => \App\Models\Queue::calculateQueuePosition(),
                    'configured_time' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                    'resolution_format' => 'tinyint (0=non résolu, 1=résolu)',
                    'comment_required_for_refusal' => true
                ],
                'chronological_queue' => \App\Models\Queue::getChronologicalQueue(),
                'global_stats' => \App\Models\Queue::getGlobalQueueStats()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur génération ticket FIFO avec résolution: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');

    Route::get('/dev/test-conseiller-interface-resolution', function () {
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
                    'tickets_resolus' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                         ->whereDate('date', today())
                                                         ->where('resolu', 1)
                                                         ->count(),
                    'tickets_non_resolus' => \App\Models\Queue::whereIn('service_id', $myServiceIds)
                                                             ->whereDate('date', today())
                                                             ->where('resolu', 0)
                                                             ->count(),
                ],
                'conseiller_stats' => [
                    'tickets_traites_aujourd_hui' => \App\Models\Queue::where('conseiller_client_id', $user->id)
                                                                      ->whereDate('date', today())
                                                                      ->where('statut_global', 'termine')
                                                                      ->count(),
                    'tickets_resolus_aujourdhui' => \App\Models\Queue::where('conseiller_client_id', $user->id)
                                                                     ->whereDate('date', today())
                                                                     ->where('statut_global', 'termine')
                                                                     ->where('resolu', 1)
                                                                     ->count(),
                    'tickets_refuses_aujourdhui' => \App\Models\Queue::where('conseiller_client_id', $user->id)
                                                                     ->whereDate('date', today())
                                                                     ->where('statut_global', 'termine')
                                                                     ->where('resolu', 0)
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
                    'type' => 'fifo_chronological_with_resolution',
                    'principle' => 'Premier arrivé, premier servi avec résolution binaire',
                    'interface_status' => 'ready',
                    'resolution_format' => 'tinyint (0=non résolu, 1=résolu)'
                ]
            ];
            
            $total = $interfaceData['conseiller_stats']['tickets_traites_aujourd_hui'];
            $resolus = $interfaceData['conseiller_stats']['tickets_resolus_aujourdhui'];
            $tauxResolution = $total > 0 ? round(($resolus / $total) * 100, 2) : 0;
            
            return response()->json([
                'success' => true,
                'message' => 'Interface conseiller avec résolution testée avec succès',
                'conseiller_info' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'creator' => $creator->username,
                    'taux_resolution' => $tauxResolution
                ],
                'interface_data' => $interfaceData,
                'routes_available' => [
                    'conseiller.tickets' => route('conseiller.tickets'),
                    'conseiller.call-ticket' => route('conseiller.call-ticket'),
                    'conseiller.complete-ticket' => route('conseiller.complete-ticket'),
                    'conseiller.my-stats' => route('conseiller.my-stats'),
                    'conseiller.history' => route('conseiller.history'),
                    'conseiller.validate-resolution-comment' => route('conseiller.validate-resolution-comment'),
                    'conseiller.resolution-stats' => route('conseiller.resolution-stats')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur test interface conseiller avec résolution: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');

    Route::get('/dev/test-ticket-resolution', function () {
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
            
            $resolutionStats = [
                'total_tickets_today' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                         ->whereDate('date', today())
                                                         ->count(),
                'tickets_resolus' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                      ->whereDate('date', today())
                                                      ->where('resolu', 1)
                                                      ->count(),
                'tickets_non_resolus' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                          ->whereDate('date', today())
                                                          ->where('resolu', 0)
                                                          ->count(),
                'tickets_avec_commentaires' => \App\Models\Queue::whereIn('service_id', $serviceIds)
                                                               ->whereDate('date', today())
                                                               ->whereNotNull('commentaire_resolution')
                                                               ->where('commentaire_resolution', '!=', '')
                                                               ->count(),
            ];
            
            $total = $resolutionStats['total_tickets_today'];
            $resolutionStats['taux_resolution'] = $total > 0 ? round(($resolutionStats['tickets_resolus'] / $total) * 100, 2) : 0;
            $resolutionStats['taux_commentaires'] = $total > 0 ? round(($resolutionStats['tickets_avec_commentaires'] / $total) * 100, 2) : 0;
            
            return response()->json([
                'success' => true,
                'resolution_format' => 'tinyint (0=non résolu, 1=résolu)',
                'comment_policy' => 'Commentaire obligatoire pour les refus',
                'resolution_stats' => $resolutionStats,
                'validation_rules' => [
                    'action_required' => true,
                    'comment_required_for_refusal' => true,
                    'comment_max_length' => 500
                ],
                'queue_info' => [
                    'type' => 'fifo_chronological_with_resolution',
                    'principle' => 'Premier arrivé, premier servi avec résolution binaire'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur test résolution tickets: ' . $e->getMessage()
            ]);
        }
    })->middleware('auth');
}