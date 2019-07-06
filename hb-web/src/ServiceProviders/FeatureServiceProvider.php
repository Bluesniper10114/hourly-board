<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use Management\Menu\MainMenu;
use \Core\View;

/**
 * Checks if a feature is permitted for a role
 */
class FeatureServiceProvider extends ServiceProvider
{
    /** 
     * List of features which will be automatically excluded at application level.
     * Exclude these features if the implementation does not allow e.g. a certain type of planning
     * 
     * @var string[] 
     */
    public $excludedFeatures = [];
    
    /**
     * Checks if at least one feature in $features is permitted by allowed features for $role.
     *
     * @param array $features List of features to be checked
     * @param int $role The authenticated user role
     * @return bool True if permitted
     */
    public function anyFeaturePermitted(array $features, $role)
    {
        $allowedFeatures = $this->getFeaturesByRole($role);
        $filteredFeatures = array_diff($allowedFeatures, $this->excludedFeatures);
        return count(array_intersect($features, $filteredFeatures)) > 0;
    }

    /**
     * Builds a feature permission array for a given role
     *
     * @param int $role User role
     * @return string[] Permitted features
     */
    private function getFeaturesByRole($role)
    {
        switch ($role){
            
            case '1': // Super User
                return array_merge(
                    self::FEATURES_MANAGE_USERS, 
                    self::FEATURES_PLANNING_BASIC,
                    self::FEATURES_PLANNING_ADVANCED,
                    self::FEATURES_PLANNING_PRODUCTION_LINES,

                    self::FEATURES_MONITORS,
                    self::FEATURES_GENERAL_SETTINGS,
                    self::FEATURES_RIGHTS_SETTINGS,

                    self::FEATURES_REPORTING,
                    self::FEATURES_LOGIN,
                    self::FEATURES_MY_PROFILE
                );
                break;
            case '2': // IT Admin
                return array_merge(
                    self::FEATURES_MANAGE_USERS, 
                    self::FEATURES_PLANNING_BASIC,
                    self::FEATURES_PLANNING_ADVANCED,
                    self::FEATURES_PLANNING_PRODUCTION_LINES,

                    self::FEATURES_MONITORS,
                    self::FEATURES_RIGHTS_SETTINGS,

                    self::FEATURES_REPORTING,
                    self::FEATURES_LOGIN,
                    self::FEATURES_MY_PROFILE
                );
                break;
            case '5': // Airbag
                return array_merge(
                    self::FEATURES_MANAGE_USERS, 
                    self::FEATURES_PLANNING_BASIC,
                    self::FEATURES_PLANNING_PRODUCTION_LINES,

                    self::FEATURES_MONITORS,
                    self::FEATURES_RIGHTS_SETTINGS,

                    self::FEATURES_REPORTING,
                    self::FEATURES_LOGIN,
                    self::FEATURES_MY_PROFILE
                );
                break;
            case '6': // Assy
                return array_merge(
                    self::FEATURES_MANAGE_USERS, 
                    self::FEATURES_PLANNING_BASIC,
                    self::FEATURES_PLANNING_ADVANCED,
                    self::FEATURES_PLANNING_PRODUCTION_LINES,

                    self::FEATURES_MONITORS,
                    self::FEATURES_RIGHTS_SETTINGS,

                    self::FEATURES_REPORTING,
                    self::FEATURES_LOGIN,
                    self::FEATURES_MY_PROFILE
                );
                break;
            default: // simple user role
                return array_merge(
                    self::FEATURES_LOGIN,
                    self::FEATURES_MY_PROFILE
                );
                break;
        }
    }

    const FEATURES_MANAGE_USERS = [
        'management.users',                     // list of users
        'management.users.add',                 // add a new user
        'management.users.edit',                // edit an existing user
        'management.users.delete',              // delete an existing user
    ];

    const FEATURES_PLANNING_BASIC = [
        'management.planning',                  // access to the planning menu
        'management.planning.datasets',         // access to planning datasets overview
        'management.planning.by-day',           // planning by day
        'management.planning.breaks',           // viewing breaks
        'management.planning.breaks.save',      // saving breaks
    ];

    const FEATURES_PLANNING_ADVANCED = [
        'management.planning.by-partnumber',    // planning by part number dataset
        'management.settings.routings-per-pn',  // Routing for PNs
    ];

    const FEATURES_PLANNING_PRODUCTION_LINES = [
        'management.settings.production-lines', // shows the production lines
    ];

    const FEATURES_MONITORS = [
        'management.monitors',                  // allows access to the monitors management screens
        'management.monitors.add',              // add a new monitor
        'management.monitors.edit',             // edit a monitor
        'management.monitors.view',             // view a monitor
    ];

    const FEATURES_GENERAL_SETTINGS = [
        'management.settings.general',          // settings form view
        'management.settings.general.save',     // saves the settings
    ];

    const FEATURES_RIGHTS_SETTINGS = [
        'management.settings.rights',           // rights management for hourly and shift signoff
    ];

    const FEATURES_REPORTING = [
        'management.reports',                   // viewing reports
    ];

    const FEATURES_LOGIN = [
        /**
         * form for login / process the login data + redirect to dashboard /
         * Checks if the login button can be enabled / destroys the user session +  redirect on login
         */
        'management.login',                     // login feature
        'management.logout',                    // logout feature
        'management.shopfloor',                    // shopfloor feature
    ];


    const FEATURES_MY_PROFILE = [
        'management.profile', // Show my profile info
        'management.profile.change-password', // Change my profile password
    ];
}