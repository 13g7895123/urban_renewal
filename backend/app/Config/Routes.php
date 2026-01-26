<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes with CORS handling
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Handle preflight OPTIONS requests for all API routes
    $routes->options('(:any)', 'UrbanRenewalController::options');
    $routes->options('(:any)/(:any)', 'LandPlotController::options');

    // Authentication API
    $routes->group('auth', function ($routes) {
        $routes->post('register', 'AuthController::register');                   // POST /api/auth/register
        $routes->post('login', 'AuthController::login');                         // POST /api/auth/login
        $routes->post('logout', 'AuthController::logout');                       // POST /api/auth/logout
        $routes->post('refresh', 'AuthController::refresh');                     // POST /api/auth/refresh
        $routes->get('me', 'AuthController::me');                               // GET /api/auth/me
        $routes->post('forgot-password', 'AuthController::forgotPassword');      // POST /api/auth/forgot-password
        $routes->post('reset-password', 'AuthController::resetPassword');        // POST /api/auth/reset-password

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'AuthController::options');
    });

    // User Management API
    $routes->group('users', function ($routes) {
        $routes->get('/', 'UserController::index');                             // GET /api/users
        $routes->get('(:num)', 'UserController::show/$1');                      // GET /api/users/{id}
        $routes->post('/', 'UserController::create');                           // POST /api/users
        $routes->put('(:num)', 'UserController::update/$1');                    // PUT /api/users/{id}
        $routes->delete('(:num)', 'UserController::delete/$1');                 // DELETE /api/users/{id}
        $routes->patch('(:num)/toggle-status', 'UserController::toggleStatus/$1'); // PATCH /api/users/{id}/toggle-status
        $routes->patch('(:num)/reset-login-attempts', 'UserController::resetLoginAttempts/$1'); // PATCH /api/users/{id}/reset-login-attempts
        $routes->post('(:num)/set-as-company-user', 'UserController::setAsCompanyUser/$1'); // POST /api/users/{id}/set-as-company-user
        $routes->post('(:num)/set-as-company-manager', 'UserController::setAsCompanyManager/$1'); // POST /api/users/{id}/set-as-company-manager
        $routes->get('search', 'UserController::search');                       // GET /api/users/search
        $routes->get('role-statistics', 'UserController::roleStatistics');      // GET /api/users/role-statistics
        $routes->get('stats', 'UserController::stats');                         // GET /api/users/stats
        $routes->get('profile', 'UserController::profile');                     // GET /api/users/profile
        $routes->put('profile', 'UserController::updateProfile');               // PUT /api/users/profile
        $routes->post('change-password', 'UserController::changePassword');     // POST /api/users/change-password
        $routes->patch('(:num)/password', 'UserController::changePasswordAdmin/$1'); // PATCH /api/users/{id}/password

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'UserController::options');
    });

    // Companies API
    $routes->group('companies', function ($routes) {
        $routes->get('me', 'CompanyController::me');                  // GET /api/companies/me
        $routes->put('me', 'CompanyController::update');              // PUT /api/companies/me
        $routes->get('me/renewals', 'CompanyController::getRenewals'); // GET /api/companies/me/renewals
        $routes->get('me/pending-users', 'CompanyController::getPendingUsers'); // GET /api/companies/me/pending-users
        $routes->post('me/approve-user/(:num)', 'CompanyController::approveUser/$1'); // POST /api/companies/me/approve-user/{id}
        $routes->get('me/invite-code', 'CompanyController::getInviteCode'); // GET /api/companies/me/invite-code
        $routes->post('me/generate-invite-code', 'CompanyController::generateInviteCode'); // POST /api/companies/me/generate-invite-code

        // Members assignment to renewals
        $routes->get('me/renewals/(:num)/members', 'CompanyController::getRenewalMembers/$1'); // GET /api/companies/me/renewals/{id}/members
        $routes->post('me/renewals/(:num)/assign', 'CompanyController::assignMemberToRenewal/$1'); // POST /api/companies/me/renewals/{id}/assign
        $routes->delete('me/renewals/(:num)/members/(:num)', 'CompanyController::unassignMemberFromRenewal/$1/$2'); // DELETE /api/companies/me/renewals/{id}/members/{userId}
        $routes->get('me/available-members', 'CompanyController::getAvailableMembers'); // GET /api/companies/me/available-members

        $routes->get('(:num)/managers', 'CompanyController::getManagers/$1'); // GET /api/companies/{id}/managers
        $routes->get('(:num)/users', 'CompanyController::getUsers/$1');       // GET /api/companies/{id}/users

        // Handle OPTIONS for specific routes
        $routes->options('me', 'CompanyController::options');
        $routes->options('(:num)/managers', 'CompanyController::options');
        $routes->options('(:num)/users', 'CompanyController::options');
        $routes->options('me/renewals', 'CompanyController::options');
    });

    // Urban Renewals API
    $routes->group('urban-renewals', function ($routes) {
        // Batch operations must come before single resource routes
        $routes->post('batch-assign', 'UrbanRenewalController::batchAssign');     // POST /api/urban-renewals/batch-assign
        $routes->get('company-managers', 'UrbanRenewalController::getCompanyManagers'); // GET /api/urban-renewals/company-managers

        $routes->get('/', 'UrbanRenewalController::index');           // GET /api/urban-renewals
        $routes->get('(:num)', 'UrbanRenewalController::show/$1');    // GET /api/urban-renewals/{id}
        $routes->post('/', 'UrbanRenewalController::create');         // POST /api/urban-renewals
        $routes->put('(:num)', 'UrbanRenewalController::update/$1');  // PUT /api/urban-renewals/{id}
        $routes->delete('(:num)', 'UrbanRenewalController::delete/$1'); // DELETE /api/urban-renewals/{id}

        // Land Plots nested routes
        $routes->get('(:num)/land-plots', 'LandPlotController::index/$1');        // GET /api/urban-renewals/{id}/land-plots
        $routes->post('(:num)/land-plots', 'LandPlotController::create/$1');      // POST /api/urban-renewals/{id}/land-plots

        // Joint Common Areas nested routes
        $routes->get('(:num)/joint-common-areas', 'JointCommonAreaController::index/$1');   // GET /api/urban-renewals/{id}/joint-common-areas
        $routes->post('(:num)/joint-common-areas', 'JointCommonAreaController::create/$1'); // POST /api/urban-renewals/{id}/joint-common-areas

        // Property Owners nested routes (specific routes must come before general routes)
        $routes->get('(:num)/property-owners/all-buildings', 'PropertyOwnerController::getAllBuildingsByUrbanRenewal/$1'); // GET /api/urban-renewals/{id}/property-owners/all-buildings
        $routes->get('(:num)/property-owners/export', 'PropertyOwnerController::export/$1'); // GET /api/urban-renewals/{id}/property-owners/export
        $routes->post('(:num)/property-owners/import', 'PropertyOwnerController::import/$1'); // POST /api/urban-renewals/{id}/property-owners/import
        $routes->get('(:num)/property-owners', 'PropertyOwnerController::getByUrbanRenewal/$1'); // GET /api/urban-renewals/{id}/property-owners

        // Handle OPTIONS for specific routes
        $routes->options('batch-assign', 'UrbanRenewalController::options');
        $routes->options('company-managers', 'UrbanRenewalController::options');
        $routes->options('/', 'UrbanRenewalController::options');
        $routes->options('(:num)', 'UrbanRenewalController::options');
        $routes->options('(:num)/land-plots', 'LandPlotController::options');
        $routes->options('(:num)/joint-common-areas', 'JointCommonAreaController::options');
        $routes->options('(:num)/property-owners', 'PropertyOwnerController::options');
        $routes->options('(:num)/property-owners/all-buildings', 'PropertyOwnerController::options');
        $routes->options('(:num)/property-owners/export', 'PropertyOwnerController::options');
        $routes->options('(:num)/property-owners/import', 'PropertyOwnerController::options');
    });

    // Meetings API
    $routes->group('meetings', function ($routes) {
        $routes->get('/', 'MeetingController::index');                          // GET /api/meetings
        $routes->post('/', 'MeetingController::create');                        // POST /api/meetings

        // Specific routes with patterns must come before generic (:num) route
        $routes->get('search', 'MeetingController::search');                    // GET /api/meetings/search
        $routes->get('upcoming', 'MeetingController::upcoming');                // GET /api/meetings/upcoming
        $routes->get('status-statistics', 'MeetingController::statusStatistics'); // GET /api/meetings/status-statistics
        $routes->get('(:num)/statistics', 'MeetingController::statistics/$1');  // GET /api/meetings/{id}/statistics
        $routes->get('(:num)/export-notice', 'MeetingController::exportNotice/$1'); // GET /api/meetings/{id}/export-notice
        $routes->get('(:num)/export-signature-book', 'MeetingController::exportSignatureBook/$1'); // GET /api/meetings/{id}/export-signature-book
        $routes->patch('(:num)/status', 'MeetingController::updateStatus/$1');  // PATCH /api/meetings/{id}/status

        // Generic routes with (:num) at the end
        $routes->get('(:num)', 'MeetingController::show/$1');                   // GET /api/meetings/{id}
        $routes->put('(:num)', 'MeetingController::update/$1');                 // PUT /api/meetings/{id}
        $routes->delete('(:num)', 'MeetingController::delete/$1');              // DELETE /api/meetings/{id}

        // Meeting Attendances nested routes
        $routes->get('(:num)/attendances', 'MeetingAttendanceController::index/$1');          // GET /api/meetings/{id}/attendances
        $routes->post('(:num)/attendances/(:num)', 'MeetingAttendanceController::checkIn/$1/$2'); // POST /api/meetings/{meetingId}/attendances/{ownerId}
        $routes->put('(:num)/attendances/(:num)', 'MeetingAttendanceController::update/$1/$2'); // PUT /api/meetings/{meetingId}/attendances/{ownerId}
        $routes->get('(:num)/attendances/statistics', 'MeetingAttendanceController::statistics/$1'); // GET /api/meetings/{id}/attendances/statistics
        $routes->post('(:num)/attendances/export', 'MeetingAttendanceController::export/$1'); // POST /api/meetings/{id}/attendances/export
        $routes->post('(:num)/attendances/batch', 'MeetingAttendanceController::batchCheckIn/$1'); // POST /api/meetings/{id}/attendances/batch

        // Eligible Voters (Snapshot) nested routes
        $routes->get('(:num)/eligible-voters', 'MeetingController::getEligibleVoters/$1');    // GET /api/meetings/{id}/eligible-voters
        $routes->post('(:num)/eligible-voters/refresh', 'MeetingController::refreshEligibleVoters/$1'); // POST /api/meetings/{id}/eligible-voters/refresh

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'MeetingController::options');
        $routes->options('(:num)/attendances', 'MeetingAttendanceController::options');
        $routes->options('(:num)/attendances/(:any)', 'MeetingAttendanceController::options');
    });

    // Meeting Attendance API
    $routes->group('meeting-attendance', function ($routes) {
        $routes->get('/', 'MeetingAttendanceController::index');                // GET /api/meeting-attendance
        $routes->post('check-in', 'MeetingAttendanceController::checkIn');      // POST /api/meeting-attendance/check-in
        $routes->post('batch-check-in', 'MeetingAttendanceController::batchCheckIn'); // POST /api/meeting-attendance/batch-check-in
        $routes->patch('(:num)/update-status', 'MeetingAttendanceController::updateStatus/$1'); // PATCH /api/meeting-attendance/{id}/update-status
        $routes->get('(:num)/summary', 'MeetingAttendanceController::summary/$1'); // GET /api/meeting-attendance/{meetingId}/summary
        $routes->get('(:num)/export', 'MeetingAttendanceController::export/$1'); // GET /api/meeting-attendance/{meetingId}/export
        $routes->get('(:num)/statistics', 'MeetingAttendanceController::statistics/$1'); // GET /api/meeting-attendance/{meetingId}/statistics

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'MeetingAttendanceController::options');
    });

    // Voting Topics API
    $routes->group('voting-topics', function ($routes) {
        $routes->get('/', 'VotingTopicController::index');                      // GET /api/voting-topics
        $routes->get('(:num)', 'VotingTopicController::show/$1');               // GET /api/voting-topics/{id}
        $routes->post('/', 'VotingTopicController::create');                    // POST /api/voting-topics
        $routes->put('(:num)', 'VotingTopicController::update/$1');             // PUT /api/voting-topics/{id}
        $routes->delete('(:num)', 'VotingTopicController::delete/$1');          // DELETE /api/voting-topics/{id}
        $routes->patch('(:num)/start-voting', 'VotingTopicController::startVoting/$1'); // PATCH /api/voting-topics/{id}/start-voting
        $routes->patch('(:num)/close-voting', 'VotingTopicController::closeVoting/$1'); // PATCH /api/voting-topics/{id}/close-voting
        $routes->get('statistics', 'VotingTopicController::statistics');        // GET /api/voting-topics/statistics
        $routes->get('upcoming', 'VotingTopicController::upcoming');            // GET /api/voting-topics/upcoming

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'VotingTopicController::options');
    });

    // Voting API
    $routes->group('voting', function ($routes) {
        $routes->get('/', 'VotingController::index');                           // GET /api/voting
        $routes->post('vote', 'VotingController::vote');                        // POST /api/voting/vote
        $routes->post('batch-vote', 'VotingController::batchVote');             // POST /api/voting/batch-vote
        $routes->get('my-vote/(:num)', 'VotingController::myVote/$1');          // GET /api/voting/my-vote/{topicId}
        $routes->delete('remove-vote', 'VotingController::removeVote');         // DELETE /api/voting/remove-vote
        $routes->get('statistics/(:num)', 'VotingController::statistics/$1');   // GET /api/voting/statistics/{topicId}
        $routes->get('export/(:num)', 'VotingController::export/$1');           // GET /api/voting/export/{topicId}
        $routes->get('detailed/(:num)', 'VotingController::detailed/$1');       // GET /api/voting/detailed/{topicId}
        $routes->post('recalculate-weights/(:num)', 'VotingController::recalculateWeights/$1');  // POST /api/voting/recalculate-weights/{topicId}

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'VotingController::options');
    });

    // System Settings API
    $routes->group('system-settings', function ($routes) {
        $routes->get('/', 'SystemSettingsController::index');                   // GET /api/system-settings
        $routes->get('public', 'SystemSettingsController::public');             // GET /api/system-settings/public
        $routes->get('category/(:segment)', 'SystemSettingsController::category/$1'); // GET /api/system-settings/category/{category}
        $routes->get('get/(:segment)', 'SystemSettingsController::get/$1');     // GET /api/system-settings/get/{key}
        $routes->post('set', 'SystemSettingsController::set');                  // POST /api/system-settings/set
        $routes->post('batch-set', 'SystemSettingsController::batchSet');       // POST /api/system-settings/batch-set
        $routes->patch('reset/(:segment)', 'SystemSettingsController::reset/$1'); // PATCH /api/system-settings/reset/{key}
        $routes->get('categories', 'SystemSettingsController::categories');     // GET /api/system-settings/categories
        $routes->delete('clear-cache', 'SystemSettingsController::clearCache'); // DELETE /api/system-settings/clear-cache
        $routes->post('validate', 'SystemSettingsController::validateSetting');  // POST /api/system-settings/validate
        $routes->get('system-info', 'SystemSettingsController::systemInfo');    // GET /api/system-settings/system-info

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'SystemSettingsController::options');
    });

    // Notifications API
    $routes->group('notifications', function ($routes) {
        $routes->get('/', 'NotificationController::index');                     // GET /api/notifications
        $routes->get('(:num)', 'NotificationController::show/$1');              // GET /api/notifications/{id}
        $routes->post('/', 'NotificationController::create');                   // POST /api/notifications
        $routes->patch('(:num)/mark-read', 'NotificationController::markRead/$1'); // PATCH /api/notifications/{id}/mark-read
        $routes->patch('mark-multiple-read', 'NotificationController::markMultipleRead'); // PATCH /api/notifications/mark-multiple-read
        $routes->patch('mark-all-read', 'NotificationController::markAllRead'); // PATCH /api/notifications/mark-all-read
        $routes->delete('(:num)', 'NotificationController::delete/$1');         // DELETE /api/notifications/{id}
        $routes->get('unread-count', 'NotificationController::unreadCount');    // GET /api/notifications/unread-count
        $routes->get('statistics', 'NotificationController::statistics');       // GET /api/notifications/statistics
        $routes->post('create-meeting-notification', 'NotificationController::createMeetingNotification'); // POST /api/notifications/create-meeting-notification
        $routes->post('create-voting-notification', 'NotificationController::createVotingNotification'); // POST /api/notifications/create-voting-notification
        $routes->delete('clean-expired', 'NotificationController::cleanExpired'); // DELETE /api/notifications/clean-expired
        $routes->get('types', 'NotificationController::types');                 // GET /api/notifications/types

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'NotificationController::options');
    });

    // Documents API
    $routes->group('documents', function ($routes) {
        $routes->get('/', 'DocumentController::index');                         // GET /api/documents
        $routes->get('(:num)', 'DocumentController::show/$1');                  // GET /api/documents/{id}
        $routes->post('upload', 'DocumentController::upload');                  // POST /api/documents/upload
        $routes->get('download/(:num)', 'DocumentController::download/$1');     // GET /api/documents/download/{id}
        $routes->put('(:num)', 'DocumentController::update/$1');                // PUT /api/documents/{id}
        $routes->delete('(:num)', 'DocumentController::delete/$1');             // DELETE /api/documents/{id}
        $routes->get('statistics', 'DocumentController::statistics');           // GET /api/documents/statistics
        $routes->get('recent', 'DocumentController::recent');                   // GET /api/documents/recent
        $routes->get('types', 'DocumentController::types');                     // GET /api/documents/types
        $routes->get('storage-usage', 'DocumentController::storageUsage');      // GET /api/documents/storage-usage
        $routes->delete('clean-orphan-files', 'DocumentController::cleanOrphanFiles'); // DELETE /api/documents/clean-orphan-files
        $routes->post('batch-upload', 'DocumentController::batchUpload');       // POST /api/documents/batch-upload

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'DocumentController::options');
    });

    // Land Plots API (individual operations)
    $routes->group('land-plots', function ($routes) {
        $routes->get('(:num)', 'LandPlotController::show/$1');                    // GET /api/land-plots/{id}
        $routes->put('(:num)', 'LandPlotController::update/$1');                  // PUT /api/land-plots/{id}
        $routes->delete('(:num)', 'LandPlotController::delete/$1');               // DELETE /api/land-plots/{id}
        $routes->put('(:num)/representative', 'LandPlotController::setRepresentative/$1'); // PUT /api/land-plots/{id}/representative

        // Handle OPTIONS for specific routes
        $routes->options('(:num)', 'LandPlotController::options');
        $routes->options('(:num)/representative', 'LandPlotController::options');
    });

    // Joint Common Areas API (individual operations)
    $routes->group('joint-common-areas', function ($routes) {
        $routes->get('(:num)', 'JointCommonAreaController::show/$1');              // GET /api/joint-common-areas/{id}
        $routes->put('(:num)', 'JointCommonAreaController::update/$1');            // PUT /api/joint-common-areas/{id}
        $routes->delete('(:num)', 'JointCommonAreaController::delete/$1');         // DELETE /api/joint-common-areas/{id}

        // Handle OPTIONS for specific routes
        $routes->options('(:num)', 'JointCommonAreaController::options');
    });

    // Property Owners API (individual operations)
    $routes->group('property-owners', function ($routes) {
        $routes->get('template', 'PropertyOwnerController::downloadTemplate');   // GET /api/property-owners/template
        $routes->get('/', 'PropertyOwnerController::index');                     // GET /api/property-owners
        $routes->get('(:num)', 'PropertyOwnerController::show/$1');              // GET /api/property-owners/{id}
        $routes->post('/', 'PropertyOwnerController::create');                   // POST /api/property-owners
        $routes->put('(:num)', 'PropertyOwnerController::update/$1');            // PUT /api/property-owners/{id}
        $routes->delete('(:num)', 'PropertyOwnerController::delete/$1');         // DELETE /api/property-owners/{id}

        // Handle OPTIONS for specific routes
        $routes->options('template', 'PropertyOwnerController::options');
        $routes->options('/', 'PropertyOwnerController::options');
        $routes->options('(:num)', 'PropertyOwnerController::options');
    });

    // Location API for cascading dropdowns
    $routes->group('locations', function ($routes) {
        $routes->get('counties', 'LocationController::counties');                          // GET /api/locations/counties
        $routes->get('districts/(:segment)', 'LocationController::districts/$1');          // GET /api/locations/districts/{countyCode}
        $routes->get('sections/(:segment)/(:segment)', 'LocationController::sections/$1/$2'); // GET /api/locations/sections/{countyCode}/{districtCode}
        $routes->get('hierarchy', 'LocationController::hierarchy');                        // GET /api/locations/hierarchy

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'LocationController::options');
    });

    // JWT Debug API (Admin only)
    $routes->group('admin/jwt-debug', ['namespace' => 'App\Controllers\Api\Admin'], function ($routes) {
        $routes->get('/', 'JwtDebugController::index');                                    // GET /api/admin/jwt-debug
        $routes->get('stats', 'JwtDebugController::stats');                                // GET /api/admin/jwt-debug/stats
        $routes->get('(:segment)', 'JwtDebugController::show/$1');                         // GET /api/admin/jwt-debug/{request_id}
        $routes->post('cleanup', 'JwtDebugController::cleanup');                           // POST /api/admin/jwt-debug/cleanup

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'JwtDebugController::options');
    });

    // Registration Logs API (Admin only)
    $routes->group('admin/registration-logs', ['namespace' => 'App\Controllers\Api\Admin'], function ($routes) {
        $routes->get('/', 'RegistrationLogController::index');                                  // GET /api/admin/registration-logs
        $routes->get('statistics', 'RegistrationLogController::statistics');                    // GET /api/admin/registration-logs/statistics
        $routes->get('(:num)', 'RegistrationLogController::show/$1');                           // GET /api/admin/registration-logs/{id}
        $routes->delete('(:num)', 'RegistrationLogController::delete/$1');                      // DELETE /api/admin/registration-logs/{id}

        $routes->options('(:any)', 'RegistrationLogController::options');
    });

    // API Request Logs (Admin only)
    $routes->group('request-logs', function ($routes) {
        $routes->get('/', 'ApiRequestLogController::index');                                    // GET /api/request-logs
        $routes->get('errors', 'ApiRequestLogController::errors');                              // GET /api/request-logs/errors
        $routes->get('slow', 'ApiRequestLogController::slow');                                  // GET /api/request-logs/slow
        $routes->get('statistics', 'ApiRequestLogController::statistics');                      // GET /api/request-logs/statistics
        $routes->delete('clean', 'ApiRequestLogController::clean');                             // DELETE /api/request-logs/clean
        $routes->get('(:num)', 'ApiRequestLogController::show/$1');                             // GET /api/request-logs/{id}
    });

    // Error Logs (Admin only)
    $routes->group('error-logs', function ($routes) {
        $routes->get('/', 'ErrorLogController::index');                                         // GET /api/error-logs
        $routes->get('unresolved', 'ErrorLogController::unresolved');                           // GET /api/error-logs/unresolved
        $routes->get('critical', 'ErrorLogController::critical');                               // GET /api/error-logs/critical
        $routes->get('statistics', 'ErrorLogController::statistics');                           // GET /api/error-logs/statistics
        $routes->post('resolve-multiple', 'ErrorLogController::resolveMultiple');               // POST /api/error-logs/resolve-multiple
        $routes->delete('clean', 'ErrorLogController::clean');                                  // DELETE /api/error-logs/clean
        $routes->get('(:num)', 'ErrorLogController::show/$1');                                  // GET /api/error-logs/{id}
        $routes->patch('(:num)/resolve', 'ErrorLogController::resolve/$1');                     // PATCH /api/error-logs/{id}/resolve

        $routes->options('(:any)', 'ApiRequestLogController::options');
    });
});
