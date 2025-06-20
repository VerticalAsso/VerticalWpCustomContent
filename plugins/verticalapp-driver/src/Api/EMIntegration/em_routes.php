<?php

namespace VerticalAppDriver\Api\EMIntegration;

use WP_REST_Request;
use WP_Error;
use EM_Ticket_Booking;

/**
 * @brief registers all REST API routes upon activation.
 * @see verticalapp-driver.php for rest api init callback registration
 */
function register_all_routes()
{
    //
}

// Will need further research

// function myplugin_rest_register_user_to_event(WP_REST_Request $request) {
//     $user_id = $request['user_id'];
//     $event_id = $request['event_id'];

//     $EM_Event = em_get_event($event_id);
//     if (!$EM_Event || !$EM_Event->event_id) {
//         return new WP_Error('event_not_found', 'Event not found', array('status' => 404));
//     }

//     if ($EM_Event->get_bookings()->has_booking($user_id)) {
//         return new WP_Error('already_booked', 'User already booked', array('status' => 400));
//     }

//     $EM_Booking = em_get_booking(['person_id'=>$user_id, 'event_id'=>$event_id, 'booking_spaces'=>1]);
//     foreach ($EM_Event->get_bookings()->get_available_tickets() as $EM_Ticket) {
//         if ($EM_Ticket->is_available()) {
//             $EM_Ticket_Booking = new EM_Ticket_Booking([
//                 'ticket_id' => $EM_Ticket->ticket_id,
//                 'booking_id' => $EM_Booking->booking_id,
//                 'booking' => $EM_Booking
//             ]);
//             $EM_Booking->get_tickets_bookings()->add($EM_Ticket_Booking);
//             break;
//         }
//     }
//     if (!$EM_Booking->validate()) {
//         return new WP_Error('booking_invalid', implode(', ', (array)$EM_Booking->get_errors()), array('status' => 400));
//     }
//     if (!$EM_Event->get_bookings()->add($EM_Booking)) {
//         return new WP_Error('booking_failed', implode(', ', (array)$EM_Event->get_bookings()->get_errors()), array('status' => 400));
//     }
//     return ['success' => true];
// }

// function myplugin_rest_unregister_user_from_event(WP_REST_Request $request) {
//     $user_id = $request['user_id'];
//     $event_id = $request['event_id'];

//     $EM_Event = em_get_event($event_id);
//     if (!$EM_Event || !$EM_Event->event_id) {
//         return new WP_Error('event_not_found', 'Event not found', array('status' => 404));
//     }
//     $EM_Booking = $EM_Event->get_bookings()->get_booking_for($user_id);
//     if (!$EM_Booking || !$EM_Booking->booking_id) {
//         return new WP_Error('booking_not_found', 'Booking not found', array('status' => 404));
//     }
//     if (!$EM_Booking->cancel()) {
//         return new WP_Error('cancel_failed', implode(', ', (array)$EM_Booking->get_errors()), array('status' => 400));
//     }
//     return ['success' => true];
// }

// function myplugin_rest_add_comment_to_event(WP_REST_Request $request) {
//     $user_id = $request['user_id'];
//     $event_id = $request['event_id'];
//     $comment = $request['comment'];

//     $commentdata = [
//         'comment_post_ID' => $event_id,
//         'user_id'         => $user_id,
//         'comment_content' => $comment,
//         'comment_approved'=> 1
//     ];
//     $comment_id = wp_insert_comment($commentdata);
//     if (!$comment_id) {
//         return new WP_Error('comment_failed', 'Failed to add comment', array('status' => 400));
//     }
//     return ['success' => true, 'comment_id' => $comment_id];
// }