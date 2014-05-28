<?php
/**
 *  ====== Related Articles ======
 *  The Related Articles sidebar widget will find articles from the Guardian that might be related to your
 *  blog post.  It will then display a list of headlines in your sidebar.
 *
 *  The Related Articles sidebar widget does not require an access key.
 *
 *  To add it to your blog, go to 'Widgets' and drag the widget to one of your sidebars.
 *
 *  ======= Notes =======
 *  This plug-in is designed to be used as is. We have several ways of working with partners if you want
 *  to do something different. Find out more here:
 *
 *  http://www.guardian.co.uk/open-platform
 *
 *  If you have ideas on how to improve the plug-in or other things we could do with WordPress,
 *  please join the conversation here:
 *
 *  http://groups.google.com/group/guardian-api-talk
 *
 *  Kind Regards,
 *  Daniel Levitt for Guardian News and Media Ltd
 *  daniel.levitt@guardian.co.uk
 *  Guardian News & Media Ltd
 *
 */

function widget_Guardian_Related() {

    global $post;

    // If the user is on a single post page
    if (is_single ()) {

        $api = new GuardianOpenPlatformAPI();

        $options = array(
            'q' => Guardian_keywords ( get_the_content () ),
            'format' => 'json',
            'page' => 1,
            'show-fields' => 'headline,standfirst'
        );

        // Search the api and pass the $page variable as page number 1
        $arr_related_content = $api->guardian_api_search ( $options, true );
        if ( $arr_related_content['total'] >= 1 ) {
            // If there are any results then render the results in html
            echo render_related_content ( $arr_related_content );
        }
    }
}

/**
 * Render the sidebar list for related content
 *
 * @param $arr_related_content
 */
function render_related_content($arr_related_content) {

    $arr_html_output = array ();

    $arr_html_output [] = "<div class=\"widget\">";
    $arr_html_output [] = "	<h3 class=\"hl\">Guardian Related Articles</h3>";
    $arr_html_output [] = "	<ul>";

    foreach ( $arr_related_content[results] as $related_content ) {
        $alt = strip_tags($related_content [fields] [standfirst]);
        $arr_html_output [] = "		<li>";
        $arr_html_output [] = "			<a href=\"{$related_content [webUrl] }\" title=\"{$alt}\" alt=\"{$alt}\" target=\"_blank\" >";
        $arr_html_output [] = "				" . $related_content [fields] [headline];
        $arr_html_output [] = "			</a>";
        $arr_html_output [] = "		</li>";
    }

    $arr_html_output [] = "	</ul>";
    $arr_html_output [] = "</div>";

    return implode ( $arr_html_output, "\n" );
}

/**
 * Returns an array of the most used words
 *
 * @param $source
 */
function Guardian_keywords($source) {

    $overusedwords = array("guardian", "a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

    $source = strip_tags ( $source );

    if (function_exists ( 'mb_split' )) {
        mb_regex_encoding ( get_option ( 'blog_charset' ) );
        $wordlist = mb_split ( '\s*\W+\s*', mb_strtolower ( $source ) );
    } else
        $wordlist = preg_split ( '%\s*\W+\s*%', strtolower ( $source ) );

    // Build an array of the unique words and number of times they occur.
    $tokens = array_count_values ( $wordlist );

    // Remove the stop words from the list.
    foreach ( $overusedwords as $word ) {
        unset ( $tokens [$word] );
    }
    // Remove words which are only a letter
    foreach ( array_keys ( $tokens ) as $word ) {
        if (function_exists ( 'mb_strlen' ))
            if (mb_strlen ( $word ) < 2)
                unset ( $tokens [$word] );
            else if (strlen ( $word ) < 2)
                unset ( $tokens [$word] );
    }

    arsort ( $tokens, SORT_NUMERIC );

    $types = array_keys ( $tokens );

    $api = new GuardianOpenPlatformAPI();

    if (count ( $types ) > $api->guardian_api_max_keywordsValue())
        $types = array_slice ( $types, 0, $api->guardian_api_max_keywordsValue() );
    return $types;
}

?>
