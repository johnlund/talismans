<?php
/**
 * Plugin Name:  mm-org The Lost Talismans
 * Plugin URI:   https://modernmasters.org
 * Description:  Adds functionality for The Lost Talismans
 * Version:      1.0.0
 * Author:       John Lund
 * Author URI:   https://modernmasters.org/
 * License:      GPL2
 */

/*

1. CSS for talismans
2. JS for talismans

*/

function collected_difficulties() {
    //global $talismans;
    $collected = get_user_meta( get_current_user_id(), 'talismans', true );
    $difficulties = [];
    
    if ($collected) {
        $collected = json_decode($collected);

        $easy = 0;
        $medium = 0;
        $hard = 0;

        foreach($collected as $x) {
            if ($x->difficulty == 'easy') $easy++;
            elseif ($x->difficulty == 'medium') $medium++;
            elseif ($x->difficulty == 'hard') $hard++;
        }

        if ($easy > 4) {
            array_push($difficulties, 'easy');
        }
        if ($medium > 4) {
            array_push($difficulties, 'medium');
        }
        if ($hard > 4) {
            array_push($difficulties, 'hard');
        }
    }

    return $difficulties;
}

add_action('wp_enqueue_scripts', 'my_enqueue');
function my_enqueue() {
    wp_enqueue_script( 'talismans', get_stylesheet_directory_uri() . '/js/talismans.js', array( 'jquery' ), '', true );
    $talismans_nonce = wp_create_nonce('talismans');
    wp_localize_script('talismans', 'my_ajax_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => $talismans_nonce,
    ));
}

function ajax_check_user_logged_in() {
    //update_user_meta( get_current_user_id(), 'talismans', '');
    //if (is_role('tester')) {
        check_ajax_referer('talismans');
        //global $talismans;

        foreach ($GLOBALS["talismans"] as $x) {
            $result = array_filter($GLOBALS["talismans"], function($x) {
                if ($x->id == $_POST['talisman']) return $x;
            });
            reset($result);
            if (current($result) == '') $result = false;
        }

        $loggedIn = is_user_logged_in() ? true : false;
        if ($loggedIn) {
            $collected = get_user_meta( get_current_user_id(), 'talismans', true );
            if ($collected) {
                $collected = json_decode($collected);
                foreach ($collected as $x) {
                    $resultC = array_filter($collected, function($x) {
                        if ($x->id == $_POST['talisman']) return $x;
                    });
                    reset($resultC);
                    if (current($resultC) == '') $resultC = false;
                }
            }
        }

        echo json_encode(array('success' => $loggedIn, 'talisman' => ($result) ? array_values($result) : $result, 'collected' => ($resultC) ? true : false));
    //}
    die();
}
add_action('wp_ajax_is_user_logged_in', 'ajax_check_user_logged_in');
add_action('wp_ajax_nopriv_is_user_logged_in', 'ajax_check_user_logged_in');

//Add collected talisman to user meta
add_action('wp_ajax_collect_talisman', 'my_ajax_handler');
function my_ajax_handler() {
    check_ajax_referer('talismans');
    //global $talismans;

    foreach ($GLOBALS["talismans"] as $x) {
        $result = array_filter($GLOBALS["talismans"], function($x) {
            if ($x->id == $_POST['talisman']) return $x;
        });
        reset($result);
    }
    
    if (is_user_logged_in()) {
        //if talismans exists, get current collected talismans, push new talisman, and save
        //When I send this in to the meta field, I want it to be a string representing an array with a set of objects.
        //If empty, collected is null
        $collected = get_user_meta( get_current_user_id(), 'talismans', true );
        //error_log('test'.$collected);
        //If there is already something there, decode the string and add object to array
        if ($collected) {
            $collected = json_decode($collected, true);
            array_push($collected, current($result));
        }
        //If empty, set it to an array with a object as it's first member
        else $collected = array(current($result));
        //Push encoded string to avoid WP serialization
        update_user_meta( get_current_user_id(), 'talismans', json_encode($collected));
    }
    echo current($result)->title;
    wp_die(); // all ajax handlers should die when finished
}

class Talisman
{
    public $filename;
    public $difficulty;
    public $card;
    public $id;
    public $title;
    public $creator;
    public $page;
}

