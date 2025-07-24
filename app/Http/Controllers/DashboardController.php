<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Models\AdministratorUser;
use App\Models\Agency;
use App\Models\Service;
use App\Models\Queue;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.user.status');
    }

    // ===============================================
    // DASHBOARDS PRINCIPAUX: app(admin) et app-users(utilisateur quelconque)
    // ===============================================

    /**
     * ✅ Dashboard principal - CORRIGÉ POUR ÉVITER LES BOUCLES
     * Logique directe sans redirections multiples
     */
    public function index()
    {
        $user = Auth::user();
        
        // Vérifier le statut de l'utilisateur
        if ($user->isInactive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte n\'est pas encore activé. Contactez un administrateur.');
        }

        if ($user->isSuspended()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été suspendu. Contactez un administrateur.');
        }

        // 🎯 SOLUTION : APPELER DIRECTEMENT LES MÉTHODES AU LIEU DE REDIRIGER
        try {
            if ($user->isAdmin()) {
                // Appeler directement adminDashboard au lieu de rediriger
                return $this->adminDashboard();
            } elseif ($user->isConseillerUser()) {
                // Appeler directement conseillerDashboard au lieu de rediriger
                return $this->conseillerDashboard();
            } else {
                // Appeler directement userDashboard au lieu de rediriger
                return $this->userDashboard();
            }
        } catch (\Exception $e) {
            Log::error('Dashboard error for user ' . $user->id, [
                'error' => $e->getMessage(),
                'user_type' => $user->getUserRole(),
                'trace' => $e->getTraceAsString()
            ]);

            // En cas d'erreur, redirection sécurisée vers login
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Erreur lors du chargement de votre espace. Veuillez vous reconnecter.');
        }
    }

    /**
     * ✅ Dashboard admin avec statistiques ISOLÉES
     * Chaque admin ne voit que SES statistiques d'utilisateurs créés
     */
    public function adminDashboard()
    {
        // Vérifier que l'utilisateur est bien admin
        if (!Auth::user()->isAdmin()) {
            return view('layouts.app-users')
                ->with('error', 'Accès non autorisé à la zone administrateur.');
        }

        try {
            $currentAdminId = Auth::id();
            
            // 🔒 ISOLATION CORRECTE - Récupérer UNIQUEMENT les utilisateurs créés par cet admin
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId; // Inclure l'admin lui-même
            
            // Statistiques ISOLÉES pour cet admin uniquement
            $stats = [
                'total_users' => count($myUserIds), // Ses utilisateurs + lui-même
                'active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count(),
                'inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'admin_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                'ecran_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'accueil_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                'conseiller_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                'recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfMonth())->count(),
                
                // 🔒 Mes agences et services
                'my_agencies' => Agency::where('created_by', $currentAdminId)->count(),
                'my_active_agencies' => Agency::where('created_by', $currentAdminId)->where('status', 'active')->count(),
                'my_services' => Service::where('created_by', $currentAdminId)->count(),
                'my_active_services' => Service::where('created_by', $currentAdminId)->where('statut', 'actif')->count(),
                
                // 🆕 NOUVEAU : Statistiques des tickets avec file chronologique
                'my_tickets_today' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                          ->whereDate('date', today())
                                          ->count(),
                'my_tickets_waiting' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                            ->whereDate('date', today())
                                            ->where('statut_global', 'en_attente')
                                            ->count(),
                'my_tickets_processing' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                                ->whereDate('date', today())
                                                ->where('statut_global', 'en_cours')
                                                ->count(),
                'my_tickets_completed' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                               ->whereDate('date', today())
                                               ->where('statut_global', 'termine')
                                               ->count(),
            ];

            // Statistiques personnelles pour l'admin connecté (SES créations)
            $personalStats = [
                'users_created_by_me' => AdministratorUser::where('administrator_id', $currentAdminId)->count(),
                'active_users_created_by_me' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1, // -1 pour l'admin
                'users_created_by_me_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_by_me_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'agencies_created_by_me' => Agency::where('created_by', $currentAdminId)->count(),
                'services_created_by_me' => Service::where('created_by', $currentAdminId)->count(),
                
                // 🆕 NOUVEAU : Statistiques tickets personnelles
                'tickets_generated_today' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                                  ->whereDate('date', today())
                                                  ->count(),
                'average_wait_time_today' => Queue::whereIn('service_id', Service::where('created_by', $currentAdminId)->pluck('id'))
                                                  ->whereDate('date', today())
                                                  ->avg('temps_attente_estime') ?? 0,
            ];

            // Activité récente ISOLÉE (SES utilisateurs seulement)
            $recentActivity = User::with(['userType', 'status', 'createdBy.administrator'])
                ->whereIn('id', $myUserIds)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Utilisateurs en attente d'activation ISOLÉS (SES utilisateurs seulement)
            $pendingUsers = User::whereIn('id', $myUserIds)
                ->where('status_id', 1)
                ->with(['userType', 'createdBy.administrator'])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();

            // 🆕 NOUVEAU : Activité récente des tickets
            $myServiceIds = Service::where('created_by', $currentAdminId)->pluck('id');
            $recentTickets = Queue::whereIn('service_id', $myServiceIds)
                                 ->whereDate('date', today())
                                 ->with('service')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(10)
                                 ->get();

            return view('layouts.app', compact(
                'stats', 
                'personalStats', 
                'recentActivity', 
                'pendingUsers',
                'recentTickets'
            ));

        } catch (\Exception $e) {
            Log::error('Admin dashboard error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('layouts.app')->with('error', 'Erreur lors du chargement du dashboard administrateur.');
        }
    }

    /**
     * ✅ Dashboard utilisateur avec différenciation selon le type - CORRIGÉ
     * - POSTE ECRAN → Interface sans sidebar + grille services
     * - CONSEILLER → Interface conseiller (APPEL DIRECT au lieu de redirection)
     * - ACCUEIL → Interface actuelle adaptée
     */
    public function userDashboard()
    {
        $user = Auth::user();

        // Si c'est un admin, utiliser adminDashboard
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        try {
            // 🎯 DIFFÉRENCIATION SELON LE TYPE D'UTILISATEUR
            if ($user->isEcranUser()) {
                return $this->ecranDashboard($user);
            } 
            elseif ($user->isConseillerUser()) {
                // ✅ APPEL DIRECT au lieu de redirection pour éviter les boucles
                return $this->conseillerDashboard();
            } 
            else {
                return $this->normalUserDashboard($user); // Pour les utilisateurs ACCUEIL
            }

        } catch (\Exception $e) {
            Log::error('User dashboard error', [
                'user_id' => Auth::id(),
                'user_type' => $user->getUserRole(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('layouts.app-users', [
                'error' => 'Erreur lors du chargement de votre espace.',
                'userInfo' => $this->getUserInfo($user)
            ]);
        }
    }

    /**
     * 🆕 Dashboard pour utilisateurs POSTE ECRAN
     * Interface sans sidebar + grille de services créés par l'admin
     */
    private function ecranDashboard(User $user)
    {
        try {
            // Récupérer l'admin créateur de cet utilisateur
            $creator = $user->getCreator();
            
            if (!$creator) {
                Log::warning("Utilisateur écran sans créateur détecté", [
                    'user_id' => $user->id,
                    'username' => $user->username
                ]);
                
                return view('layouts.app-ecran', [
                    'services' => collect(),
                    'userInfo' => $this->getUserInfo($user),
                    'noCreator' => true
                ]);
            }

            // 🎯 RÉCUPÉRER SEULEMENT LES SERVICES ACTIFS
            $services = $creator->createdServices()
                              ->where('statut', 'actif')  // Filtrage automatique
                              ->orderBy('created_at', 'desc')
                              ->get();

            // ✅ ENRICHIR CHAQUE SERVICE AVEC SES STATISTIQUES
            $services = $services->map(function($service) {
                $service->queue_stats = Queue::getServiceStats($service->id);
                return $service;
            });

            // Statistiques des services pour l'interface écran
            $serviceStats = [
                'total_services' => $services->count(),
                'active_services' => $services->where('statut', 'actif')->count(),
                'inactive_services' => 0, // Plus de services inactifs affichés
                'recent_services' => $services->where('created_at', '>=', now()->subDays(7))->count(),
                
                // ✅ NOUVEAU : Statistiques des tickets avec file chronologique
                'total_tickets_today' => Queue::whereIn('service_id', $services->pluck('id'))
                                              ->whereDate('date', today())
                                              ->count(),
                'tickets_en_attente' => Queue::whereIn('service_id', $services->pluck('id'))
                                             ->whereDate('date', today())
                                             ->where('statut_global', 'en_attente')
                                             ->count(),
                'tickets_en_cours' => Queue::whereIn('service_id', $services->pluck('id'))
                                           ->whereDate('date', today())
                                           ->where('statut_global', 'en_cours')
                                           ->count(),
                'tickets_termines' => Queue::whereIn('service_id', $services->pluck('id'))
                                           ->whereDate('date', today())
                                           ->where('statut_global', 'termine')
                                           ->count(),
                
                // 🆕 NOUVEAU : Informations sur la file avec numérotation par service
                'queue_info' => [
                    'type' => 'service_numbering_chronological',
                    'principe' => 'Numérotation par service, traitement chronologique',
                    'prochaine_position' => Queue::calculateQueuePosition(),
                    'temps_attente_configure' => Setting::getDefaultWaitingTimeMinutes(),
                ]
            ];

            Log::info("Interface écran chargée avec file avec numérotation par service", [
                'user_id' => $user->id,
                'creator_id' => $creator->id,
                'services_count' => $services->count(),
                'tickets_today' => $serviceStats['total_tickets_today'],
                'only_active_services' => true,
                'queue_type' => 'service_numbering_chronological'
            ]);

            return view('layouts.app-ecran', [
                'services' => $services,
                'serviceStats' => $serviceStats,
                'userInfo' => $this->getUserInfo($user),
                'creatorInfo' => [
                    'username' => $creator->username,
                    'company' => $creator->company,
                    'email' => $creator->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dashboard écran', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('layouts.app-ecran', [
                'services' => collect(),
                'userInfo' => $this->getUserInfo($user),
                'error' => 'Erreur lors du chargement des services'
            ]);
        }
    }

    /**
     * 🆕 Dashboard pour utilisateurs ACCUEIL uniquement - MODIFIÉ
     * (Les conseillers ont maintenant leur propre interface)
     */
    private function normalUserDashboard(User $user)
    {
        // Vérifier que c'est bien un utilisateur ACCUEIL
        if (!$user->isAccueilUser()) {
            // ✅ APPEL DIRECT au lieu de redirection
            return $this->conseillerDashboard();
        }

        try {
            // Statistiques personnelles pour l'utilisateur ACCUEIL
            $userStats = [
                'days_since_creation' => $user->created_at->diffInDays(now()),
                'account_age_formatted' => $user->created_at->diffForHumans(),
                'is_recently_created' => $user->created_at->diffInDays(now()) < 7,
                'creator_info' => $user->getCreationInfo(),
                'login_count_today' => 1,
                'last_password_change' => $user->updated_at->diffForHumans(),
            ];

            // Données spécifiques ACCUEIL
            $typeSpecificData = [
                'type_description' => 'Poste Accueil - Réception et orientation des visiteurs',
                'type_features' => [
                    'Accueil des visiteurs',
                    'Orientation et information',
                    'Gestion des rendez-vous',
                    'Communication interne'
                ],
                'type_recommendations' => [
                    'Vérifiez régulièrement les nouveaux visiteurs',
                    'Tenez à jour les informations d\'orientation',
                    'Communiquez avec l\'équipe de gestion'
                ],
                'queue_info' => [
                    'note' => 'Les tickets sont gérés par l\'équipe de conseillers',
                    'your_role' => 'Accueil et orientation des clients',
                    'ticket_flow' => 'Ecrans → File FIFO → Conseillers'
                ]
            ];

            return view('layouts.app-users', [
                'userStats' => $userStats,
                'typeSpecificData' => $typeSpecificData,
                'userInfo' => $this->getUserInfo($user)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dashboard accueil', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return view('layouts.app-users', [
                'userInfo' => $this->getUserInfo($user),
                'error' => 'Erreur lors du chargement de votre espace accueil'
            ]);
        }
    }

    // ===============================================
    // 🆕 SECTION CONSEILLER - INTERFACE DÉDIÉE AMÉLIORÉE
    // ===============================================

    /**
     * 👨‍💼 DASHBOARD PRINCIPAL CONSEILLER - AMÉLIORÉ
     * Interface dédiée avec file d'attente FIFO et temps admin
     */
    public function conseillerDashboard()
    {
        $user = Auth::user();

        // Vérifier que c'est bien un conseiller
        if (!$user->isConseillerUser()) {
            // ✅ RETOUR APPROPRIÉ selon le type au lieu de redirection
            if ($user->isAdmin()) {
                return $this->adminDashboard();
            } else {
                return view('layouts.app-users', [
                    'userInfo' => $this->getUserInfo($user),
                    'error' => 'Interface réservée aux conseillers.'
                ]);
            }
        }

        try {
            // 🎯 RÉCUPÉRER L'ADMIN CRÉATEUR
            $creator = $user->getCreator();
            
            if (!$creator) {
                return view('layouts.app-conseiller', [
                    'error' => 'Configuration manquante : administrateur créateur introuvable',
                    'userInfo' => $this->getUserInfo($user),
                    'defaultWaitTime' => Setting::getDefaultWaitingTimeMinutes()
                ]);
            }

            // 🎫 STATISTIQUES DE LA FILE D'ATTENTE (services de son admin)
            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            // ✅ COMPTEURS CORRIGÉS avec logique claire
            $fileStats = [
                'tickets_en_attente' => Queue::whereIn('service_id', $myServiceIds)
                                            ->whereDate('date', today())
                                            ->where('statut_global', 'en_attente') // Pas encore appelés
                                            ->count(),
                                            
                'tickets_en_cours' => Queue::whereIn('service_id', $myServiceIds)
                                          ->whereDate('date', today())
                                          ->where('statut_global', 'en_cours') // Appelés mais pas encore traités
                                          ->count(),
                                          
                'tickets_termines' => Queue::whereIn('service_id', $myServiceIds)
                                          ->whereDate('date', today())
                                          ->where('statut_global', 'termine') // Traités
                                          ->count(),
                                          
                'temps_attente_moyen' => Setting::getDefaultWaitingTimeMinutes(), // ✅ Temps par défaut admin
            ];

            // 👨‍💼 STATISTIQUES PERSONNELLES DU CONSEILLER
            $conseillerStats = [
                'tickets_traites_aujourd_hui' => Queue::where('conseiller_client_id', $user->id)
                                                      ->whereDate('date', today())
                                                      ->where('statut_global', 'termine')
                                                      ->count(),
                                                      
                'temps_moyen_traitement' => Queue::where('conseiller_client_id', $user->id)
                                                 ->whereDate('date', today())
                                                 ->where('statut_global', 'termine')
                                                 ->whereNotNull('heure_de_fin')
                                                 ->whereNotNull('heure_prise_en_charge')
                                                 ->selectRaw('AVG(TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60) as avg_minutes')
                                                 ->value('avg_minutes') ?? 0,
                                                 
                'ticket_en_cours' => Queue::where('conseiller_client_id', $user->id)
                                          ->whereDate('date', today())
                                          ->where('statut_global', 'en_cours')
                                          ->first(),
                                          
                'premier_ticket_du_jour' => Queue::where('conseiller_client_id', $user->id)
                                                 ->whereDate('date', today())
                                                 ->orderBy('heure_prise_en_charge', 'asc')
                                                 ->first(),
                                                 
                'is_en_pause' => false, // TODO: Implémenter la logique de pause
            ];

            // ✅ TEMPS CONFIGURÉ PAR L'ADMIN
            $defaultWaitTime = Setting::getDefaultWaitingTimeMinutes();

            Log::info("Interface conseiller chargée", [
                'conseiller_id' => $user->id,
                'creator_id' => $creator->id,
                'tickets_en_attente' => $fileStats['tickets_en_attente'],
                'conseiller_tickets_traites' => $conseillerStats['tickets_traites_aujourd_hui'],
                'default_wait_time' => $defaultWaitTime
            ]);

            return view('layouts.app-conseiller', [
                'fileStats' => $fileStats,
                'conseillerStats' => $conseillerStats,
                'userInfo' => $this->getUserInfo($user),
                'defaultWaitTime' => $defaultWaitTime, // ✅ Passé à la vue
                'creatorInfo' => [
                    'username' => $creator->username,
                    'company' => $creator->company,
                    'services_count' => $creator->createdServices()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dashboard conseiller', [
                'conseiller_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('layouts.app-conseiller', [
                'error' => 'Erreur lors du chargement de l\'interface conseiller',
                'userInfo' => $this->getUserInfo($user),
                'defaultWaitTime' => Setting::getDefaultWaitingTimeMinutes()
            ]);
        }
    }

    // ===============================================
    // MÉTHODES API CONSEILLER
    // ===============================================

    /**
     * 🎫 RÉCUPÉRER LES TICKETS EN ATTENTE (FIFO CHRONOLOGIQUE)
     */
    public function getConseillerTickets(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration manquante'
                ], 500);
            }

            // 🎯 RÉCUPÉRER LA FILE D'ATTENTE CHRONOLOGIQUE (FIFO)
            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            $ticketsEnAttente = Queue::whereIn('service_id', $myServiceIds)
                                    ->whereDate('date', today())
                                    ->where('statut_global', 'en_attente')
                                    ->orderBy('created_at', 'asc') // 🎯 FIFO : Premier arrivé, premier servi
                                    ->with(['service:id,nom,letter_of_service'])
                                    ->limit(20) // Limiter l'affichage
                                    ->get()
                                    ->map(function($ticket) {
                                        return $ticket->toTicketArray();
                                    });

            // 📊 STATISTIQUES GLOBALES CORRIGÉES
            $stats = [
                'total_en_attente' => Queue::whereIn('service_id', $myServiceIds)
                                          ->whereDate('date', today())
                                          ->where('statut_global', 'en_attente')
                                          ->count(),
                                          
                'total_en_cours' => Queue::whereIn('service_id', $myServiceIds)
                                        ->whereDate('date', today())
                                        ->where('statut_global', 'en_cours')
                                        ->count(),
                                        
                'total_termines' => Queue::whereIn('service_id', $myServiceIds)
                                        ->whereDate('date', today())
                                        ->where('statut_global', 'termine')
                                        ->count(),
            ];

            return response()->json([
                'success' => true,
                'tickets' => $ticketsEnAttente,
                'stats' => $stats,
                'queue_info' => [
                    'type' => 'fifo_chronological',
                    'principle' => 'Premier arrivé, premier servi',
                    'next_position' => Queue::calculateQueuePosition(),
                    'total_waiting' => $stats['total_en_attente'],
                    'default_wait_time' => Setting::getDefaultWaitingTimeMinutes()
                ],
                'timestamp' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération tickets conseiller', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tickets'
            ], 500);
        }
    }

    /**
     * 📞 APPELER LE PROCHAIN TICKET (FIFO)
     */
    public function callNextTicket(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Vérifier si le conseiller a déjà un ticket en cours
            $ticketEnCours = Queue::where('conseiller_client_id', $user->id)
                                 ->whereDate('date', today())
                                 ->where('statut_global', 'en_cours')
                                 ->first();

            if ($ticketEnCours) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà un ticket en cours de traitement',
                    'current_ticket' => $ticketEnCours->toTicketArray()
                ], 400);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration manquante'
                ], 500);
            }

            // 🎯 RÉCUPÉRER LE PROCHAIN TICKET FIFO
            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            $nextTicket = Queue::whereIn('service_id', $myServiceIds)
                              ->whereDate('date', today())
                              ->where('statut_global', 'en_attente')
                              ->orderBy('created_at', 'asc') // 🎯 FIFO : Le plus ancien
                              ->first();

            if (!$nextTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket en attente'
                ], 404);
            }

            // 📞 PRENDRE EN CHARGE LE TICKET
            DB::beginTransaction();
            
            $success = $nextTicket->priseEnCharge($user->id);
            
            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la prise en charge'
                ], 500);
            }

            DB::commit();

            Log::info('Ticket appelé par conseiller', [
                'ticket_id' => $nextTicket->id,
                'numero_ticket' => $nextTicket->numero_ticket,
                'conseiller_id' => $user->id,
                'conseiller_nom' => $user->username,
                'fifo_order' => 'Premier arrivé pris en charge'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Ticket {$nextTicket->numero_ticket} pris en charge",
                'ticket' => $nextTicket->fresh()->toTicketArray(),
                'queue_info' => [
                    'principe' => 'FIFO - Premier arrivé, premier servi',
                    'heure_prise_en_charge' => now()->format('H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur appel prochain ticket', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'appel du prochain ticket'
            ], 500);
        }
    }

    /**
     * ✅ TERMINER LE TICKET EN COURS
     */
    public function completeCurrentTicket(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Récupérer le ticket en cours
            $currentTicket = Queue::where('conseiller_client_id', $user->id)
                                 ->whereDate('date', today())
                                 ->where('statut_global', 'en_cours')
                                 ->first();

            if (!$currentTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket en cours'
                ], 404);
            }

            // Validation optionnelle
            $resolu = $request->input('resolu', 'Yes');
            $commentaire = $request->input('commentaire_resolution');

            if ($resolu === 'No' && empty($commentaire)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commentaire obligatoire pour les problèmes non résolus'
                ], 422);
            }

            // ✅ TERMINER LE TICKET
            DB::beginTransaction();
            
            $success = $currentTicket->terminer($resolu, $commentaire);
            
            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la finalisation'
                ], 500);
            }

            DB::commit();

            Log::info('Ticket terminé par conseiller', [
                'ticket_id' => $currentTicket->id,
                'numero_ticket' => $currentTicket->numero_ticket,
                'conseiller_id' => $user->id,
                'resolu' => $resolu,
                'duree_traitement' => $this->calculateProcessingTime($currentTicket)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Ticket {$currentTicket->numero_ticket} terminé avec succès",
                'ticket' => $currentTicket->fresh()->toTicketArray(),
                'processing_time' => $this->calculateProcessingTime($currentTicket)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur finalisation ticket', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation du ticket'
            ], 500);
        }
    }

    /**
     * 📊 STATISTIQUES PERSONNELLES CONSEILLER
     */
    public function getConseillerStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $date = $request->get('date', today());
            
            $stats = [
                'aujourd_hui' => [
                    'tickets_traites' => Queue::where('conseiller_client_id', $user->id)
                                            ->whereDate('date', $date)
                                            ->where('statut_global', 'termine')
                                            ->count(),
                                            
                    'temps_moyen_traitement' => Queue::where('conseiller_client_id', $user->id)
                                                    ->whereDate('date', $date)
                                                    ->where('statut_global', 'termine')
                                                    ->whereNotNull('heure_de_fin')
                                                    ->whereNotNull('heure_prise_en_charge')
                                                    ->selectRaw('AVG(TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60) as avg_minutes')
                                                    ->value('avg_minutes') ?? 0,
                                                    
                    'ticket_en_cours' => Queue::where('conseiller_client_id', $user->id)
                                             ->whereDate('date', $date)
                                             ->where('statut_global', 'en_cours')
                                             ->first()?->toTicketArray(),
                                             
                    'premier_ticket' => Queue::where('conseiller_client_id', $user->id)
                                            ->whereDate('date', $date)
                                            ->orderBy('heure_prise_en_charge', 'asc')
                                            ->first()?->heure_prise_en_charge,
                                            
                    'dernier_ticket' => Queue::where('conseiller_client_id', $user->id)
                                            ->whereDate('date', $date)
                                            ->orderBy('heure_de_fin', 'desc')
                                            ->first()?->heure_de_fin,
                ],
                
                'cette_semaine' => [
                    'tickets_traites' => Queue::where('conseiller_client_id', $user->id)
                                            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                                            ->where('statut_global', 'termine')
                                            ->count(),
                                            
                    'temps_moyen' => Queue::where('conseiller_client_id', $user->id)
                                         ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                                         ->where('statut_global', 'termine')
                                         ->whereNotNull('heure_de_fin')
                                         ->whereNotNull('heure_prise_en_charge')
                                         ->selectRaw('AVG(TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60) as avg_minutes')
                                         ->value('avg_minutes') ?? 0,
                ],
                
                'performance' => [
                    'efficacite' => $this->calculateEfficiencyScore($user->id),
                    'satisfaction_client' => $this->calculateSatisfactionScore($user->id),
                    'temps_pause_total' => 0, // TODO: Implémenter
                ]
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'conseiller_info' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'actif_depuis' => $user->created_at->diffForHumans()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur statistiques conseiller', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * 📜 HISTORIQUE DES TICKETS TRAITÉS
     */
    public function getConseillerHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 50);
            $date = $request->get('date', today());

            $query = Queue::where('conseiller_client_id', $user->id)
                         ->whereDate('date', $date)
                         ->where('statut_global', 'termine')
                         ->with(['service:id,nom,letter_of_service'])
                         ->orderBy('heure_de_fin', 'desc');

            $tickets = $query->paginate($limit, ['*'], 'page', $page);

            $ticketsFormatted = $tickets->items();
            $ticketsArray = array_map(function($ticket) {
                $ticketArray = $ticket->toTicketArray();
                $ticketArray['duree_traitement'] = $this->calculateProcessingTime($ticket);
                $ticketArray['debut_traitement'] = $ticket->heure_prise_en_charge;
                $ticketArray['fin_traitement'] = $ticket->heure_de_fin;
                return $ticketArray;
            }, $ticketsFormatted);

            return response()->json([
                'success' => true,
                'tickets' => $ticketsArray,
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'total' => $tickets->total(),
                    'per_page' => $tickets->perPage(),
                    'last_page' => $tickets->lastPage()
                ],
                'summary' => [
                    'total_tickets_traites' => Queue::where('conseiller_client_id', $user->id)
                                                   ->whereDate('date', $date)
                                                   ->where('statut_global', 'termine')
                                                   ->count(),
                    'temps_moyen_traitement' => Queue::where('conseiller_client_id', $user->id)
                                                    ->whereDate('date', $date)
                                                    ->where('statut_global', 'termine')
                                                    ->whereNotNull('heure_de_fin')
                                                    ->whereNotNull('heure_prise_en_charge')
                                                    ->selectRaw('AVG(TIME_TO_SEC(TIMEDIFF(heure_de_fin, heure_prise_en_charge))/60) as avg_minutes')
                                                    ->value('avg_minutes') ?? 0,
                ],
                'date' => Carbon::parse($date)->format('d/m/Y')
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur historique conseiller', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * ⏸️ TOGGLE PAUSE CONSEILLER
     */
    public function toggleConseillerPause(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $isPaused = $request->input('is_paused', false);
            
            Log::info('Toggle pause conseiller', [
                'conseiller_id' => $user->id,
                'is_paused' => $isPaused
            ]);

            return response()->json([
                'success' => true,
                'message' => $isPaused ? 'Pause activée' : 'Service repris',
                'is_paused' => $isPaused
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }

    /**
     * 🔍 DÉTAILS D'UN TICKET SPÉCIFIQUE
     */
    public function getTicketDetails(Request $request, $ticketId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration manquante'
                ], 500);
            }

            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            $ticket = Queue::whereIn('service_id', $myServiceIds)
                          ->where('id', $ticketId)
                          ->with(['service:id,nom,letter_of_service', 'conseillerClient:id,username'])
                          ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket non trouvé ou non autorisé'
                ], 404);
            }

            $ticketDetails = $ticket->toTicketArray();
            $ticketDetails['duree_traitement'] = $this->calculateProcessingTime($ticket);
            $ticketDetails['historique'] = $ticket->historique ?? [];
            
            // Calcul du statut priorité basé sur temps d'attente
            $waitingTime = $this->calculateTicketWaitingTime($ticket);
            if ($waitingTime > 30) {
                $ticketDetails['priority_status'] = 'urgent';
                $ticketDetails['priority_label'] = 'Urgent';
                $ticketDetails['priority_color'] = 'danger';
            } elseif ($waitingTime > 15) {
                $ticketDetails['priority_status'] = 'moyen';
                $ticketDetails['priority_label'] = 'Moyen';
                $ticketDetails['priority_color'] = 'warning';
            } else {
                $ticketDetails['priority_status'] = 'nouveau';
                $ticketDetails['priority_label'] = 'Nouveau';
                $ticketDetails['priority_color'] = 'success';
            }
            
            $ticketDetails['waiting_time_calculated'] = $waitingTime;

            return response()->json([
                'success' => true,
                'ticket' => $ticketDetails
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur détails ticket', [
                'conseiller_id' => Auth::id(),
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails'
            ], 500);
        }
    }

    /**
     * 🔄 TRANSFER TICKET
     */
    public function transferTicket(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en développement'
        ], 501);
    }

    /**
     * 🔄 RAFRAÎCHIR LA FILE D'ATTENTE EN TEMPS RÉEL
     */
    public function refreshConseillerQueue(Request $request): JsonResponse
    {
        return $this->getConseillerTickets($request);
    }

    /**
     * 👁️ APERÇU DU PROCHAIN TICKET SANS LE PRENDRE
     */
    public function getNextTicketPreview(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration manquante'
                ], 500);
            }

            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            $nextTicket = Queue::whereIn('service_id', $myServiceIds)
                              ->whereDate('date', today())
                              ->where('statut_global', 'en_attente')
                              ->orderBy('created_at', 'asc')
                              ->with(['service:id,nom,letter_of_service'])
                              ->first();

            if (!$nextTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket en attente',
                    'next_ticket' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'next_ticket' => $nextTicket->toTicketArray(),
                'queue_position' => 1,
                'estimated_call_time' => 'Maintenant',
                'fifo_info' => 'Premier arrivé, premier servi'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la prévisualisation'
            ], 500);
        }
    }

    /**
     * 🔍 VÉRIFIER LE TICKET ACTUEL DU CONSEILLER
     */
    public function getCurrentTicketStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $currentTicket = Queue::where('conseiller_client_id', $user->id)
                                 ->whereDate('date', today())
                                 ->where('statut_global', 'en_cours')
                                 ->first();

            if (!$currentTicket) {
                return response()->json([
                    'success' => true,
                    'has_current_ticket' => false,
                    'current_ticket' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'has_current_ticket' => true,
                'current_ticket' => $currentTicket->toTicketArray(),
                'processing_time' => $this->calculateProcessingTime($currentTicket),
                'started_at' => $currentTicket->heure_prise_en_charge
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut'
            ], 500);
        }
    }

    /**
     * 🔔 NOTIFICATIONS CONSEILLER
     */
    public function getConseillerNotifications(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'notifications' => []
                ]);
            }

            $myServiceIds = Service::where('created_by', $creator->id)->pluck('id');
            
            $notifications = [];
            
            $newTicketsCount = Queue::whereIn('service_id', $myServiceIds)
                                   ->whereDate('date', today())
                                   ->where('statut_global', 'en_attente')
                                   ->where('created_at', '>=', now()->subMinutes(5))
                                   ->count();
            
            if ($newTicketsCount > 0) {
                $notifications[] = [
                    'type' => 'new_tickets',
                    'message' => "{$newTicketsCount} nouveau(x) ticket(s) en attente",
                    'count' => $newTicketsCount,
                    'timestamp' => now()->format('H:i:s')
                ];
            }

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'notifications' => []
            ]);
        }
    }

    /**
     * 📈 STATISTIQUES TEMPS RÉEL CONSEILLER
     */
    public function getLiveConseillerStats(Request $request): JsonResponse
    {
        return $this->getConseillerStats($request);
    }

    /**
     * 📤 EXPORT DONNÉES CONSEILLER
     */
    public function exportConseillerData(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->isConseillerUser()) {
                abort(403, 'Accès non autorisé');
            }

            $date = $request->get('date', today());
            
            $tickets = Queue::where('conseiller_client_id', $user->id)
                           ->whereDate('date', $date)
                           ->where('statut_global', 'termine')
                           ->with(['service:id,nom'])
                           ->orderBy('heure_prise_en_charge', 'asc')
                           ->get();

            $filename = 'conseiller_' . $user->username . '_' . Carbon::parse($date)->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($tickets, $user) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'Numéro Ticket',
                    'Service',
                    'Client',
                    'Téléphone',
                    'Prise en charge',
                    'Fin traitement',
                    'Durée (min)',
                    'Résolu',
                    'Commentaire'
                ], ';');
                
                foreach ($tickets as $ticket) {
                    $duree = $this->calculateProcessingTime($ticket);
                    
                    fputcsv($file, [
                        $ticket->numero_ticket,
                        $ticket->service ? $ticket->service->nom : 'N/A',
                        $ticket->prenom,
                        $ticket->telephone,
                        $ticket->heure_prise_en_charge,
                        $ticket->heure_de_fin,
                        $duree,
                        $ticket->resolu,
                        $ticket->commentaire_resolution ?: ''
                    ], ';');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erreur export conseiller', [
                'conseiller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    // ===============================================
    // GÉNÉRATION DE TICKETS
    // ===============================================

    /**
     * 🎯 GÉNÉRATION DE NUMÉRO DE TICKET UNIQUE
     */
    private function generateUniqueTicketNumber($serviceId, $letterOfService)
    {
        $date = now()->format('Y-m-d');
        $counter = 1;
        
        do {
            $ticketNumber = $letterOfService . str_pad($counter, 3, '0', STR_PAD_LEFT);
            
            $exists = DB::table('queues')
                ->where('numero_ticket', $ticketNumber)
                ->where('date', $date)
                ->exists();
                
            if (!$exists) {
                return $ticketNumber;
            }
            
            $counter++;
            
            if ($counter > 999) {
                throw new \Exception("Impossible de générer un numéro de ticket unique pour le service");
            }
            
        } while (true);
    }

    /**
     * 🎫 GÉNÉRATION EFFECTIVE D'UN TICKET EN BASE DE DONNÉES
     */
    public function generateTicket(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user->isEcranUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Seuls les postes écran peuvent générer des tickets.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|integer|exists:services,id',
                'full_name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'comment' => 'nullable|string|max:500'
            ], [
                'service_id.required' => 'Le service est obligatoire.',
                'service_id.exists' => 'Service sélectionné invalide.',
                'full_name.required' => 'Le nom est obligatoire.',
                'full_name.max' => 'Le nom ne peut pas dépasser 100 caractères.',
                'phone.required' => 'Le téléphone est obligatoire.',
                'phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',
                'comment.max' => 'Le commentaire ne peut pas dépasser 500 caractères.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de configuration : admin créateur introuvable.'
                ], 500);
            }

            $service = Service::where('id', $request->service_id)
                             ->where('created_by', $creator->id)
                             ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non autorisé pour cet utilisateur.'
                ], 403);
            }

            if (!$service->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service n\'est pas disponible actuellement.'
                ], 400);
            }

            $ticket = DB::transaction(function () use ($request, $service, $user, $creator) {
                $letterOfService = strtoupper(substr($service->nom, 0, 1));
                $uniqueTicketNumber = $this->generateUniqueTicketNumber($service->id, $letterOfService);
                
                $position = Queue::whereDate('date', today())
                                ->where('statut_global', '!=', 'termine')
                                ->count() + 1;
                
                $estimatedWaitTime = Setting::getDefaultWaitingTimeMinutes();
                
                $ticketData = [
                    'id_agence' => $user->agency_id ?? 1,
                    'letter_of_service' => $letterOfService,
                    'service_id' => $service->id,
                    'prenom' => $request->full_name,
                    'telephone' => $request->phone,
                    'commentaire' => $request->comment ?? '',
                    'date' => now()->format('Y-m-d'),
                    'heure_d_enregistrement' => now()->format('H:i:s'),
                    'numero_ticket' => $uniqueTicketNumber,
                    'position_file' => $position,
                    'temps_attente_estime' => $estimatedWaitTime,
                    'statut_global' => 'en_attente',
                    'resolu' => 'En cours',
                    'transferer' => 'No',
                    'debut' => 'No',
                    'created_by_ip' => $request->ip(),
                    'historique' => json_encode([[
                        'action' => 'creation',
                        'timestamp' => now()->toISOString(),
                        'details' => 'Ticket créé avec numéro unique - Système anti-doublon'
                    ]]),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $ticketId = DB::table('queues')->insertGetId($ticketData);
                
                return (object) array_merge($ticketData, ['id' => $ticketId]);
            });

            $queueStats = Queue::getServiceStats($service->id);

            $response = [
                'success' => true,
                'message' => 'Ticket généré avec succès !',
                'ticket' => [
                    'id' => $ticket->id,
                    'number' => $ticket->numero_ticket,
                    'service' => $service->nom,
                    'service_letter' => $ticket->letter_of_service,
                    'position' => $ticket->position_file,
                    'estimated_time' => $ticket->temps_attente_estime,
                    'date' => now()->format('d/m/Y'),
                    'time' => Carbon::createFromFormat('H:i:s', $ticket->heure_d_enregistrement)->format('H:i'),
                    'fullName' => $ticket->prenom,
                    'phone' => $ticket->telephone,
                    'comment' => $ticket->commentaire ?: '',
                    'statut' => $ticket->statut_global,
                    'queue_stats' => $queueStats,
                    'queue_info' => [
                        'type' => 'service_numbering_unique',
                        'principle' => 'Numérotation par service avec système anti-doublon',
                        'arrival_time' => $ticket->heure_d_enregistrement,
                        'global_position' => $ticket->position_file,
                        'configured_wait_time' => Setting::getDefaultWaitingTimeMinutes()
                    ]
                ],
                'queue_status' => [
                    'total_today' => $queueStats['total_tickets'],
                    'waiting' => $queueStats['en_attente'],
                    'in_progress' => $queueStats['en_cours'],
                    'completed' => $queueStats['termines']
                ]
            ];

            Log::info('✅ Ticket généré avec succès - Système anti-doublon', [
                'ticket_id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service_name' => $service->nom,
                'user_id' => $user->id,
                'user_type' => $user->getUserRole(),
                'creator_admin' => $creator->username,
                'unique_number_generated' => true,
                'position_chronologique' => $ticket->position_file,
                'heure_arrivee' => $ticket->heure_d_enregistrement,
                'configured_wait_time' => Setting::getDefaultWaitingTimeMinutes(),
                'anti_duplicate_system' => 'active'
            ]);

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('❌ Erreur génération ticket - Système anti-doublon', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du ticket : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ RAFRAÎCHIR LES STATISTIQUES DES SERVICES
     */
    public function refreshUserServices(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isEcranUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $creator = $user->getCreator();
            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin créateur introuvable'
                ], 500);
            }

            $services = $creator->createdServices()
                              ->where('statut', 'actif')
                              ->get()
                              ->map(function($service) {
                                  $queueStats = Queue::getServiceStats($service->id);
                                  return [
                                      'id' => $service->id,
                                      'nom' => $service->nom,
                                      'letter_of_service' => $service->letter_of_service,
                                      'statut' => $service->statut,
                                      'queue_stats' => $queueStats
                                  ];
                              });

            return response()->json([
                'success' => true,
                'services' => $services,
                'timestamp' => now()->format('H:i:s'),
                'total_tickets_today' => Queue::whereIn('service_id', $services->pluck('id'))
                                              ->whereDate('date', today())
                                              ->count(),
                'queue_info' => [
                    'type' => 'service_numbering_unique',
                    'principle' => 'Numérotation par service avec système anti-doublon',
                    'next_global_position' => Queue::calculateQueuePosition(),
                    'configured_wait_time' => Setting::getDefaultWaitingTimeMinutes(),
                    'anti_duplicate_system' => 'active'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur refresh services Ecran', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement'
            ], 500);
        }
    }

    // ===============================================
    // GESTION DES UTILISATEURS
    // ===============================================

    /**
     * ✅ Liste des utilisateurs créés par l'admin connecté UNIQUEMENT
     */
    public function usersList(Request $request)
    {
        try {
            $currentAdmin = Auth::user();
            
            if (!$currentAdmin->isAdmin()) {
                abort(403, 'Accès non autorisé');
            }

            $myUserIds = AdministratorUser::where('administrator_id', $currentAdmin->id)
                                          ->pluck('user_id')
                                          ->toArray();
            
            $myUserIds[] = $currentAdmin->id;
            $myCreatedUserIds = $myUserIds;

            $query = User::whereIn('id', $myUserIds)
                        ->with(['userType', 'status', 'agency', 'createdBy']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('mobile_number', 'LIKE', "%{$search}%")
                      ->orWhere('company', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('user_type')) {
                $typeMapping = [
                    'admin' => 1,
                    'ecran' => 2,
                    'accueil' => 3,
                    'conseiller' => 4,
                ];
                
                if (isset($typeMapping[$request->user_type])) {
                    $query->where('user_type_id', $typeMapping[$request->user_type]);
                }
            }

            if ($request->filled('status')) {
                $statusMapping = [
                    'active' => 2,
                    'inactive' => 1,
                    'suspended' => 3,
                ];
                
                if (isset($statusMapping[$request->status])) {
                    $query->where('status_id', $statusMapping[$request->status]);
                }
            }

            if ($request->filled('agency_id')) {
                $query->where('agency_id', $request->agency_id);
            }

            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate(15)->appends($request->query());

            $stats = [
                'total_my_users' => count($myUserIds) - 1,
                'active_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1,
                'inactive_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'recent_my_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            $myAgencies = Agency::where('created_by', $currentAdmin->id)
                               ->orderBy('name')
                               ->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'mobile_number' => $user->mobile_number,
                            'company' => $user->company,
                            'type' => $user->getTypeName(),
                            'type_icon' => $user->getTypeIcon(),
                            'type_badge_color' => $user->getTypeBadgeColor(),
                            'type_emoji' => $user->getTypeEmoji(),
                            'agency' => $user->agency ? $user->agency->name : null,
                            'agency_id' => $user->agency_id,
                            'status' => $user->getStatusName(),
                            'status_badge_color' => $user->getStatusBadgeColor(),
                            'created_at' => $user->created_at->format('d/m/Y H:i'),
                            'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                            'is_active' => $user->isActive(),
                            'is_admin' => $user->isAdmin(),
                            'creation_info' => $user->getCreationInfo(),
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'last_page' => $users->lastPage()
                    ],
                    'stats' => $stats
                ]);
            }

            return view('User.users-list', compact('users', 'stats', 'myAgencies', 'myCreatedUserIds'));

        } catch (\Exception $e) {
            Log::error("Erreur liste utilisateurs pour admin " . Auth::id() . ": " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des utilisateurs'
                ], 500);
            }
            
            return redirect()->route('layouts.app')
                    ->with('error', 'Erreur lors de la récupération des utilisateurs.');
        }
    }

    // ===============================================
    // ACTIONS SUR LES UTILISATEURS
    // ===============================================

    /**
     * 🔒 Vérifier que l'admin connecté a créé cet utilisateur
     */
    private function checkUserOwnership(User $user): bool
    {
        $currentAdmin = Auth::user();
        
        if ($user->id === $currentAdmin->id) {
            return true;
        }
        
        return AdministratorUser::where('administrator_id', $currentAdmin->id)
                               ->where('user_id', $user->id)
                               ->exists();
    }

    /**
     * ✅ Activer utilisateur
     */
    public function activateUser(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas modifier cet utilisateur.');
        }

        try {
            $success = $user->activate();
            
            if ($success) {
                Log::info("Utilisateur {$user->username} activé par " . Auth::user()->username);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Utilisateur {$user->username} activé avec succès !"
                    ]);
                }
                
                return redirect()->back()->with('success', "Utilisateur {$user->username} activé !");
            }
            
            throw new \Exception('Échec de l\'activation');
            
        } catch (\Exception $e) {
            Log::error("Erreur activation utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'activation de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de l\'activation.');
        }
    }

    /**
     * ✅ Suspendre utilisateur
     */
    public function suspendUser(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas modifier cet utilisateur.');
        }

        if ($user->id === Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous suspendre vous-même.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous suspendre vous-même.');
        }

        try {
            $success = $user->suspend();
            
            if ($success) {
                Log::info("Utilisateur {$user->username} suspendu par " . Auth::user()->username);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Utilisateur {$user->username} suspendu avec succès !"
                    ]);
                }
                
                return redirect()->back()->with('success', "Utilisateur {$user->username} suspendu !");
            }
            
            throw new \Exception('Échec de la suspension');
            
        } catch (\Exception $e) {
            Log::error("Erreur suspension utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suspension de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suspension.');
        }
    }

    /**
     * ✅ Réactiver utilisateur
     */
    public function reactivateUser(User $user, Request $request)
    {
        return $this->activateUser($user, $request);
    }

    /**
     * ✅ Supprimer utilisateur
     */
    public function deleteUser(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas supprimer cet utilisateur.');
        }

        if ($user->id === Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous supprimer vous-même.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous supprimer vous-même.');
        }

        try {
            $username = $user->username;
            
            AdministratorUser::where('user_id', $user->id)->delete();
            $user->delete();
            
            Log::info("Utilisateur {$username} supprimé par " . Auth::user()->username);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Utilisateur {$username} supprimé avec succès !"
                ]);
            }
            
            return redirect()->back()->with('success', "Utilisateur {$username} supprimé !");
            
        } catch (\Exception $e) {
            Log::error("Erreur suppression utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * ✅ Actions en masse
     */
    public function bulkActivate(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $userIds = $request->input('user_ids', []);
            $currentAdminId = Auth::id();
            
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();

            if (empty($userIds)) {
                $count = User::whereIn('id', $myUserIds)
                            ->where('status_id', 1)
                            ->update(['status_id' => 2]);

                if ($count === 0) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Aucun utilisateur en attente d\'activation.'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "{$count} utilisateur(s) en attente activé(s) avec succès !"
                ]);
            }

            $validUserIds = array_intersect($userIds, $myUserIds);
            
            if (empty($validUserIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur autorisé dans la sélection.'
                ], 403);
            }

            $count = User::whereIn('id', $validUserIds)
                        ->where('status_id', '!=', 2)
                        ->update(['status_id' => 2]);

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos utilisateur(s) activé(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur activation en masse: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse.'
            ], 500);
        }
    }

    /**
     * ✅ Suppression en masse
     */
    public function bulkDeleteUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $userIds = $request->input('user_ids', []);
            
            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur sélectionné.'
                ], 400);
            }

            $userIds = array_filter($userIds, function($id) {
                return $id != Auth::id();
            });

            $myUserIds = AdministratorUser::where('administrator_id', Auth::id())
                                         ->pluck('user_id')
                                         ->toArray();
            
            $validUserIds = array_intersect($userIds, $myUserIds);
            
            if (empty($validUserIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur autorisé dans la sélection.'
                ], 403);
            }

            AdministratorUser::whereIn('user_id', $validUserIds)->delete();
            $count = User::whereIn('id', $validUserIds)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos utilisateur(s) supprimé(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur suppression en masse: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse.'
            ], 500);
        }
    }

    /**
     * ✅ Réinitialiser mot de passe
     */
    public function resetUserPassword(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        if (!$this->checkUserOwnership($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas réinitialiser le mot de passe de cet utilisateur.'
            ], 403);
        }

        try {
            $newPassword = $this->generateSecurePassword();
            $user->update(['password' => Hash::make($newPassword)]);

            $adminUserRecord = AdministratorUser::where('administrator_id', Auth::id())
                ->where('user_id', $user->id)
                ->first();
            
            if ($adminUserRecord) {
                $adminUserRecord->update([
                    'password_reset_required' => true,
                    'temporary_password' => $newPassword
                ]);
            }

            Log::info("Mot de passe réinitialisé pour {$user->username} par " . Auth::user()->username);

            return response()->json([
                'success' => true,
                'message' => "Mot de passe réinitialisé pour {$user->username}",
                'new_password' => $newPassword,
                'credentials' => [
                    'email' => $user->email,
                    'username' => $user->username,
                    'password' => $newPassword,
                    'login_url' => route('login')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur réinitialisation mot de passe: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.'
            ], 500);
        }
    }

    // ===============================================
    // API AJAX POUR STATISTIQUES ET RECHERCHE
    // ===============================================

    /**
     * ✅ Statistiques seulement pour les utilisateurs de l'admin
     */
    public function getStats(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $currentAdmin = Auth::user();
            
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdmin->id)
                                         ->pluck('user_id')
                                         ->toArray();
            
            $myUserIds[] = $currentAdmin->id;
            $myServiceIds = Service::where('created_by', $currentAdmin->id)->pluck('id');

            $stats = [
                'my_total_users' => count($myUserIds) - 1,
                'my_active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1,
                'my_inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'my_suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'my_recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'my_users_needing_password_reset' => AdministratorUser::where('administrator_id', $currentAdmin->id)
                                                                     ->where('password_reset_required', true)
                                                                     ->count(),
                
                'my_admin_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                'my_ecran_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'my_accueil_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                'my_conseiller_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                
                'my_agencies' => Agency::where('created_by', $currentAdmin->id)->count(),
                'my_active_agencies' => Agency::where('created_by', $currentAdmin->id)->where('status', 'active')->count(),
                'my_services' => Service::where('created_by', $currentAdmin->id)->count(),
                'my_active_services' => Service::where('created_by', $currentAdmin->id)->where('statut', 'actif')->count(),
                
                'my_tickets_today' => Queue::whereIn('service_id', $myServiceIds)->whereDate('date', today())->count(),
                'my_tickets_waiting' => Queue::whereIn('service_id', $myServiceIds)->whereDate('date', today())->where('statut_global', 'en_attente')->count(),
                'my_tickets_processing' => Queue::whereIn('service_id', $myServiceIds)->whereDate('date', today())->where('statut_global', 'en_cours')->count(),
                'my_tickets_completed' => Queue::whereIn('service_id', $myServiceIds)->whereDate('date', today())->where('statut_global', 'termine')->count(),
                'my_average_wait_time' => Queue::whereIn('service_id', $myServiceIds)->whereDate('date', today())->avg('temps_attente_estime') ?? 0,
            ];

            return response()->json([
                'success' => true, 
                'stats' => $stats,
                'admin_info' => [
                    'id' => $currentAdmin->id,
                    'username' => $currentAdmin->username,
                    'email' => $currentAdmin->email
                ],
                'queue_info' => [
                    'type' => 'service_numbering_unique',
                    'principle' => 'Numérotation par service avec système anti-doublon',
                    'configured_time' => Setting::getDefaultWaitingTimeMinutes(),
                    'anti_duplicate_system' => 'active'
                ],
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erreur statistiques isolées: " . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * ✅ Recherche seulement dans ses utilisateurs
     */
    public function searchUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        try {
            $currentAdminId = Auth::id();
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId;
            
            $users = User::whereIn('id', $myUserIds)
                        ->where(function($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%")
                              ->orWhere('mobile_number', 'LIKE', "%{$search}%")
                              ->orWhere('company', 'LIKE', "%{$search}%");
                        })
                        ->with(['userType', 'status'])
                        ->limit(5)
                        ->get();

            $suggestions = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->mobile_number,
                    'type' => $user->getTypeName(),
                    'status' => $user->getStatusName(),
                    'creation_info' => $user->getCreationInfo()
                ];
            });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * ✅ Détails utilisateur (vérification d'autorisation)
     */
    public function getUserDetails(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        if (!$this->checkUserOwnership($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas voir les détails de cet utilisateur.'
            ], 403);
        }

        try {
            $user->load(['userType', 'status', 'agency', 'createdBy.administrator']);
            
            $userDetails = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'company' => $user->company,
                'type_info' => $user->getTypeInfo(),
                'status_info' => $user->getStatusInfo(),
                'agency' => $user->agency ? [
                    'id' => $user->agency->id,
                    'name' => $user->agency->name,
                    'city' => $user->agency->city,
                    'country' => $user->agency->country
                ] : null,
                'login_info' => $user->getLastLoginInfo(),
                'password_info' => $user->getPasswordInfo(),
                'security_info' => $user->getLoginAttemptsInfo(),
                'creation_info' => $user->getCreationInfo(),
                'required_actions' => $user->getRequiredActions(),
                'permissions' => [
                    'can_edit' => true,
                    'can_delete' => $user->id !== Auth::id(),
                    'can_suspend' => $user->id !== Auth::id(),
                    'can_reset_password' => true,
                ]
            ];

            return response()->json([
                'success' => true,
                'user' => $userDetails
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur détails utilisateur: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    /**
     * ✅ Statistiques avancées isolées
     */
    public function getAdvancedStats(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $currentAdminId = Auth::id();
            
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId;
            
            $statsByType = [
                'admin' => [
                    'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                    'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 2)->count(),
                    'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 1)->count(),
                    'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 3)->count(),
                ],
                'ecran' => [
                    'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                    'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 2)->count(),
                    'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 1)->count(),
                    'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 3)->count(),
                ],
                'accueil' => [
                    'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                    'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 2)->count(),
                    'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 1)->count(),
                    'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 3)->count(),
                ],
                'conseiller' => [
                    'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                    'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 2)->count(),
                    'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 1)->count(),
                    'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 3)->count(),
                ]
            ];

            return response()->json([
                'success' => true, 
                'stats_by_type' => $statsByType,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques avancées'
            ], 500);
        }
    }

    /**
     * ✅ Export seulement des utilisateurs de l'admin
     */
    public function exportUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            $currentAdminId = Auth::id();
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId;
            
            $users = User::whereIn('id', $myUserIds)
                        ->with(['userType', 'status', 'createdBy.administrator'])
                        ->get();
            
            $filename = 'mes_utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'ID',
                    'Nom d\'utilisateur',
                    'Email',
                    'Téléphone',
                    'Type',
                    'Statut',
                    'Créé par',
                    'Date de création',
                    'Dernière modification'
                ], ';');
                
                foreach ($users as $user) {
                    $createdBy = $user->getCreator();
                    
                    fputcsv($file, [
                        $user->id,
                        $user->username,
                        $user->email,
                        $user->mobile_number,
                        $user->getTypeName(),
                        $user->getStatusName(),
                        $createdBy ? $createdBy->username : 'Inscription directe',
                        $user->created_at->format('d/m/Y H:i:s'),
                        $user->updated_at->format('d/m/Y H:i:s')
                    ], ';');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Export error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    // ===============================================
    // MÉTHODES UTILITAIRES PRIVÉES
    // ===============================================

    /**
     * ⏱️ CALCULER LE TEMPS DE TRAITEMENT D'UN TICKET
     */
    private function calculateProcessingTime($ticket): int
    {
        if (!$ticket->heure_prise_en_charge) {
            return 0;
        }

        $start = Carbon::parse($ticket->heure_prise_en_charge);
        $end = $ticket->heure_de_fin ? Carbon::parse($ticket->heure_de_fin) : now();
        
        return $start->diffInMinutes($end);
    }

    /**
     * ✅ CALCULER LE TEMPS D'ATTENTE D'UN TICKET SPÉCIFIQUE
     */
    private function calculateTicketWaitingTime($ticket): int
    {
        $now = now();
        $arrival = null;
        
        if ($ticket->heure_d_enregistrement && $ticket->heure_d_enregistrement !== '--:--') {
            $today = $now->toDateString();
            $arrival = Carbon::parse($today . ' ' . $ticket->heure_d_enregistrement);
        } elseif ($ticket->created_at) {
            $arrival = Carbon::parse($ticket->created_at);
        }
        
        if (!$arrival) {
            return 0;
        }
        
        return max(0, $arrival->diffInMinutes($now));
    }

    /**
     * 📊 CALCULER LE SCORE D'EFFICACITÉ (placeholder)
     */
    private function calculateEfficiencyScore($conseillerId): float
    {
        return 85.5; // TODO: Implémenter le calcul réel
    }

    /**
     * ⭐ CALCULER LE SCORE DE SATISFACTION CLIENT (placeholder)
     */
    private function calculateSatisfactionScore($conseillerId): float
    {
        return 4.2; // TODO: Implémenter le calcul réel sur 5
    }

    /**
     * 🆕 Données spécifiques selon le type d'utilisateur
     */
    private function getTypeSpecificData(User $user): array
    {
        $data = [
            'type_description' => '',
            'type_features' => [],
            'type_recommendations' => []
        ];

        if ($user->isAccueilUser()) {
            $data['type_description'] = 'Poste Accueil - Réception et orientation des visiteurs';
            $data['type_features'] = [
                'Accueil des visiteurs',
                'Orientation et information',
                'Gestion des rendez-vous',
                'Communication interne'
            ];
            $data['type_recommendations'] = [
                'Vérifiez régulièrement les nouveaux visiteurs',
                'Tenez à jour les informations d\'orientation',
                'Communiquez avec l\'équipe de gestion'
            ];
        } elseif ($user->isConseillerUser()) {
            $data['type_description'] = 'Poste Conseiller - Support et assistance client';
            $data['type_features'] = [
                'Support client avancé',
                'Résolution de problèmes',
                'Conseils personnalisés',
                'Suivi client'
            ];
            $data['type_recommendations'] = [
                'Restez à jour sur les procédures',
                'Documentez les interactions clients',
                'Collaborez avec l\'équipe support'
            ];
        }

        return $data;
    }

    /**
     * 🆕 Informations utilisateur formatées
     */
    private function getUserInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'company' => $user->company,
            'type_info' => $user->getTypeInfo(),
            'status_info' => $user->getStatusInfo(),
            'creation_info' => $user->getCreationInfo(),
            'agency' => $user->agency ? [
                'name' => $user->agency->name,
                'city' => $user->agency->city,
                'country' => $user->agency->country
            ] : null,
            'login_info' => $user->getLastLoginInfo(),
            'password_info' => $user->getPasswordInfo()
        ];
    }

    /**
     * Générer un mot de passe sécurisé
     */
    private function generateSecurePassword($length = 8): string 
    {
        $voyelles = 'aeiou';
        $consonnes = 'bcdfghjklmnpqrstvwxz';
        $password = '';
        
        $password .= strtoupper($consonnes[rand(0, strlen($consonnes) - 1)]);
        $password .= $voyelles[rand(0, strlen($voyelles) - 1)];
        $password .= $consonnes[rand(0, strlen($consonnes) - 1)];
        $password .= rand(100, 999);
        $password .= '@';
        
        return $password;
    }

    /**
     * Formater l'âge du compte
     */
    private function formatAccountAge(int $days): string
    {
        if ($days < 1) {
            return 'Moins d\'un jour';
        } elseif ($days === 1) {
            return '1 jour';
        } elseif ($days < 7) {
            return $days . ' jours';
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            return $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        } else {
            $years = floor($days / 365);
            return $years . ' an' . ($years > 1 ? 's' : '');
        } 
    }
}