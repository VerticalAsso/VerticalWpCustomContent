<?php

namespace VerticalAppDriver\Api\EMIntegration;

require_once __DIR__ . '/../../Auth/apikey_checking.php';
require_once __DIR__ . '/../Database/Composite/full_event.php';

use \VerticalAppDriver\Api\Database as Database;
use WP_REST_Request;
use WP_Error;
use EM_Ticket_Booking;
use EM_Ticket;
use EM_Booking;

use VerticalAppDriver\Api\Database\Composite\UserProfile;
use WP_REST_Response;

use function VerticalAppDriver\Api\Database\Composite\internal_get_user_profile;

function register_em_registration_routes()
{
    register_rest_route('vdriver/v1', '/em/register', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\EMIntegration\register_user_to_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'event_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ],
    ]);

    register_rest_route('vdriver/v1', '/em/unregister', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\EMIntegration\unregister_user_from_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'event_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ],
    ]);
}



/**
 * @brief Registers a user to an event.
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 * @see https://wp-events-plugin.com/documentation/developers/booking-api/
 */
function register_user_to_event(WP_REST_Request $request)
{
    $user_id = $request['user_id'];
    $event_id = $request['event_id'];

    // Fetch the event
    $EM_Event = em_get_event($event_id);
    if (!$EM_Event || !$EM_Event->event_id)
    {
        return new WP_Error('event_not_found', 'Event not found', array('status' => 404));
    }

    // Retrieve user roles and check if the user has sufficient permissions (aka the proper role set) to
    // register to an event. This is optional and can be customized as needed.
    $user = internal_get_user_profile($user_id);
    if (!$user)
    {
        return new WP_Error('user_not_found', 'User not found', array('status' => 404));
    }

    $full_event =  Database\Composite\internal_get_full_event($event_id);
    if ($full_event instanceof WP_REST_Response)
    {
        return $full_event; // Propagate the error
    }

    // Assert user can register to this event based on their roles
    $event_tickets = $EM_Event->get_tickets();
    $has_required_role = internal_check_user_roles($user, $event_tickets);
    if (!$has_required_role)
    {
        $required_roles = $event_tickets->get_first()->members_roles;
        return new WP_Error('insufficient_permissions', 'User does not have the required role to register for this event.', array('status' => 403, 'required_roles' => $required_roles));
    }

    // Reject if the user is already booked
    if ($EM_Event->get_bookings()->has_booking($user_id))
    {
        return new WP_Error('already_booked', 'User already booked', array('status' => 400));
    }

    // Uses the first "Ticket" template available in the list of tickets for the event
    // Then, proceeds to book one space for the user
    $EM_Booking = em_get_booking(['person_id' => $user_id, 'event_id' => $event_id, 'booking_spaces' => 1]);
    $bookings = $EM_Event->get_bookings();
    $bookings->load();

    /** @var \EM_Tickets $tickets */
    $tickets = $bookings->get_tickets();

    /** @var \EM_Ticket $ticket */
    foreach ($tickets as $ticket)
    {
        $EM_Ticket_Booking = new EM_Ticket_Booking([
            'ticket_id' => $ticket->ticket_id,
            'booking_id' => $EM_Booking->booking_id,
            'booking' => $EM_Booking
        ]);

        // Cannot book a new ticket
        if (!$EM_Ticket_Booking->validate())
        {
            return new WP_Error('ticket_booking_invalid', implode(', ', (array)$EM_Ticket_Booking->get_errors()), array('status' => 500));
        }
        $EM_Booking->get_tickets_bookings()->add($EM_Ticket_Booking);
    }

    if (!$EM_Booking->validate())
    {
        return new WP_Error('booking_invalid', implode(', ', (array)$EM_Booking->get_errors()), array('status' => 400));
    }

    if (!$EM_Event->get_bookings()->add($EM_Booking))
    {
        return new WP_Error('booking_failed', implode(', ', (array)$EM_Event->get_bookings()->get_errors()), array('status' => 400));
    }

    // Prepare booking details for the response
    $ticket_bookings = [];
    foreach ($EM_Booking->get_tickets_bookings() as $tb)
    {
        $ticket_bookings[] = [
            'ticket_id' => $tb->ticket_id,
            'spaces' => $tb->get_spaces(),
            'price' => $tb->get_price(),
            'ticket_name' => method_exists($tb, 'get_ticket') ? $tb->get_ticket()->ticket_name : null,
        ];
    }

    return [
        'booking_id' => $EM_Booking->booking_id,
        'event_id' => $event_id,
        'user_id' => $user_id,
        'booking_status' => $EM_Booking->booking_status,
        'ticket_bookings' => $ticket_bookings,
    ];
}


/**
 * @brief Verifies if a user has the required roles to register for an event.
 * @param UserProfile $profile The user's profile containing their roles.
 * @return bool True if the user has at least one of the required roles, false otherwise.
 * If no specific roles are required, returns true.
 */
function internal_check_user_roles(UserProfile $profile, \EM_Tickets | null $tickets ): bool
{
    // Check ticket-specific role restrictions (if any)
    // Tickets can have their own role restrictions, which should be considered in addition to event-level
    $required_roles = [];
    if( $tickets != null )
    {
        $ticket = $tickets->get_first();
        if( $ticket instanceof EM_Ticket )
        {
            $ticket_restrictions = $ticket->members_roles;
            if( is_array($ticket_restrictions) && count($ticket_restrictions) > 0 )
            {
                $required_roles = array_merge($required_roles, $ticket_restrictions);
            }
        }
    }

    // Check roles for this user
    $has_required_role = false;
    if (empty($required_roles))
    {
        // No specific role required, allow registration
        $has_required_role = true;
    }
    else
    {
        // Check if the user has at least one of the required roles
        $has_required_role = false;
        foreach ($profile->roles as $role)
        {
            if (in_array($role, $required_roles))
            {
                $has_required_role = true;
                break;
            }
        }
    }

    return $has_required_role;
}


function unregister_user_from_event(WP_REST_Request $request)
{
    $user_id = $request['user_id'];
    $event_id = $request['event_id'];

    // Fetch the event
    $EM_Event = em_get_event($event_id);
    if (!$EM_Event || !$EM_Event->event_id)
    {
        return new WP_Error('event_not_found', 'Event not found', array('status' => 404));
    }

    // Check if the user has a booking for this event
    /** @var \EM_Bookings $EM_Booking */
    $EM_Booking = $EM_Event->get_bookings();

    // Returns a mixed type: either false or an EM_Booking object
    $user_booking = $EM_Booking->has_booking($user_id);
    if ($user_booking == false)
    {
        return new WP_Error('booking_not_found', 'Booking not found for this user and event', array('status' => 404));
    }

    // Delete the booking
    /** @var \EM_Booking $user_booking */
    if (!$user_booking->cancel(true)) // false: do not send cancellation email
    {
        return new WP_Error('deletion_failed', implode(', ', (array)$EM_Booking->get_errors()), array('status' => 500));
    }

    return new WP_REST_Response('User unregistered from event successfully', 200);
}