$meta = array(
    "element_of_ether"=>[ //easy - breadcrumbs
        "page"=>"mm22-experience",
        "description"=>"Realm wherein the mark of creation originates. Infuses Elemental Essence of Ether. Message: I go beyond the veil to present my intention."
    ],
    "hands_of_the_healer"=>[ //medium
        "page"=>"hidden-human-powers",
        "description"=>"Immortal Soul Line of the Honored Healer. Charged Anima&mdash;Enhances the soul's journey on the Golden Path of Healing. Power&ndash;Physical Healing"
    ],
    "soul_circles"=>[ //easy - in paragraphs
        "page"=>"soul-mates-resonance",
        "description"=>"A Blessing of Fortuna for those Called and Chosen to connect with other Masters. Follow the call of your heart and gain uncompromised love."
    ],
    "cloak_of_conspiracy"=>[ //hard
        "page"=>"free-technology",
        "description"=>"Granted to invoke the Spirit of Truth. Invoken Chant: Hail Spirit of Sight! Come to me &amp; Let me see! And now my eyes can see through lies."
    ],
    "card_of_destiny"=>[ //medium
        "page"=>"team",
        "description"=>"Sign of Fortuna given to the Chosen Seekers determined to find and fulfill their destiny. Persevere! And with this gift, you cannot fail!"
    ],
    "element_of_water"=>[ //medium
        "page"=>"features",
        "description"=>"Realm wherein creative force permeates. Infuses Elemental Essence of Water. Message: I infiltrate, flow &amp; repair the cracks of weakness."
    ],
    "element_of_air"=>[ //hard
        "page"=>"contact",
        "description"=>"Realm wherein creation connects &amp; expands. Infuses Elemental Essence of Air. Message: I breathe words of truth upon the winds of change."
    ],
    "voice_of_the_messenger"=>[ //easy - end of content
        "page"=>"youve-been-called",
        "description"=>"Immortal Soul Line of the Visionary Messenger. Charged Anima&mdash;Enhances the soul's journey on the Sapphire Path of the Seer. Power&ndash;Prophecy"
    ],
    "element_of_earth"=>[ //hard
        "page"=>"masons-old-school",
        "description"=>"Realm wherein creative force binds & materializes. Infuses Elemental Essence of Earth. Message: I sculpt in stone & plant ideas in soil."
    ],
    "the_heros_heart"=>[ //medium
        "page"=>"podesta-delonge",
        "description"=>"A Blessing of Fortuna for those Called and Chosen to become Modern Masters. Follow the path of your courage and gain your heart's desires."
    ],
    "crown_of_the_magi"=>[ //easy - title
        "page"=>"game",
        "description"=>"Immortal Soul Line of the Magi Alchemist. Charged Anima&mdash;Enhances the soul's journey on the Violet Path of High Magick. Power&ndash;Metamorphosis"
    ],
    "element_of_fire"=>[ //hard
        "page"=>"oversoul-sevens-little-book",
        "description"=>"Realm wherein creation purifies &amp; transforms. Invokes Elemental Essence of Fire. Message: I blaze through darkness &amp; sanctify desire."
    ],
    "eye_of_the_shaman"=>[ //easy - in paragraphs
        "page"=>"esoterica-contents",
        "description"=>"Immortal Soul Line of the Mystical Shaman. Charged Anima&mdash;Enhances walking the Indigo Path of the Shaman. Power&ndash;Multi-Dimensional Travel"
    ],
    "cradle_of_the_artisan"=>[ //easy - in paragraphs
        "page"=>"initial-contact",
        "description"=>"Immortal Soul Line of the Eternal Artisan. Charged Anima&mdash;Enhances the soul's journey on the Amber Path of Creativity. Power&ndash;Manifestation"
    ],
    "heart_of_the_priest_and_priestess"=>[ //hard
        "page"=>"soul-lines-beloved-priest-priestess",
        "description"=>"Immortal Soul Line of the Beloved Priest &amp; Priestess. Charged Anima&mdash;Enhances the Emerald Path of Compassion. Power&ndash;Emotional Healing"
    ],
    "mark_of_the_vampire"=>[ //medium
        "page"=>"soul-lines-the-immortal-vampire",
        "description"=>"Immortal Soul Line of Vampire. Charged Anima&mdash;Enhances the soul's journey on the Scarlet Path of Energy Transformation. Power&ndash;Life Force."
    ]
);

$directory = '/nas/content/live/modernmasters/wp-content/uploads/talismans/';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));

//Break up into three equal parts, easy, hard, very hard
//Define global variable to hold talismans
$talismans = [];

function modify($str) {
    return ucwords(str_replace("_", " ", $str));
}

foreach ($scanned_directory as $talisman) {
    $temp = new Talisman();
    
    //0 = filename, 1 = difficulty, 2 = card number, 3 = id, 4 = title (formatted), 5 = creator
    // e.g. medium-16-hands_of_the_healer-modernfollower.png
    $data = [];
    preg_match("/(.*?)-(.*)-(.*)-(.*)\.(.*)/", $talisman, $data);

    $temp->filename = $data[0];
    $temp->difficulty = $data[1];
    $temp->card = $data[2];
    $temp->id = $data[3];
    $temp->title = ucwords(str_replace("_", " ", $data[3]));
    $temp->creator = $data[4];

    //global $meta;
    $temp->page = $GLOBALS["meta"][$temp->id]['page'];
    $temp->description = $GLOBALS["meta"][$temp->id]['description'];

    array_push($GLOBALS["talismans"], $temp);
}

add_filter ('the_content', 'insert_talisman');
function insert_talisman($content) {
    //global $talismans;
    foreach ($GLOBALS["talismans"] as $talisman) {
        //output talismans to pages
        $curPage = get_permalink();
        $test = [];
        $re = "/\/".$talisman->page."\/$/";
        if (preg_match($re, $curPage, $test)) {
            if ($test[0]) {
                $content .= '<div class="talisman" id="'.$talisman->id.'"></div>';
            }
        }
    }
    return $content;
}
