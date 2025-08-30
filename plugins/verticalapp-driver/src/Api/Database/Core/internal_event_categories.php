<?php

namespace VerticalAppDriver\Api\Database\Core;

/**
 * @brief Retrieve event categories using raw database accesses.
 * This function fetches categories associated with a given event ID.
 * @param int $event_id The ID of the event.
 * @return array An array of categories associated with the event.
 */
function internal_get_event_categories_raw_db($event_id)
{
    global $wpdb;

    $table_term_relationships = $wpdb->prefix . 'term_relationships';
    $table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
    $table_terms = $wpdb->prefix . 'terms';

    // Prepare the SQL query to fetch categories associated with the event
    $query = $wpdb->prepare(
        "SELECT t.term_id, t.name, t.slug
        FROM $table_terms AS t
        INNER JOIN $table_term_taxonomy AS tt ON t.term_id = tt.term_id
        INNER JOIN $table_term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
        WHERE tr.object_id = %d AND tt.taxonomy = %s",
        $event_id,
        'event-categories' // Assuming 'event-categories' is the taxonomy used for event categories
    );

    // Execute the query and get results
    $categories = $wpdb->get_results($query);

    return $categories;
}

/**
 * Retrieve event categories using Events Manager functions.
 *
 * @param int $event_id The ID of the event.
 * @return array An array of categories associated with the event.
 */
function internal_get_event_categories_events_manager($event_id)
{
    /** @var \EM_Event $em_event*/
    $em_event = em_get_event($event_id);

    // Categories can be retrieved by fetching the _terms; _termmeta; _term_relationships; _taxonomymeta tables.
    // That's a mess, and Events Manager already has a way to get the categories for an event
    // So we use it instead !
    /** @var \EM_Categories $categories */
    $em_categories = $em_event->get_categories();
    $terms = $em_categories->terms;

    $categories = [];
    foreach ($terms as $term)
    {
        $categories[] = [
            "term_id" => $term->term_id,
            "name" => $term->name,
            "slug" => $term->slug
        ];
    }
    return $categories;
}