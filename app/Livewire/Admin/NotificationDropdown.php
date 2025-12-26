<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationDropdown extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    protected $listeners = [
        'notificationRead' => '$refresh',
        'echo:notifications,DatabaseNotificationCreated' => 'handleNewNotification'
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            $this->unreadCount = 0;
            $this->notifications = [];
            return;
        }
        
        // Get unread count
        $this->unreadCount = $user->unreadNotifications()->count();
        
        // Get recent notifications (10 most recent)
        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->getNotificationType($notification),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'icon' => $this->getNotificationIcon($notification),
                    'color' => $this->getNotificationColor($notification),
                ];
            })->toArray();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notificationRead');
        }
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        $user->unreadNotifications->markAsRead();
        $this->loadNotifications();
        $this->dispatch('notificationRead');
    }

    public function deleteNotification($notificationId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    private function getNotificationType($notification)
    {
        $class = class_basename($notification->type);
        
        switch ($class) {
            case 'NewUserRegistered':
                return 'Nouvel utilisateur';
            case 'NewChurchCreated':
                return 'Nouvelle église';
            case 'WelcomeNotification':
                return 'Bienvenue';
            case 'CustomResetPasswordNotification':
                return 'Réinitialisation mot de passe';
            case 'EmailChangeCodeNotification':
                return 'Changement d\'email';
            default:
                return 'Notification';
        }
    }

    private function getNotificationIcon($notification)
    {
        $class = class_basename($notification->type);
        
        switch ($class) {
            case 'NewUserRegistered':
                return 'users';
            case 'NewChurchCreated':
                return 'church';
            case 'WelcomeNotification':
                return 'user';
            case 'CustomResetPasswordNotification':
                return 'logout';
            case 'EmailChangeCodeNotification':
                return 'bell';
            default:
                return 'bell';
        }
    }

    private function getNotificationColor($notification)
    {
        $class = class_basename($notification->type);
        
        switch ($class) {
            case 'NewUserRegistered':
                return 'blue';
            case 'NewChurchCreated':
                return 'green';
            case 'WelcomeNotification':
                return 'green';
            case 'CustomResetPasswordNotification':
                return 'orange';
            case 'EmailChangeCodeNotification':
                return 'blue';
            default:
                return 'violet';
        }
    }

    public function handleNewNotification()
    {
        $this->loadNotifications();
        $this->dispatch('newNotification');
    }

    public function render()
    {
        return view('livewire.admin.notification-dropdown');
    }
}
