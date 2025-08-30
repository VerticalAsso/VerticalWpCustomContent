<?php

namespace VerticalAppDriver\Api\Database\Core;
require_once __DIR__ . '/../Core/post_content.php';
require_once __DIR__ . '/../Core/events.php';

use \VerticalAppDriver\Api\Database\Core as Core;

class Category
{
    public int $term_id;
    public $name;
    public $slug;
}

/**
 * @brief Retrieve event categories using raw database accesses.
 * This function fetches categories associated with a given event ID.
 * @param int $event_id The ID of the event.
 * @return Category[] | null An array of categories associated with the event.
 */
function internal_get_event_categories_raw_db(int $event_id) : array | null
{
    global $wpdb;

    $table_term_relationships = $wpdb->prefix . 'term_relationships';
    $table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
    $table_terms = $wpdb->prefix . 'terms';

    // 1. Fetch data from term_taxonomy table:
    // term_taxonomy table have a taxonomy column that indicates the type of taxonomy.
    // We need to filter by 'event-categories' taxonomy, and this yields all the different available "categories" by their id and used count.
    // Here is an example of what we can find in the term_taxonomy table:
    // term_taxonomy_id : 24, term_id : 24, taxonomy : "event-categories", description : "", parent : 0, count: 101

    // 2. From the term_relationships table, we can link an event (object_id, which seems to point to a "POST ID") to a term_taxonomy_id.
    // Here is an example of what we can find in the term_relationships table:
    // object_id : 1234, term_taxonomy_id : 24, term_order : 0

    // 3. Finally, we can get the actual category names and slugs from the terms table using the term_id.
    // Here is an example of what we can find in the terms table:
    // term_id : 24, name : "Music", slug : "music", term_group : 0

    $event = Core\internal_get_single_event_record($event_id);
    $post_id = $event['post_id'] ?? null;
    if ($post_id === null) return []; // Event not found
    // Note: event ids are not contiguous (there are big id jumps between event records )

    // Prepare the SQL query to fetch categories associated with the event
    $query = $wpdb->prepare(
        "SELECT t.term_id, t.name, t.slug
        FROM $table_terms AS t
        INNER JOIN $table_term_taxonomy AS tt ON t.term_id = tt.term_id
        INNER JOIN $table_term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
        WHERE tr.object_id = %d AND tt.taxonomy = %s",
        $post_id,
        'event-categories' // Assuming 'event-categories' is the taxonomy used for event categories
    );

    // Execute the query and get results
    $categories = $wpdb->get_results($query);

    $result = [];
    foreach ($categories as $cat) {
        $category = new Category();
        $category->term_id = (int)$cat->term_id;
        $category->name = $cat->name;
        $category->slug = $cat->slug;
        $result[] = $category;
    }

    return $result;
}

/**
 * Retrieve event categories using Events Manager functions.
 *
 * @param int $event_id The ID of the event.
 * @return Category[] | null An array of categories associated with the event.
 */
function internal_get_event_categories_events_manager($event_id) : array | null
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

    $result = [];
    foreach ($categories as $cat) {
        $category = new Category();
        $category->term_id = (int)$cat['term_id'];
        $category->name = $cat['name'];
        $category->slug = $cat['slug'];
        $result[] = $category;
    }

    return $result;
}