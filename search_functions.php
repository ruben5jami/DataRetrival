
<?php

include_once 'expressions.php';
include_once 'index_functions.php';


//function that
function search_words($q, $index) {
    global $total_docs;
    global $stopwords;
    global $operators_dictionary;
    global $has_quotes;
    $total_docs = get_docs_id();
    $query_vars = explode(" ", $q);
    //for every search word the user types change to lowercase
    //check if surrounded with quotes
    //if true remove them and update flag
    //if false check they are in the stop words array
    foreach ($query_vars as $key => $value) {
        $token = strtolower($value);
        if (mb_substr($token, 0, 1) != '"' && mb_substr($token, -1) != '"') {
            $has_quotes = false;
            if (in_array($token, $stopwords)) {
                //in case of an operator command with a stop word
                $query_vars = array_diff($query_vars, array('and', 'or', 'not', 'And', 'Or', 'Not', 'OR', 'AND', 'NOT'));
                unset($query_vars[$key]);
                continue;
            }
        } else {
            $token = substr($token, 1, -1);
            $has_quotes = true;
        }
        $query_vars[$key] = $token;
    }
    //transform in the correct order for a regular expression and validity of parentheses
    $query_vars = convert_to_npr($query_vars);
    if(empty($query_vars)){
        return array();
    }


    $added_docs = array(); //this is the docs of the synonyms
    //foreach word in the search look up for the word and its synonyms
    foreach ($query_vars as $token) {
        if (!in_array($token, $operators_dictionary) || $has_quotes) {
            $synonyms = get_synonyms($token);   //get all the synonyms word from a given term
            if($synonyms != NULL) {
                //adds the synonyms words doc id from the current word
                foreach ($synonyms as $synonym) {
                    $added_docs[] = get_docs_from_index($synonym, $index);
                }
            }
            //adds to temp index the id of the docs which the word given appear in
            $temp_index[] = array("term" => $token, "docs" => get_docs_from_index($token, $index));
        } else {
            $temp_index[] = array("term" => $token, "docs" => null);
        }

    }

    //create the expression tree in which every intersection represent a boolean operator
    //and every leaf represents a word
    $root = create_tree(new ArrayIterator($temp_index));
    $result = $root->evaluate();
    //adds the synonyms docs for final search
    foreach($added_docs as $doc){
        foreach($doc as $d){
            $result[] = $d;
        }
    }

    $docs = array();
    foreach ($result as $id) {
        $docs[$id] = get_doc_details($id);
    }

    return $docs;
}

//query to the database to get a document id
function get_docs_id() {
    global $conn;

    $sql = "SELECT `id` FROM `documents`";
    if ($result = $conn->query($sql)) {
        $ids = array();
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row["id"];
        }
        return $ids;
    }
    return array();
}

//transform in the correct order for a regular expression and validity of parentheses
//uses a stack to input opening parentheses and pop on closing parentheses
function convert_to_npr($tokens) {
    global $operators_dictionary;
    global $has_quotes;
    $out_q = new SplQueue();
    $stack = new SplStack();

    $index = 0;

    while (count($tokens) > $index) {
        $t = $tokens[$index];
        switch ($t) {
            case (!in_array($t, $operators_dictionary) || $has_quotes):
                $out_q->enqueue($t);
                break;
            case ($t == "not"):
            case ($t == "and"):
            case ($t == "or"):
                $stack->push($t);
                break;
            case ($t == "("):
                $stack->push($t);
                break;
            case ($t == ")"):
                while ($stack->top() != "(") {
                    $out_q->enqueue($stack->pop());
                }
                $stack->pop();
                if ($stack->count() > 0 && $stack->top() == "not") {
                    $out_q->enqueue($stack->pop());
                }
                break;
            default :
                break;
        }
        ++$index;
    }
    while ($stack->count() > 0) {
        $out_q->enqueue($stack->pop());
    }

    $reversed_q = array();
    foreach ($out_q as $value) {
        $reversed_q[] = $value;
    }
    return array_reverse($reversed_q);
}

//create the expression tree in which every intersection represent a boolean operator
//and every leaf represents a word
function create_tree(ArrayIterator &$it) {
    global $operators_dictionary;
    global $has_quotes;
    if (!in_array($it->current()["term"], $operators_dictionary) || $has_quotes) {
        $leaf = new leaf($it->current());
        $it->next();
        return $leaf;
    } else {
        if ($it->current()["term"] == "not") {
            $it->next();

            $op = create_tree($it);
            return new notEx($op);
        } else if ($it->current()["term"] == "and") {
            $it->next();

            $left = create_tree($it);
            $right = create_tree($it);
            return new andEx($left, $right);
        } else if ($it->current()["term"] == "or") {
            $it->next();

            $left = create_tree($it);
            $right = create_tree($it);
            return new orEx($left, $right);
        }
    }
    return null;
}

//gets the details of a doc with the id
function get_doc_details($id) {
    global $conn;
    $sql = "SELECT * FROM `documents` WHERE `id` = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row;
}

$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "another", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "are", "around", "as", "at", "back", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom", "but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

$operators_dictionary = array("(", "and", "or", "not", ")");

